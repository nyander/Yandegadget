<?php

namespace Database\Factories;

use App\Enums\StockStatus;
use App\Models\Condition;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockUnit;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockUnit>
 */
class StockUnitFactory extends Factory
{
    public function definition(): array
    {
        $cost = fake()->numberBetween(40, 600);

        return [
            'product_id' => Product::factory(),
            'condition_id' => fn () => Condition::query()->inRandomOrder()->value('id'),
            'location_id' => fn () => Location::query()->where('code', 'ACC-LEGON')->value('id'),
            'status' => StockStatus::Purchased,
            'cost' => Money::of($cost, 'GBP'),
            'price' => Money::of((int) round($cost * 1.35), 'GBP'),
            'purchased_at' => now()->subDays(fake()->numberBetween(5, 60))->toDateString(),
        ];
    }

    public function status(StockStatus $status): static
    {
        return $this->state(fn () => [
            'status' => $status,
            'received_at' => in_array($status, [StockStatus::Received, StockStatus::Reserved, StockStatus::Sold], true) ? now() : null,
            'sold_at' => $status === StockStatus::Sold ? now() : null,
        ]);
    }

    public function received(): static
    {
        return $this->status(StockStatus::Received);
    }

    public function sold(): static
    {
        return $this->status(StockStatus::Sold);
    }

    public function priced(int|string $gbp): static
    {
        return $this->state(['price' => Money::of($gbp, 'GBP')]);
    }
}
