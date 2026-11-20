<?php

namespace Tests\Unit;

use App\Enums\StockStatus;
use PHPUnit\Framework\TestCase;

class StockStatusTest extends TestCase
{
    public function test_the_normal_journey_from_uk_purchase_to_sale_is_allowed(): void
    {
        $journey = [StockStatus::Purchased, StockStatus::InTransit, StockStatus::Received, StockStatus::Reserved, StockStatus::Sold];

        for ($i = 0; $i < count($journey) - 1; $i++) {
            $this->assertTrue($journey[$i]->canTransitionTo($journey[$i + 1]), "{$journey[$i]->value} -> {$journey[$i + 1]->value}");
        }
    }

    public function test_items_cannot_be_sold_before_they_arrive_in_ghana(): void
    {
        $this->assertFalse(StockStatus::Purchased->canTransitionTo(StockStatus::Sold));
        $this->assertFalse(StockStatus::InTransit->canTransitionTo(StockStatus::Sold));
    }

    public function test_written_off_is_final(): void
    {
        $this->assertSame([], StockStatus::WrittenOff->allowedTransitions());
    }

    public function test_only_received_and_reserved_stock_is_visible_to_customers(): void
    {
        $this->assertSame(['received', 'reserved'], StockStatus::customerVisibleValues());
        $this->assertFalse(StockStatus::Sold->isVisibleToCustomers());
        $this->assertFalse(StockStatus::InTransit->isVisibleToCustomers());
    }
}
