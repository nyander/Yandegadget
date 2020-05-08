<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // when we run php artidan bbc, anything in the run meathod with be called
        $this->call(RolesTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(SupplierTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(ConditionsTableSeeder::class);        
        $this->call(ProductTableSeeder::class);
        $this->call(TransactionTypeSeeder::class);
        $this->call(ShipmentCompanySeeder::class); 
        $this->call(TransactionTableSeeder::class); 
        $this->call(ConversionSeeder::class); 
        
        
              
    }
}
