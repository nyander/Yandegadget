<?php

use Illuminate\Database\Seeder;
use App\Supplier;

class SupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Supplier::truncate();
        Supplier::create(['name' => 'Supplier 1',
                          'address' => 'Address road 1',
                          'contact' => '123456',
                          'email' => 'supplier1@supplier1.com',]); 
        Supplier::create(['name' => 'Supplier 2',
                          'address' => 'Address road 2',
                          'contact' => '123456',
                          'email' => 'supplier2@supplier2.com',]);                                
        Supplier::create(['name' => 'Supplier 3',
                          'address' => 'Address road 3',
                          'contact' => '123456',
                          'email' => 'supplier3@supplier3.com',]);  
                        }
}
