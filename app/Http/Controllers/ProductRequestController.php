<?php

namespace App\Http\Controllers;

use App\Enums\CancellationCause;
use App\Enums\PaymentStatus;
use App\Enums\RequestStatus;
use App\Exceptions\RequestRuleViolation;
use App\Http\Presenters\RequestPresenter;
use App\Http\Requests\StoreProductRequestRequest;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\User;
use App\Services\Payments\GatewayException;
use App\Services\Pricing;
use App\Services\ProductRequestService;
use App\Services\RefundPolicy;
use Brick\Money\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Customers asking the shop to find an item, with a deposit taken online.
 */
class ProductRequestController extends Controller
{
    public function __construct(
        private readonly ProductRequestService $requests,
        private readonly Pricing $pricing,
    ) {}

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('requests/index', [
            'requests' => ProductRequest::where('customer_id', $user->id)
                ->with(['category', 'condition'])
                ->latest()
                ->get()
                ->map(RequestPresenter::listItem(...))
                ->all(),
        ]);
    }

    public function create(): Response
    {
        $currency = (string) config('services.payments.currency');

        $conditions = Condition::orderBy('sort_order')->get()->map(function (Condition $c) use ($currency) {
            $deposit = $this->pricing->convert($c->deposit, $currency);

            return [
                'id' => $c->id,
                'grade' => $c->grade,
                'name' => $c->name,
                'description' => $c->description,
                'deposit' => $deposit ? $this->pricing->present($deposit) : null,
            ];
        });

        return Inertia::render('requests/create', [
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'conditions' => $conditions->all(),
            'depositsAvailable' => $conditions->every(fn (array $c) => $c['deposit'] !== null),
        ]);
    }

    public function store(StoreProductRequestRequest $request): HttpResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{category_id: int, condition_id: int, description: string, notes?: string|null} $data */
        $data = $request->validated();
        $productRequest = $this->requests->create($user, $data);

        return $this->checkout($productRequest);
    }

    public function show(ProductRequest $productRequest, RefundPolicy $refunds): Response
    {
        Gate::authorize('view', $productRequest);

        $productRequest->load(['category', 'condition', 'stockUnit.product', 'stockUnit.location', 'latestPayment']);
        $payment = $productRequest->paidDeposit() ?? $productRequest->latestPayment;
        $unit = $productRequest->stockUnit;
        $currency = (string) config('services.payments.currency');
        $estimate = $this->pricing->convert($productRequest->deposit, $currency);

        return Inertia::render('requests/show', [
            'request' => [
                'id' => $productRequest->id,
                'reference' => $productRequest->reference,
                'description' => $productRequest->description,
                'notes' => $productRequest->notes,
                'status' => $productRequest->status->value,
                'status_label' => $productRequest->status->getLabel(),
                'category' => $productRequest->category->name,
                'condition' => [
                    'grade' => $productRequest->condition->grade,
                    'name' => $productRequest->condition->name,
                    'description' => $productRequest->condition->description,
                ],
                'deposit' => $payment ? $this->pricing->present($payment->money) : ($estimate ? $this->pricing->present($estimate) : null),
                'created_at' => $productRequest->created_at?->toIso8601String(),
                'paid_at' => $productRequest->paid_at?->toIso8601String(),
                'sourced_at' => $productRequest->sourced_at?->toIso8601String(),
                'ready_at' => $productRequest->ready_at?->toIso8601String(),
                'fulfilled_at' => $productRequest->fulfilled_at?->toIso8601String(),
                'cancelled_at' => $productRequest->cancelled_at?->toIso8601String(),
                'cancel_reason' => $productRequest->cancel_reason,
                'item' => $unit ? [
                    'reference' => $unit->reference,
                    'product' => $unit->product->name,
                    'shop_slug' => Product::visibleInShop()->whereKey($unit->product_id)->exists() ? $unit->product->slug : null,
                    'price' => ($listed = $this->pricing->convert($unit->price, $unit->location->currency)) ? $this->pricing->present($listed) : null,
                ] : null,
                'payment' => $payment ? [
                    'status' => $payment->status->value,
                    'status_label' => $payment->status->getLabel(),
                    'channel' => $payment->channel,
                    'refunded' => $payment->status === PaymentStatus::Refunded,
                ] : null,
                'can_pay' => $productRequest->status === RequestStatus::AwaitingDeposit,
                'can_cancel' => in_array($productRequest->status, [RequestStatus::AwaitingDeposit, RequestStatus::Open, RequestStatus::Sourced, RequestStatus::ReadyForCollection], true),
                'cancel_quote' => ($quote = $refunds->quote($productRequest, CancellationCause::Customer)) ? [
                    'refund' => $this->pricing->present($quote->refund),
                    'kept' => $quote->kept->isZero() ? null : $this->pricing->present($quote->kept),
                    'fee_percent' => $quote->feePercent,
                ] : null,
                'collect_by' => $refunds->collectBy($productRequest)?->toIso8601String(),
                'refunded' => $payment?->refunded_at !== null ? $this->pricing->present(Money::ofMinor($payment->refunded_amount, $payment->currency)) : null,
            ],
        ]);
    }

    public function pay(ProductRequest $productRequest): HttpResponse
    {
        Gate::authorize('pay', $productRequest);

        return $this->checkout($productRequest);
    }

    public function cancel(Request $request, ProductRequest $productRequest): RedirectResponse
    {
        Gate::authorize('pay', $productRequest);

        /** @var User $user */
        $user = $request->user();

        try {
            $this->requests->cancel(
                $productRequest,
                $user,
                $productRequest->paid_at === null ? 'Cancelled by the customer before paying.' : 'Cancelled by the customer.',
            );
        } catch (RequestRuleViolation|GatewayException $e) {
            return back()->withErrors(['request' => $e->getMessage()]);
        }

        return to_route('requests.show', $productRequest);
    }

    /**
     * Where Paystack sends the customer back. The redirect proves nothing on
     * its own, so the payment is checked with Paystack before anything changes.
     */
    private function checkout(ProductRequest $productRequest): HttpResponse
    {
        try {
            $url = $this->requests->startDeposit($productRequest, route('payments.callback'));
        } catch (RequestRuleViolation|GatewayException $e) {
            return to_route('requests.show', $productRequest)->withErrors(['payment' => $e->getMessage()]);
        }

        return Inertia::location($url);
    }
}
