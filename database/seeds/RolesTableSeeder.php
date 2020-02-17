<?php

use Illuminate\Database\Seeder;
use App\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::truncate();
        Role::create(['name'=> 'admin']);
        Role::create(['name'=> 'staff']);
        Role::create(['name'=> 'customer']);
        Role::create(['name'=> 'supplier']);
    }
}
