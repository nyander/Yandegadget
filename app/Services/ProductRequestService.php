<?php

namespace App\Services;

use App\Enums\CancellationCause;
use App\Enums\Permission;
use App\Enums\RequestStatus;
use App\Enums\StockStatus;
use App\Exceptions\RequestRuleViolation;
use App\Models\Condition;
use App\Models\ProductRequest;
use App\Models\Sale;
use App\Models\StockUnit;
use App\Models\User;
use App\Notifications\CollectionReminder;
use App\Notifications\NewProductRequest;
use App\Notifications\ProductRequestUpdated;
use App\Services\Payments\GatewayException;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Customer requests: the customer pays a deposit, the shop finds the item,
 * it's reserved in store, and the deposit counts towards the price.
 */
class ProductRequestService
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly Pricing $pricing,
        private readonly RefundPolicy $refunds,
        private readonly ShopSettings $settings,
    ) {}

    /**
     * @param  array{category_id: int, condition_id: int, description: string, notes?: string|null}  $data
     */
    public function create(User $customer, array $data): ProductRequest
    {
        $condition = Condition::findOrFail($data['condition_id']);

        $request = new ProductRequest([
            'category_id' => $data['category_id'],
            'condition_id' => $condition->id,
            'description' => $data['description'],
            'notes' => $data['notes'] ?? null,
            'deposit' => $condition->deposit,
        ]);
        $request->customer_id = $customer->id;
        $request->save();

        return $request;
    }

    /**
     * Starts (or restarts) the deposit payment and returns the checkout URL.
     *
     * @throws RequestRuleViolation|GatewayException
     */
    public function startDeposit(ProductRequest $request, string $callbackUrl): string
    {
        if ($request->status !== RequestStatus::AwaitingDeposit) {
            throw new RequestRuleViolation('The deposit for this request has already been paid.');
        }

        $currency = (string) config('services.payments.currency');
        $amount = $this->pricing->convert($request->deposit, $currency);

        if ($amount === null) {
            throw new RequestRuleViolation('Deposits can\'t be taken right now. Please try again later.');
        }

        $rate = $request->deposit->getCurrency()->getCurrencyCode() === $currency
            ? null
            : $this->pricing->rate($request->deposit->getCurrency()->getCurrencyCode(), $currency)?->rate;

        return $this->payments->start($request, $request->customer, $amount, $callbackUrl, $rate);
    }

    /**
     * Called once the deposit payment is confirmed.
     */
    public function depositPaid(ProductRequest $request): void
    {
        $updated = DB::transaction(function () use ($request) {
            $request = ProductRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($request->status !== RequestStatus::AwaitingDeposit) {
                return null;
            }

            $request->status = RequestStatus::Open;
            $request->paid_at = now();
            $request->save();

            return $request;
        });

        if ($updated === null) {
            return;
        }

        Notification::send(User::permission(Permission::ManageRequests->value)->get(), new NewProductRequest($updated));
        $updated->customer->notify(new ProductRequestUpdated($updated));
    }

    /**
     * Links a stock unit to an open request. If it's already in the store it is
     * reserved straight away; otherwise it's reserved when it's checked in.
     */
    public function source(ProductRequest $request, StockUnit $unit): void
    {
        DB::transaction(function () use ($request, $unit) {
            $request = ProductRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            $unit = StockUnit::whereKey($unit->id)->lockForUpdate()->firstOrFail();

            if ($request->status !== RequestStatus::Open) {
                throw new RequestRuleViolation('Only requests the shop is still looking for can be matched.');
            }

            if (! in_array($unit->status, [StockStatus::Purchased, StockStatus::InTransit, StockStatus::Received], true)) {
                throw new RequestRuleViolation("{$unit->reference} isn't available to reserve.");
            }

            $taken = ProductRequest::where('stock_unit_id', $unit->id)
                ->whereIn('status', [RequestStatus::Sourced, RequestStatus::ReadyForCollection])
                ->exists();

            if ($taken) {
                throw new RequestRuleViolation("{$unit->reference} is already matched to another request.");
            }

            $request->stock_unit_id = $unit->id;
            $request->sourced_at = now();
            $request->status = RequestStatus::Sourced;

            if ($unit->status === StockStatus::Received) {
                $this->reserve($request, $unit);
            }

            $request->save();
        });

        $request->refresh();
        $request->customer->notify(new ProductRequestUpdated($request));
    }

    /**
     * Called when a stock unit is checked into a store.
     */
    public function unitArrived(StockUnit $unit): void
    {
        $request = ProductRequest::where('stock_unit_id', $unit->id)
            ->where('status', RequestStatus::Sourced)
            ->first();

        if ($request === null || $unit->fresh()?->status !== StockStatus::Received) {
            return;
        }

        DB::transaction(function () use ($request, $unit) {
            $this->reserve($request, $unit);
            $request->save();
        });

        $request->customer->notify(new ProductRequestUpdated($request->refresh()));
    }

    /**
     * Called by SaleService when the reserved item is sold to the customer.
     */
    public function fulfil(ProductRequest $request, Sale $sale): void
    {
        $request->status = RequestStatus::Fulfilled;
        $request->fulfilled_at = $sale->sold_at;
        $request->save();
    }

    /**
     * Called by SaleService when the sale of a requested item is voided.
     */
    public function saleVoided(ProductRequest $request): void
    {
        if ($request->status !== RequestStatus::Fulfilled || $request->stockUnit === null) {
            return;
        }

        $request->stockUnit->transitionTo(StockStatus::Reserved);
        $request->status = RequestStatus::ReadyForCollection;
        $request->fulfilled_at = null;
        $request->save();
    }

    /**
     * Cancels a request, releases any reserved item and refunds the deposit
     * as App\Services\RefundPolicy says. Customers can cancel their own
     * requests until the item is collected; only the shop can cancel as
     * "shop couldn't supply it". A null $by is the system (uncollected items).
     *
     * @throws RequestRuleViolation|GatewayException
     */
    public function cancel(ProductRequest $request, ?User $by, string $reason, CancellationCause $cause = CancellationCause::Customer): void
    {
        $isShop = $by?->can(Permission::ManageRequests->value) ?? false;

        if ($by === null && $cause !== CancellationCause::NotCollected) {
            throw new RequestRuleViolation('Only uncollected items are cancelled automatically.');
        }

        if ($by !== null && ! $isShop) {
            if ($by->id !== $request->customer_id || $cause !== CancellationCause::Customer) {
                throw new RequestRuleViolation('Only the shop can cancel this request.');
            }
        }

        if (in_array($request->status, [RequestStatus::Fulfilled, RequestStatus::Cancelled], true)) {
            throw new RequestRuleViolation("{$request->reference} can't be cancelled now.");
        }

        // Worked out before the status changes, since the fee depends on how far the request got.
        $quote = $this->refunds->quote($request, $cause);

        if ($quote !== null && $quote->refund->isPositive()) {
            $payment = $request->paidDeposit();

            if ($payment !== null) {
                $this->payments->refund($payment, $quote->refund);
            }
        }

        DB::transaction(function () use ($request, $reason, $cause) {
            $unit = $request->stockUnit;

            if ($unit !== null && $unit->status === StockStatus::Reserved) {
                $unit->transitionTo(StockStatus::Received);
            }

            $request->forceFill([
                'status' => RequestStatus::Cancelled,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'cancelled_by' => $cause,
                'stock_unit_id' => null,
            ])->save();
        });

        if ($request->paid_at !== null) {
            $request->customer->notify(new ProductRequestUpdated($request->refresh()));
        }
    }

    /**
     * Emails customers whose reserved item goes back on sale within the
     * reminder window (Shop settings), once per request. Returns how many.
     */
    public function remindUncollected(): int
    {
        $before = $this->settings->collectionReminderDays();

        if ($before <= 0) {
            return 0;
        }

        $refunds = app(RefundPolicy::class);
        $fee = $this->settings->cancellationFeePercent();
        $reminded = 0;

        ProductRequest::query()
            ->where('status', RequestStatus::ReadyForCollection)
            ->whereNull('collection_reminded_at')
            ->with('customer')
            ->each(function (ProductRequest $request) use ($refunds, $before, $fee, &$reminded) {
                $collectBy = $refunds->collectBy($request);

                if ($collectBy === null || $collectBy->isPast() || now()->lt($collectBy->subDays($before)->startOfDay())) {
                    return;
                }

                $request->customer->notify(new CollectionReminder($request, $collectBy, $fee));
                $request->forceFill(['collection_reminded_at' => now()])->save();
                $reminded++;
            });

        return $reminded;
    }

    /**
     * Cancels requests whose item has waited in store past the collection
     * window, putting the item back on sale. Returns how many were released.
     */
    public function releaseUncollected(): int
    {
        $days = $this->settings->collectionDays();
        $released = 0;

        ProductRequest::query()
            ->where('status', RequestStatus::ReadyForCollection)
            ->where('ready_at', '<', now()->subDays($days)->startOfDay())
            ->each(function (ProductRequest $request) use ($days, &$released) {
                try {
                    $this->cancel($request, null, "Not collected within {$days} days of being ready, so it has gone back on sale.", CancellationCause::NotCollected);
                    $released++;
                } catch (RequestRuleViolation|GatewayException $e) {
                    report($e);
                }
            });

        return $released;
    }

    private function reserve(ProductRequest $request, StockUnit $unit): void
    {
        $unit->transitionTo(StockStatus::Reserved);
        $request->status = RequestStatus::ReadyForCollection;
        $request->ready_at = now();
        // A fresh collection window gets its own reminder.
        $request->collection_reminded_at = null;
    }
}
