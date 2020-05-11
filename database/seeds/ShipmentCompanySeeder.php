<?php

use Illuminate\Database\Seeder;
use App\ShipCompany;

class ShipmentCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ShipCompany::truncate();
        ShipCompany::create(['name' => 'DFS Wordwide Logistics',
                             'address' => 'Unit 7, Marlin Park Central Way Feltham',
                             'postcode' => 'TW14 0XD',
                             'number' => '01618189600',
                             'extra_information' => 'Email: sales@dfsworldwide.com']);  
        ShipCompany::create(['name' => 'MANC Global Logistics',
                             'address' => 'Address road 2',
                             'postcode' => 'M22 4RW',
                             'number' => '12345678910',
                             'extra_information' => 'hello@manclogistics.co.uk']);   
    }
}
