<?php

namespace App\Http\Controllers;

use App\Enums\ReservationPayment;
use App\Exceptions\ReservationRuleViolation;
use App\Http\Presenters\ReservationPresenter;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use App\Services\Payments\GatewayException;
use App\Services\Pricing;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Customers reserving an item that's in the store and paying online (mobile
 * money or card), in full or a deposit, then collecting it in store.
 */
class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservations,
        private readonly ReservationPresenter $presenter,
        private readonly Pricing $pricing,
    ) {}

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('reservations/index', [
            'reservations' => Reservation::where('customer_id', $user->id)
                ->latest('id')
                ->get()
                ->map(fn (Reservation $r) => $this->presenter->show($r))
                ->all(),
        ]);
    }

    /** The choice: pay in full, or a deposit. */
    public function create(Product $product, string $reference): Response|RedirectResponse
    {
        $unit = $this->reservations->availableUnit($product, $reference);

        if ($unit === null) {
            return to_route('shop.show', $product)->withErrors(['reserve' => 'Sorry, that item has just been reserved. Please choose another.']);
        }

        $unit->load(['condition', 'media', 'product.media']);

        try {
            $quote = $this->reservations->quote($unit);
        } catch (ReservationRuleViolation $e) {
            return to_route('shop.show', $product)->withErrors(['reserve' => $e->getMessage()]);
        }

        $photo = $unit->getFirstMedia('photos') ?? $product->getFirstMedia('gallery');

        return Inertia::render('reservations/create', [
            'item' => [
                'reference' => $unit->reference,
                'name' => $product->name,
                'slug' => $product->slug,
                'grade' => $unit->condition->grade,
                'condition' => $unit->condition->name,
                'image' => $photo?->getUrl('thumb'),
            ],
            'price' => $this->pricing->present($quote['price']),
            'deposit' => $this->pricing->present($quote['deposit']),
            'depositPercent' => $quote['percent'],
            'days' => $quote['days'],
        ]);
    }

    public function store(Request $request, Product $product, string $reference): HttpResponse
    {
        $data = $request->validate(['payment_option' => ['required', 'in:full,deposit']]);
        /** @var User $user */
        $user = $request->user();
        $unit = $this->reservations->availableUnit($product, $reference);

        if ($unit === null) {
            return to_route('shop.show', $product)->withErrors(['reserve' => 'Sorry, that item has just been reserved. Please choose another.']);
        }

        try {
            $url = $this->reservations->start($user, $unit, ReservationPayment::from($data['payment_option']), route('payments.callback'));
        } catch (ReservationRuleViolation|GatewayException $e) {
            return to_route('shop.show', $product)->withErrors(['reserve' => $e->getMessage()]);
        }

        return Inertia::location($url);
    }

    public function show(Reservation $reservation): Response
    {
        Gate::authorize('view', $reservation);

        return Inertia::render('reservations/show', [
            'reservation' => $this->presenter->show($reservation),
            'canPay' => Gate::allows('pay', $reservation),
            'canCancel' => Gate::allows('cancel', $reservation),
        ]);
    }

    public function pay(Reservation $reservation): HttpResponse
    {
        Gate::authorize('pay', $reservation);

        try {
            $url = $this->reservations->retryPayment($reservation, route('payments.callback'));
        } catch (ReservationRuleViolation|GatewayException $e) {
            return to_route('reservations.show', $reservation)->withErrors(['payment' => $e->getMessage()]);
        }

        return Inertia::location($url);
    }

    public function cancel(Reservation $reservation): RedirectResponse
    {
        Gate::authorize('cancel', $reservation);

        try {
            $this->reservations->cancel($reservation, 'customer', 'You cancelled it.');
        } catch (ReservationRuleViolation|GatewayException $e) {
            return to_route('reservations.show', $reservation)->withErrors(['cancel' => $e->getMessage()]);
        }

        return to_route('reservations.show', $reservation);
    }
}
