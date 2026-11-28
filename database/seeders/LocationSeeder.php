<?php

namespace Database\Seeders;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /** The starting point for shipments a supplier sends straight to Accra. */
    public const DIRECT = 'SUPPLIER-DIRECT';

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

        // Stock a supplier (e.g. on Alibaba) ships straight to Accra, not via the UK base.
        Location::updateOrCreate(['code' => self::DIRECT], [
            'name' => 'Direct from supplier',
            'type' => LocationType::Buying,
            'country_code' => 'ZZ',
            'currency' => 'GBP',
            'timezone' => 'UTC',
            'address' => null,
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
