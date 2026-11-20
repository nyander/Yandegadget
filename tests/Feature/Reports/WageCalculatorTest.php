<?php

namespace Tests\Feature\Reports;

use App\Enums\WagePeriod;
use App\Models\StaffWage;
use App\Services\Reports\WageCalculator;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class WageCalculatorTest extends TestCase
{
    private function wage(string $amount, WagePeriod $period, string $starts, ?string $ends = null): StaffWage
    {
        return new StaffWage([
            'name' => 'Ama',
            'rate' => Money::of($amount, 'GHS'),
            'period' => $period,
            'starts_on' => $starts,
            'ends_on' => $ends,
        ]);
    }

    private function cost(StaffWage $wage, string $from, string $to): string
    {
        return (string) app(WageCalculator::class)
            ->cost($wage, CarbonImmutable::parse($from), CarbonImmutable::parse($to))
            ->getAmount();
    }

    public function test_a_daily_wage_counts_both_ends_of_the_range(): void
    {
        $this->assertSame('100.00', $this->cost($this->wage('10', WagePeriod::Daily, '2026-01-01'), '2026-03-01', '2026-03-10'));
    }

    public function test_a_weekly_wage_is_spread_over_seven_days(): void
    {
        $this->assertSame('140.00', $this->cost($this->wage('70', WagePeriod::Weekly, '2026-01-01'), '2026-03-01', '2026-03-14'));
        $this->assertSame('30.00', $this->cost($this->wage('70', WagePeriod::Weekly, '2026-01-01'), '2026-03-01', '2026-03-03'));
    }

    public function test_a_monthly_wage_costs_the_full_rate_for_a_full_month_of_any_length(): void
    {
        $wage = $this->wage('1500', WagePeriod::Monthly, '2026-01-01');

        $this->assertSame('1500.00', $this->cost($wage, '2026-02-01', '2026-02-28'));
        $this->assertSame('1500.00', $this->cost($wage, '2026-03-01', '2026-03-31'));
        $this->assertSame('750.00', $this->cost($wage, '2026-04-01', '2026-04-15'));
        // Half of March and all of April.
        $this->assertSame('2225.81', $this->cost($wage, '2026-03-17', '2026-04-30'));
    }

    public function test_only_the_days_the_wage_was_paid_are_counted(): void
    {
        $wage = $this->wage('10', WagePeriod::Daily, '2026-03-05', '2026-03-08');

        $this->assertSame('40.00', $this->cost($wage, '2026-03-01', '2026-03-31'));
        $this->assertSame('0.00', $this->cost($wage, '2026-04-01', '2026-04-30'));
    }
}
