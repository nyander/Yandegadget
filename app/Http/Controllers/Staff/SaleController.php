<?php

namespace App\Http\Controllers\Staff;

use App\Enums\PaymentMethod;
use App\Enums\StockStatus;
use App\Exceptions\SaleRuleViolation;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecordSaleRequest;
use App\Models\Sale;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\Pricing;
use App\Services\SaleService;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The in-store sale screen: find the item, agree a price above the floor, record it.
 */
class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService $sales,
        private readonly Pricing $pricing,
    ) {}

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $search = trim($request->string('q')->limit(100, '')->toString());

        $units = StockUnit::query()
            ->whereIn('status', [StockStatus::Received, StockStatus::Reserved])
            ->when($user->location_id, fn (Builder $q, int $id) => $q->where('location_id', $id))
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->where('reference', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%")
                ->orWhereHas('product', fn (Builder $p) => $p
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%"))))
            ->with(['product.media', 'condition', 'location'])
            ->latest('received_at')
            ->limit(24)
            ->get();

        $todaysSales = Sale::completed()
            ->when($user->location_id, fn (Builder $q, int $id) => $q->where('location_id', $id))
            ->where('sold_at', '>=', today())
            ->with(['stockUnit.product', 'seller'])
            ->latest('sold_at')
            ->get();

        return Inertia::render('staff/sell/index', [
            'search' => $search,
            'units' => $units->map(fn (StockUnit $unit) => $this->unitCard($unit))->all(),
            'today' => [
                'count' => $todaysSales->count(),
                'total' => $todaysSales->isEmpty() ? null : $this->pricing->present(
                    Money::ofMinor($todaysSales->sum('price_paid_amount'), $todaysSales->first()->currency),
                ),
                'sales' => $todaysSales->take(10)->map(fn (Sale $sale) => [
                    'id' => $sale->id,
                    'reference' => $sale->reference,
                    'product' => $sale->stockUnit->product->name,
                    'price_paid' => $this->pricing->present($sale->price_paid),
                    'payment_method' => $sale->payment_method->getLabel(),
                    'sold_at' => $sale->sold_at->toIso8601String(),
                    'seller' => $sale->seller->name,
                ])->values()->all(),
            ],
        ]);
    }

    public function create(StockUnit $unit): Response
    {
        Gate::authorize('sell', $unit);

        $unit->load(['product.media', 'condition', 'location']);

        try {
            $quote = $this->sales->quote($unit);
            $pricing = [
                'listed' => $this->pricing->present($quote->listed),
                'floor' => $this->pricing->present($quote->floor),
                'floor_percent' => $quote->floorPercent,
                'currency' => $quote->listed->getCurrency()->getCurrencyCode(),
                'rate' => $quote->rate ? [
                    'value' => $quote->rate->rate,
                    'effective_at' => $quote->rate->effective_at->toIso8601String(),
                ] : null,
            ];
            $problem = null;
        } catch (SaleRuleViolation $e) {
            $pricing = null;
            $problem = $e->getMessage();
        }

        return Inertia::render('staff/sell/create', [
            'unit' => $this->unitCard($unit) + [
                'condition_notes' => $unit->condition_notes,
                'serial_number' => $unit->serial_number,
            ],
            'pricing' => $pricing,
            'problem' => $problem,
            'paymentMethods' => collect(PaymentMethod::cases())->map(fn (PaymentMethod $m) => [
                'value' => $m->value,
                'label' => $m->getLabel(),
                'takes_reference' => $m->takesReference(),
            ])->all(),
            'today' => today()->toDateString(),
            'earliest' => today()->subDays(30)->toDateString(),
        ]);
    }

    public function store(RecordSaleRequest $request, StockUnit $unit): RedirectResponse
    {
        Gate::authorize('sell', $unit);

        /** @var User $user */
        $user = $request->user();
        $currency = $unit->location->currency;

        $soldOn = CarbonImmutable::parse($request->string('sold_on')->toString(), $unit->location->timezone);
        $soldAt = $soldOn->isToday() ? now() : $soldOn->setTime(12, 0);

        try {
            $sale = $this->sales->record(
                $unit,
                Money::of($request->string('price')->toString(), $currency, roundingMode: RoundingMode::HalfUp),
                PaymentMethod::from($request->string('payment_method')->toString()),
                $user,
                [
                    'payment_reference' => $request->input('payment_reference'),
                    'customer_name' => $request->input('customer_name'),
                    'customer_phone' => $request->input('customer_phone'),
                    'notes' => $request->input('notes'),
                    'sold_at' => $soldAt,
                ],
            );
        } catch (SaleRuleViolation $e) {
            return back()->withErrors(['price' => $e->getMessage()]);
        }

        return to_route('staff.sales.show', $sale);
    }

    public function show(Sale $sale): Response
    {
        Gate::authorize('viewReceipt', $sale);

        $sale->load(['stockUnit.product', 'stockUnit.condition', 'seller', 'location', 'voider']);

        return Inertia::render('staff/sales/show', [
            'sale' => [
                'id' => $sale->id,
                'reference' => $sale->reference,
                'product' => $sale->stockUnit->product->name,
                'brand' => $sale->stockUnit->product->brand,
                'unit_reference' => $sale->stockUnit->reference,
                'serial_number' => $sale->stockUnit->serial_number,
                'condition' => "{$sale->stockUnit->condition->grade} – {$sale->stockUnit->condition->name}",
                'listed' => $this->pricing->present($sale->listed),
                'price_paid' => $this->pricing->present($sale->price_paid),
                'discount_percent' => $sale->discountPercent(),
                'payment_method' => $sale->payment_method->getLabel(),
                'payment_reference' => $sale->payment_reference,
                'customer_name' => $sale->customer_name,
                'customer_phone' => $sale->customer_phone,
                'notes' => $sale->notes,
                'seller' => $sale->seller->name,
                'location' => $sale->location->name,
                'address' => $sale->location->address,
                'sold_at' => $sale->sold_at->toIso8601String(),
                'voided_at' => $sale->voided_at?->toIso8601String(),
                'void_reason' => $sale->void_reason,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function unitCard(StockUnit $unit): array
    {
        $listed = $this->pricing->convert($unit->price, $unit->location->currency);

        return [
            'id' => $unit->id,
            'reference' => $unit->reference,
            'product' => $unit->product->name,
            'brand' => $unit->product->brand,
            'status' => $unit->status->value,
            'condition' => ['grade' => $unit->condition->grade, 'name' => $unit->condition->name],
            'image' => $unit->product->getFirstMediaUrl('gallery', 'thumb') ?: null,
            'listed' => $listed ? $this->pricing->present($listed) : null,
        ];
    }
}
