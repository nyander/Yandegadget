<?php

namespace App\Services\Reports;

use App\Enums\WagePeriod;
use App\Models\StaffWage;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Carbon\CarbonImmutable;

/**
 * Prorates a wage into a date range (both ends inclusive). A monthly wage
 * costs its full rate for a full calendar month, whatever its length.
 */
class WageCalculator
{
    public function cost(StaffWage $wage, CarbonImmutable $from, CarbonImmutable $to): Money
    {
        $start = $from->startOfDay()->max($wage->starts_on->startOfDay());
        $end = $to->startOfDay();

        if ($wage->ends_on !== null) {
            $end = $end->min($wage->ends_on->startOfDay());
        }

        $currency = $wage->rate->getCurrency()->getCurrencyCode();

        if ($end->lessThan($start)) {
            return Money::zero($currency);
        }

        $rate = $wage->rate->getAmount();

        $total = match ($wage->period) {
            WagePeriod::Daily => $rate->multipliedBy(self::days($start, $end)),
            WagePeriod::Weekly => $rate->multipliedBy(self::days($start, $end))->dividedBy(7, 6, RoundingMode::HalfUp),
            WagePeriod::Monthly => $this->monthly($rate, $start, $end),
        };

        return Money::of($total, $currency, roundingMode: RoundingMode::HalfUp);
    }

    private function monthly(BigDecimal $rate, CarbonImmutable $start, CarbonImmutable $end): BigDecimal
    {
        $total = BigDecimal::zero();
        $cursor = $start;

        while ($cursor->lessThanOrEqualTo($end)) {
            $monthEnd = $cursor->endOfMonth()->startOfDay()->min($end);
            $share = BigDecimal::of(self::days($cursor, $monthEnd))
                ->dividedBy($cursor->daysInMonth, 10, RoundingMode::HalfUp);

            $total = $total->plus($rate->multipliedBy($share));
            $cursor = $monthEnd->addDay();
        }

        return $total;
    }

    private static function days(CarbonImmutable $start, CarbonImmutable $end): int
    {
        return (int) $start->diffInDays($end) + 1;
    }
}
