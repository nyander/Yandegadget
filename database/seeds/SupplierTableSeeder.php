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
        Supplier::create(['name' => 'Harry Potter',
                          'address' => 'A4 Privet Drive',
                          'contact' => '01632 960060',]); 
        Supplier::create(['name' => 'James Hazleworth',
                          'address' => '5 Kendall Street',
                          'contact' => '07588069507',]);                                
        Supplier::create(['name' => 'Lebron James',
                          'address' => '10 LA Lakers road',
                          'contact' => '01632 960769',]);  
                        }
}
