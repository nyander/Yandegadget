<?php

namespace Database\Factories;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->city().' store',
            'code' => Str::upper(Str::random(8)),
            'type' => LocationType::Store,
            'country_code' => 'GH',
            'currency' => 'GHS',
            'timezone' => 'Africa/Accra',
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}
