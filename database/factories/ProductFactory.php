<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $brand = fake()->randomElement(['Apple', 'Samsung', 'Sony', 'LG', 'Dell', 'HP', 'Canon', 'Nintendo']);

        return [
            'category_id' => fn () => Category::query()->inRandomOrder()->value('id'),
            'name' => $brand.' '.fake()->bothify('?? ###'),
            'brand' => $brand,
            'model' => fake()->bothify('??-####'),
            'description' => fake()->paragraph(),
            'is_featured' => false,
            'is_published' => true,
        ];
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }
}
