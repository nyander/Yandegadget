<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

/**
 * Converts GBP list prices into the customer's display currency (GHS) at the
 * latest exchange rate, and formats money for the frontend.
 */
class Pricing
{
    /** @var array<string, ExchangeRate|null> */
    private array $rates = [];

    /**
     * The price a customer sees, or null when no rate has been set yet.
     */
    public function forDisplay(Money $listPrice): ?Money
    {
        $target = (string) config('shop.display_currency');

        return $this->convert($listPrice, $target);
    }

    public function convert(Money $money, string $to): ?Money
    {
        $from = $money->getCurrency()->getCurrencyCode();

        if ($from === $to) {
            return $money;
        }

        $rate = $this->rate($from, $to);

        if ($rate === null) {
            return null;
        }

        return Money::of(
            $money->getAmount()->multipliedBy($rate->rate),
            $to,
            roundingMode: RoundingMode::HalfUp,
        );
    }

    public function rate(string $from, string $to): ?ExchangeRate
    {
        return $this->rates["{$from}:{$to}"] ??= ExchangeRate::current($from, $to);
    }

    /**
     * @return array{amount: int, currency: string, formatted: string}
     */
    public function present(Money $money): array
    {
        return [
            'amount' => $money->getMinorAmount()->toInt(),
            'currency' => $money->getCurrency()->getCurrencyCode(),
            'formatted' => $money->formatToLocale((string) config('shop.display_locale')),
        ];
    }

    /**
     * The display price when a rate exists, otherwise the list price.
     *
     * @return array{amount: int, currency: string, formatted: string}
     */
    public function presentForCustomer(Money $listPrice): array
    {
        return $this->present($this->forDisplay($listPrice) ?? $listPrice);
    }
}
