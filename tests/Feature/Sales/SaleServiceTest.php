<?php

namespace Tests\Feature\Sales;

use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Exceptions\SaleRuleViolation;
use App\Models\ExchangeRate;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\SaleService;
use App\Services\ShopSettings;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $sales;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $this->sales = app(SaleService::class);
        $this->staff = User::factory()->create()->assignRole(Role::Staff->value);
    }

    private function unitListedAt(int|string $gbp, StockStatus $status = StockStatus::Received): StockUnit
    {
        return StockUnit::factory()->status($status)->priced($gbp)->create();
    }

    public function test_quote_converts_the_list_price_and_applies_the_70_percent_floor(): void
    {
        $quote = $this->sales->quote($this->unitListedAt(340));

        $this->assertTrue($quote->listed->isEqualTo(Money::of(5100, 'GHS')));
        $this->assertTrue($quote->floor->isEqualTo(Money::of(3570, 'GHS')));
        $this->assertSame(70, $quote->floorPercent);
    }

    public function test_the_floor_rounds_up_to_the_pesewa_so_it_is_never_undercut(): void
    {
        // £82.31 × 15 = GH₵1,234.65; 70% = 864.255 → 864.26
        $quote = $this->sales->quote($this->unitListedAt('82.31'));

        $this->assertTrue($quote->floor->isEqualTo(Money::of('864.26', 'GHS')));
    }

    public function test_the_admin_can_change_the_floor(): void
    {
        app(ShopSettings::class)->set(ShopSettings::NEGOTIATION_FLOOR, 85);

        $quote = $this->sales->quote($this->unitListedAt(100));

        $this->assertSame(85, $quote->floorPercent);
        $this->assertTrue($quote->floor->isEqualTo(Money::of(1275, 'GHS')));
    }

    public function test_a_sale_at_the_floor_is_recorded_with_a_snapshot_of_the_rate(): void
    {
        $unit = $this->unitListedAt(340);

        $sale = $this->sales->record($unit, Money::of(3570, 'GHS'), PaymentMethod::MobileMoney, $this->staff, [
            'payment_reference' => 'MOMO-1',
            'customer_name' => 'Kofi',
        ]);

        $this->assertSame(sprintf('SA-%06d', $sale->id), $sale->fresh()?->reference);
        $this->assertSame(357000, $sale->price_paid_amount);
        $this->assertSame(510000, $sale->listed_amount);
        $this->assertSame(357000, $sale->floor_amount);
        $this->assertSame(34000, $sale->list_price_amount);
        $this->assertSame('GBP', $sale->list_price_currency);
        $this->assertSame('15.000000', $sale->fresh()?->rate);
        $this->assertSame(70, $sale->floor_percent);
        $this->assertSame('MOMO-1', $sale->payment_reference);
        $this->assertSame(30, $sale->discountPercent());

        $unit->refresh();
        $this->assertSame(StockStatus::Sold, $unit->status);
        $this->assertNotNull($unit->sold_at);
        $this->assertFalse($unit->product->visibleUnits()->exists());
    }

    public function test_a_price_below_the_floor_is_rejected_and_nothing_changes(): void
    {
        $unit = $this->unitListedAt(340);

        try {
            $this->sales->record($unit, Money::of('3569.99', 'GHS'), PaymentMethod::Cash, $this->staff);
            $this->fail('A price below the floor should be rejected.');
        } catch (SaleRuleViolation $e) {
            $this->assertStringContainsString('3,570.00', $e->getMessage());
        }

        $this->assertSame(StockStatus::Received, $unit->fresh()?->status);
        $this->assertSame(0, $unit->sales()->count());
    }

    public function test_later_rate_changes_do_not_change_recorded_sales(): void
    {
        $sale = $this->sales->record($this->unitListedAt(100), Money::of(1500, 'GHS'), PaymentMethod::Cash, $this->staff);

        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '20.000000', 'effective_at' => now()]);

        $this->assertSame(150000, $sale->fresh()?->listed_amount);
        $this->assertSame('15.000000', $sale->fresh()?->rate);
    }

    public function test_cash_sales_drop_any_payment_reference(): void
    {
        $sale = $this->sales->record($this->unitListedAt(100), Money::of(1500, 'GHS'), PaymentMethod::Cash, $this->staff, ['payment_reference' => 'ignored']);

        $this->assertNull($sale->payment_reference);
    }

    public function test_reserved_items_can_be_sold(): void
    {
        $unit = $this->unitListedAt(100, StockStatus::Reserved);

        $this->sales->record($unit, Money::of(1500, 'GHS'), PaymentMethod::Card, $this->staff);

        $this->assertSame(StockStatus::Sold, $unit->fresh()?->status);
    }

    public function test_items_not_in_the_store_cannot_be_sold(): void
    {
        foreach ([StockStatus::Purchased, StockStatus::InTransit, StockStatus::Sold] as $status) {
            try {
                $this->sales->record($this->unitListedAt(100, $status), Money::of(1500, 'GHS'), PaymentMethod::Cash, $this->staff);
                $this->fail("A {$status->value} item should not be sellable.");
            } catch (SaleRuleViolation) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_the_price_must_be_in_the_store_currency(): void
    {
        $this->expectException(SaleRuleViolation::class);

        $this->sales->record($this->unitListedAt(100), Money::of(100, 'GBP'), PaymentMethod::Cash, $this->staff);
    }

    public function test_sales_need_an_exchange_rate(): void
    {
        ExchangeRate::query()->delete();

        $this->expectException(SaleRuleViolation::class);
        $this->expectExceptionMessage('exchange rate');

        $this->sales->quote($this->unitListedAt(100));
    }

    public function test_future_dates_are_rejected(): void
    {
        $this->expectException(SaleRuleViolation::class);

        $this->sales->record($this->unitListedAt(100), Money::of(1500, 'GHS'), PaymentMethod::Cash, $this->staff, ['sold_at' => now()->addDay()]);
    }

    public function test_voiding_puts_the_item_back_in_store_and_keeps_the_record(): void
    {
        $admin = User::factory()->create()->assignRole(Role::Admin->value);
        $unit = $this->unitListedAt(100);
        $sale = $this->sales->record($unit, Money::of(1500, 'GHS'), PaymentMethod::Cash, $this->staff);

        $this->sales->void($sale, $admin, 'Wrong item scanned');

        $sale->refresh();
        $this->assertTrue($sale->isVoided());
        $this->assertSame($admin->id, $sale->voided_by);
        $this->assertSame(StockStatus::Received, $unit->fresh()?->status);
        $this->assertNull($unit->fresh()?->sold_at);
        $this->assertNull($unit->fresh()?->currentSale());

        $this->expectException(SaleRuleViolation::class);
        $this->sales->void($sale, $admin, 'Again');
    }
}
