<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([ReferenceDataSeeder::class, CarrierSeeder::class]);

        if (app()->isProduction()) {
            return;
        }

        // Local demo accounts, one per role. Password for all: "password".
        $legon = Location::where('code', 'ACC-LEGON')->first();

        $demoUsers = [
            [Role::Admin, 'Demo Admin', 'admin@example.test', $legon?->id],
            [Role::Staff, 'Demo Staff', 'staff@example.test', $legon?->id],
            [Role::Customer, 'Demo Customer', 'customer@example.test', null],
            [Role::Supplier, 'Demo Supplier', 'supplier@example.test', null],
        ];

        foreach ($demoUsers as [$role, $name, $email, $locationId]) {
            $user = User::where('email', $email)->first()
                ?? User::factory()->create(['name' => $name, 'email' => $email]);

            $user->forceFill(['location_id' => $locationId])->save();
            $user->syncRoles([$role->value]);
        }

        $this->call(DemoCatalogueSeeder::class);
    }
}
