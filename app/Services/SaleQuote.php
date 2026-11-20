<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Brick\Money\Money;

/**
 * What a stock unit is listed at in the store's currency right now, and the
 * lowest price staff may accept.
 */
final readonly class SaleQuote
{
    public function __construct(
        public Money $listPrice,
        public Money $listed,
        public Money $floor,
        public int $floorPercent,
        public ?ExchangeRate $rate,
    ) {}

    public function accepts(Money $price): bool
    {
        return $price->getCurrency()->isEqualTo($this->floor->getCurrency())
            && $price->isGreaterThanOrEqualTo($this->floor);
    }
}
