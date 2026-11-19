<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Data the app can't run without. Idempotent: run it on every deploy with
 * `php artisan db:seed --class=ReferenceDataSeeder --force`.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            LocationSeeder::class,
            CatalogueReferenceSeeder::class,
            TransactionTypeSeeder::class,
        ]);
    }
}
