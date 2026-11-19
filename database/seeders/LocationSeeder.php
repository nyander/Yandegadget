<?php

namespace Database\Seeders;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::updateOrCreate(['code' => 'ACC-LEGON'], [
            'name' => 'Legon flagship store',
            'type' => LocationType::Store,
            'country_code' => 'GH',
            'currency' => 'GHS',
            'timezone' => 'Africa/Accra',
            'address' => '27 Ayawaso, Legon Boundary, Accra, Ghana',
        ]);

        Location::updateOrCreate(['code' => 'UK-BUYING'], [
            'name' => 'UK buying',
            'type' => LocationType::Buying,
            'country_code' => 'GB',
            'currency' => 'GBP',
            'timezone' => 'Europe/London',
            'address' => null,
        ]);
    }
}
