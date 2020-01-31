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
    }
}
