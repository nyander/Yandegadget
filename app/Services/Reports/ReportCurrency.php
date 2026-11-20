<?php

namespace App\Services\Reports;

use App\Models\ExchangeRate;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use DateTimeInterface;

/**
 * Converts amounts into the report currency (GHS) at the rate that was in
 * force on the day, so last year's figures don't move when the rate does.
 */
class ReportCurrency
{
    /** @var array<string, string|null> */
    private array $rates = [];

    /** @var array<string, true> */
    private array $missing = [];

    public function code(): string
    {
        return (string) config('shop.display_currency');
    }

    public function zero(): Money
    {
        return Money::zero($this->code());
    }

    /**
     * The amount in the report currency, or zero (with a note) when no rate exists at all.
     */
    public function convert(Money $money, DateTimeInterface $at): Money
    {
        $from = $money->getCurrency()->getCurrencyCode();
        $to = $this->code();

        if ($from === $to) {
            return $money;
        }

        $rate = $this->rate($from, $at);

        if ($rate === null) {
            $this->missing["{$from}→{$to}"] = true;

            return $this->zero();
        }

        return self::multiply($money, $rate, $to);
    }

    public static function multiply(Money $money, BigDecimal|string $rate, string $to): Money
    {
        return Money::of($money->getAmount()->multipliedBy($rate), $to, roundingMode: RoundingMode::HalfUp);
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return array_map(
            fn (string $pair) => "No {$pair} exchange rate has been set, so amounts in that currency are left out. Add one under Settings → Exchange rates.",
            array_keys($this->missing),
        );
    }

    private function rate(string $from, DateTimeInterface $at): ?string
    {
        $key = $from.'|'.$at->format('Y-m-d');

        if (! array_key_exists($key, $this->rates)) {
            // Before the first rate was entered, fall back to the earliest one.
            $rate = ExchangeRate::asOf($from, $this->code(), $at)
                ?? ExchangeRate::query()
                    ->where('base_currency', $from)
                    ->where('quote_currency', $this->code())
                    ->oldest('effective_at')
                    ->first();

            $this->rates[$key] = $rate?->rate;
        }

        return $this->rates[$key];
    }
}
