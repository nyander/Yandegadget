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
        ShipCompany::create(['name' => 'Shipment 1',
                             'address' => 'Address road 1',
                             'postcode' => 'SC1 1CS',
                             'number' => '12345678910',
                             'extra_information' => 'This company are negotiable']);  
        ShipCompany::create(['name' => 'Shipment 2',
                             'address' => 'Address road 2',
                             'postcode' => 'SC2 2CS',
                             'number' => '12345678910',
                             'extra_information' => 'This company are negotiable']);   
    }
}
