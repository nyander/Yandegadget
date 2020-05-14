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
        $customerRole = Role::where('name', 'customer')->first();
        $supplierRole = Role::where('name', 'supplier')->first();

        // create users
        $admin = User::create([
            'name' => 'Dave Yande',
            'email' => 'admin@admin.com',
            'email_verified_at' => '2020-05-11 13:46:35',
            'password' => Hash::make('GadgetsAccra123_'),
        ]);

        $staff = User::create([
            'name' => 'Morris Chestnut',
            'email' => 'staff@staff.com',
            'email_verified_at' => '2020-05-11 13:46:35',
            'password' => Hash::make('ChestMorStaff123_'),
        ]);

        $customer = User::create([
            'name' => 'Jamal Jones',
            'email' => 'customer@customer.com',
            'email_verified_at' => '2020-05-11 13:46:35',
            'password' => Hash::make('JamJoneSCustom_2'),
        ]);

        $supplier = User::create([
            'name' => 'James Smith',
            'email' => 'supplier@supplier.com',
            'email_verified_at' => '2020-05-11 13:46:35',
            'password' => Hash::make('SupJAMSmith_'),
        ]);


        // this attaches the role to the user we are creating a one to many connection
        $admin->roles()->attach($adminRole);
        $staff->roles()->attach($staffRole);
        $customer->roles()->attach($customerRole);
        $supplier->roles()->attach($supplierRole);
    }
}
