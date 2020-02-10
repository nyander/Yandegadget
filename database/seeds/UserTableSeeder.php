<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the user tables and what links the roles to the users table
        User::truncate();
        DB::table('role_user')->truncate();

        // Find & get the roles
        $adminRole = Role::where('name', 'admin')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $userRole = Role::where('name', 'user')->first();
        $supplierRole = Role::where('name', 'supplier')->first();

        // create users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('richard'),
        ]);

        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@staff.com',
            'password' => Hash::make('richard'),
        ]);

        $user = User::create([
            'name' => 'Customer User',
            'email' => 'customer@customer.com',
            'password' => Hash::make('richard'),
        ]);

        $supplier = User::create([
            'name' => 'Supplier User',
            'email' => 'supplier@supplier.com',
            'password' => Hash::make('richard'),
        ]);


        // this attaches the role to the user we are creating a one to many connection
        $admin->roles()->attach($adminRole);
        $staff->roles()->attach($staffRole);
        $user->roles()->attach($userRole);
        $supplier->roles()->attach($supplierRole);
    }
}
