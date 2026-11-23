<?php

namespace Tests\Feature\Reports;

use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Models\Condition;
use App\Models\ExchangeRate;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\Reports\SalesReport;
use App\Services\SaleService;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-06-15 12:00'));
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '10.000000', 'effective_at' => '2026-01-01']);
    }

    public function test_summary_and_breakdowns_cover_completed_sales_in_the_range(): void
    {
        $staff = User::factory()->create()->assignRole(Role::Staff->value);
        $sales = app(SaleService::class);

        foreach ([[50, 100, 1000], [30, 60, 450]] as [$cost, $list, $paid]) {
            $unit = StockUnit::factory()->received()->create(['cost' => Money::of($cost, 'GBP'), 'price' => Money::of($list, 'GBP')]);
            $sales->record($unit, Money::of($paid, 'GHS'), PaymentMethod::Cash, $staff, ['sold_at' => CarbonImmutable::parse('2026-06-10')]);
        }

        $report = app(SalesReport::class);
        $summary = $report->summary(CarbonImmutable::parse('2026-06-01'), CarbonImmutable::parse('2026-06-30'));

        $this->assertSame(2, $summary['units']);
        $this->assertSame('1450.00', (string) $summary['revenue']->getAmount());
        $this->assertSame('650.00', (string) $summary['profit']->getAmount());
        $this->assertEqualsWithDelta(44.83, $summary['marginPercent'], 0.01);
        // 0% and 25% off the listed price.
        $this->assertSame(12.5, $summary['averageDiscount']);

        $byStore = $report->breakdown(CarbonImmutable::parse('2026-06-01'), CarbonImmutable::parse('2026-06-30'), 'location');
        $this->assertCount(1, $byStore);
        $this->assertSame(2, $byStore[0]['units']);

        $empty = $report->summary(CarbonImmutable::parse('2026-05-01'), CarbonImmutable::parse('2026-05-31'));
        $this->assertSame(0, $empty['units']);
        $this->assertNull($empty['marginPercent']);

        $months = $report->monthly();
        $this->assertCount(12, $months);
        $this->assertSame('Jun 2026', $months[11]['month']);
        $this->assertSame(1450.0, $months[11]['revenue']);
    }

    public function test_breakdowns_list_conditions_by_grade_and_the_rest_by_sales(): void
    {
        $staff = User::factory()->create()->assignRole(Role::Staff->value);
        $sales = app(SaleService::class);
        $conditions = Condition::orderBy('sort_order')->get();
        $best = $conditions->first();
        $worst = $conditions->last();
        $this->assertNotNull($best);
        $this->assertNotNull($worst);

        // The worst grade sells for more, so a sales order would put it first.
        foreach ([[$best, 100], [$worst, 500]] as [$condition, $paid]) {
            $unit = StockUnit::factory()->received()->create([
                'condition_id' => $condition->id,
                'cost' => Money::of(5, 'GBP'),
                'price' => Money::of($paid / 10, 'GBP'),
            ]);
            $sales->record($unit, Money::of($paid, 'GHS'), PaymentMethod::Cash, $staff, ['sold_at' => CarbonImmutable::parse('2026-06-10')]);
        }

        $report = app(SalesReport::class);
        $from = CarbonImmutable::parse('2026-06-01');
        $to = CarbonImmutable::parse('2026-06-30');

        $this->assertSame([$best->name, $worst->name], array_column($report->breakdown($from, $to, 'condition'), 'label'));

        $byCategory = $report->breakdown($from, $to, 'category');
        $revenues = array_map(fn (array $row) => $row['revenue']->getMinorAmount()->toInt(), $byCategory);
        $sorted = $revenues;
        rsort($sorted);
        $this->assertSame($sorted, $revenues);
    }

    public function test_stock_age_buckets_items_in_store_by_days_since_arrival(): void
    {
        foreach ([5, 45, 120] as $days) {
            StockUnit::factory()->received()->create(['cost' => Money::of(10, 'GBP')])
                ->forceFill(['received_at' => now()->subDays($days)])->save();
        }
        StockUnit::factory()->status(StockStatus::InTransit)->create();

        $stock = app(SalesReport::class)->stockAge();

        $this->assertSame(3, $stock['units']);
        $this->assertSame('300.00', (string) $stock['value']->getAmount());
        $this->assertSame([1, 1, 0, 1], array_column($stock['buckets'], 'units'));
        $this->assertSame(56.7, $stock['averageDays']);
    }
}
