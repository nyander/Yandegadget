<?php

namespace App\Services;

use App\Enums\CancellationCause;
use App\Enums\RequestStatus;
use App\Models\ProductRequest;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Carbon\CarbonImmutable;

/**
 * Deposit refunds when a customer request is cancelled:
 *
 * - The shop cancels (can't find it, or it isn't as described): full refund.
 * - The customer cancels while the shop is still looking: full refund.
 * - The customer cancels after the item has been found, or doesn't collect it
 *   within the collection window: the cancellation fee (a share of the
 *   deposit, an admin setting) is kept and the rest refunded.
 *
 * The fee is rounded down to the pesewa, so any rounding favours the customer.
 */
class RefundPolicy
{
    public function __construct(private readonly ShopSettings $settings) {}

    /**
     * Null when no deposit has been paid.
     */
    public function quote(ProductRequest $request, CancellationCause $cause): ?RefundQuote
    {
        $payment = $request->paidDeposit();

        if ($payment === null || $payment->refunded_at !== null) {
            return null;
        }

        $paid = $payment->money;
        $feePercent = $this->feeApplies($request, $cause) ? $this->settings->cancellationFeePercent() : 0;

        $kept = Money::of(
            $paid->getAmount()->multipliedBy($feePercent)->dividedBy(100, $paid->getCurrency()->getDefaultFractionDigits(), RoundingMode::Down),
            $paid->getCurrency(),
        );

        return new RefundQuote($paid, $paid->minus($kept), $kept, $feePercent);
    }

    /**
     * The last day to collect a reserved item, or null if it isn't ready yet.
     */
    public function collectBy(ProductRequest $request): ?CarbonImmutable
    {
        if ($request->status !== RequestStatus::ReadyForCollection || $request->ready_at === null) {
            return null;
        }

        return $request->ready_at->addDays($this->settings->collectionDays())->endOfDay();
    }

    private function feeApplies(ProductRequest $request, CancellationCause $cause): bool
    {
        if ($cause === CancellationCause::Shop) {
            return false;
        }

        return in_array($request->status, [RequestStatus::Sourced, RequestStatus::ReadyForCollection], true);
    }
}
