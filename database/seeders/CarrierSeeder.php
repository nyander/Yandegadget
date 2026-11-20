<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

/**
 * Freight companies from the 2020 seeder. Details should be checked by the
 * admin before use; MANC's 2020 address and phone were placeholders, so only
 * its name and email are kept.
 */
class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        $carriers = [
            ['DFS Worldwide Logistics', ['address' => 'Unit 7, Marlin Park, Central Way, Feltham', 'postcode' => 'TW14 0XD', 'phone' => '0161 818 9600', 'email' => 'sales@dfsworldwide.com']],
            ['MANC Global Logistics', ['email' => 'hello@manclogistics.co.uk']],
            ['Royal Mail Group Ltd', ['address' => 'Moorside', 'postcode' => 'CO1 2GB', 'phone' => '0345 602 1021', 'email' => 'enquiry@royalmail.co.uk']],
            ['DHL Express', ['address' => 'Units D/E/F, Caxton Court, Newcomen Way, Severalls Park', 'postcode' => 'CO4 9QJ', 'phone' => '0844 248 0844']],
        ];

        foreach ($carriers as [$name, $details]) {
            Carrier::firstOrCreate(['name' => $name], $details);
        }
    }
}
