<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_role_is_seeded_with_its_permissions(): void
    {
        foreach (Role::cases() as $role) {
            $model = RoleModel::findByName($role->value, 'web');

            $expected = array_map(fn (Permission $permission) => $permission->value, $role->permissions());

            $this->assertEqualsCanonicalizing($expected, $model->permissions->pluck('name')->all(), $role->value);
        }
    }

    public function test_admin_has_every_permission(): void
    {
        $admin = User::factory()->create()->assignRole(Role::Admin->value);

        foreach (Permission::cases() as $permission) {
            $this->assertTrue($admin->can($permission->value), $permission->value);
        }
    }

    public function test_staff_can_receive_and_sell_but_not_manage_the_business(): void
    {
        $staff = User::factory()->create()->assignRole(Role::Staff->value);

        $this->assertTrue($staff->can(Permission::ReceiveShipments->value));
        $this->assertTrue($staff->can(Permission::RecordSales->value));
        $this->assertFalse($staff->can(Permission::ManageUsers->value));
        $this->assertFalse($staff->can(Permission::ViewReports->value));
        $this->assertFalse($staff->can(Permission::ManageFinances->value));
    }

    public function test_customers_and_suppliers_only_get_their_own_actions(): void
    {
        $customer = User::factory()->create()->assignRole(Role::Customer->value);
        $supplier = User::factory()->create()->assignRole(Role::Supplier->value);

        $this->assertTrue($customer->can(Permission::RequestProducts->value));
        $this->assertFalse($customer->can(Permission::SubmitSupplierOffers->value));
        $this->assertFalse($customer->can(Permission::RecordSales->value));

        $this->assertTrue($supplier->can(Permission::SubmitSupplierOffers->value));
        $this->assertFalse($supplier->can(Permission::RequestProducts->value));
        $this->assertFalse($supplier->can(Permission::ManageCatalogue->value));
    }

    public function test_registration_creates_a_customer(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Ama Mensah',
            'email' => 'ama@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'ama@example.test')->firstOrFail();

        $this->assertSame([Role::Customer->value], $user->getRoleNames()->all());
    }

    public function test_reference_data_seeder_is_safe_to_run_twice(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $this->assertSame(count(Role::cases()), RoleModel::count());
        $this->assertSame(2, Location::count());
    }

    public function test_shared_props_include_roles_and_permissions(): void
    {
        $staff = User::factory()->create()->assignRole(Role::Staff->value);

        $this->actingAs($staff)->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('auth.roles', [Role::Staff->value])
                ->where('auth.permissions', fn ($permissions) => collect($permissions)->sort()->values()->all()
                    === collect([Permission::ReceiveShipments->value, Permission::RecordSales->value])->sort()->values()->all()));
    }
}
