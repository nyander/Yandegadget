<?php

namespace App\Services;

use Brick\Money\Money;

/**
 * What happens to a paid deposit if a request is cancelled now.
 */
final readonly class RefundQuote
{
    public function __construct(
        public Money $paid,
        public Money $refund,
        public Money $kept,
        public int $feePercent,
    ) {}

    public function isFull(): bool
    {
        return $this->kept->isZero();
    }
}
