<?php

namespace Tests\Feature;

use App\Enums\StockStatus;
use App\Exceptions\InvalidStockTransition;
use App\Models\ExchangeRate;
use App\Models\StockUnit;
use App\Services\Pricing;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_units_get_a_readable_reference(): void
    {
        $unit = StockUnit::factory()->create();

        $this->assertSame(sprintf('YG-%06d', $unit->id), $unit->fresh()?->reference);
    }

    public function test_transitions_record_when_each_step_happened(): void
    {
        $this->freezeSecond();
        $unit = StockUnit::factory()->create();

        $unit->transitionTo(StockStatus::InTransit);
        $this->assertSame(StockStatus::InTransit, $unit->fresh()?->status);
        $this->assertNotNull($unit->shipped_at);

        $unit->transitionTo(StockStatus::Received);
        $this->assertTrue(now()->equalTo($unit->fresh()?->received_at));
    }

    public function test_invalid_transitions_are_rejected_and_nothing_changes(): void
    {
        $unit = StockUnit::factory()->create();

        try {
            $unit->transitionTo(StockStatus::Sold);
            $this->fail('Expected an InvalidStockTransition.');
        } catch (InvalidStockTransition) {
            $this->assertSame(StockStatus::Purchased, $unit->fresh()?->status);
        }
    }

    public function test_stock_entered_as_already_in_store_gets_received_timestamps(): void
    {
        $unit = StockUnit::factory()->create(['status' => StockStatus::Received]);

        $this->assertNotNull($unit->received_at);
        $this->assertNotNull($unit->shipped_at);
    }

    public function test_cost_and_price_are_stored_in_minor_units(): void
    {
        $unit = StockUnit::factory()->create([
            'cost' => Money::of('249.99', 'GBP'),
            'price' => Money::of('339.50', 'GBP'),
        ]);

        $fresh = $unit->fresh();
        $this->assertNotNull($fresh);
        $this->assertSame(24999, $fresh->cost_amount);
        $this->assertSame(33950, $fresh->price_amount);
        $this->assertTrue($fresh->price->isEqualTo(Money::of('339.50', 'GBP')));
    }

    public function test_pricing_uses_the_latest_rate_in_force(): void
    {
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '14.000000', 'effective_at' => now()->subDays(10)]);
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.500000', 'effective_at' => now()->subDay()]);
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '99.000000', 'effective_at' => now()->addDay()]);

        $converted = (new Pricing)->forDisplay(Money::of('100.01', 'GBP'));

        $this->assertNotNull($converted);
        // 100.01 × 15.5 = 1550.155 → rounded half up to 1550.16
        $this->assertTrue($converted->isEqualTo(Money::of('1550.16', 'GHS')));
    }
}
