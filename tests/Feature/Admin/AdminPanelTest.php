<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Enums\StockStatus;
use App\Filament\Resources\ExchangeRates\Pages\ManageExchangeRates;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\StockUnits\Pages\ListStockUnits;
use App\Models\Category;
use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\StockUnit;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(Role $role): User
    {
        return User::factory()->create()->assignRole($role->value);
    }

    private function actingAsAdmin(): User
    {
        $admin = $this->userWithRole(Role::Admin);
        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        return $admin;
    }

    public function test_guests_are_sent_to_the_main_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_only_admins_can_open_the_back_office(): void
    {
        $this->actingAs($this->userWithRole(Role::Admin))->get('/admin')->assertOk();

        foreach ([Role::Staff, Role::Customer, Role::Supplier] as $role) {
            $this->actingAs($this->userWithRole($role))->get('/admin')->assertForbidden();
        }
    }

    public function test_unverified_admins_are_kept_out(): void
    {
        $admin = User::factory()->unverified()->create()->assignRole(Role::Admin->value);

        $this->actingAs($admin)->get('/admin')->assertForbidden();
    }

    public function test_admin_resource_pages_load(): void
    {
        $this->actingAsAdmin();
        $unit = StockUnit::factory()->received()->create();

        foreach (['/admin/products', '/admin/products/create', "/admin/products/{$unit->product->slug}/edit", "/admin/stock-units/{$unit->id}/edit", '/admin/stock-units', '/admin/stock-units/create', '/admin/categories', '/admin/conditions', '/admin/suppliers', '/admin/exchange-rates'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_can_create_a_product(): void
    {
        $this->actingAsAdmin();
        $category = Category::where('slug', 'phones')->firstOrFail();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'iPhone 14 Pro',
                'slug' => 'iphone-14-pro',
                'category_id' => $category->id,
                'brand' => 'Apple',
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(Product::where('slug', 'iphone-14-pro')->where('category_id', $category->id)->exists());
    }

    public function test_admin_moves_a_unit_along_its_lifecycle_but_cannot_mark_it_sold(): void
    {
        $this->actingAsAdmin();
        $unit = StockUnit::factory()->status(StockStatus::InTransit)->create();

        Livewire::test(ListStockUnits::class)
            ->callAction(TestAction::make('changeStatus')->table($unit), ['status' => StockStatus::Received->value])
            ->assertHasNoFormErrors();

        $this->assertSame(StockStatus::Received, $unit->fresh()?->status);

        Livewire::test(ListStockUnits::class)
            ->callAction(TestAction::make('changeStatus')->table($unit), ['status' => StockStatus::Sold->value])
            ->assertHasFormErrors(['status']);

        $this->assertSame(StockStatus::Received, $unit->fresh()?->status);
    }

    public function test_setting_a_rate_records_who_set_it_and_rates_cannot_be_edited(): void
    {
        $admin = $this->actingAsAdmin();

        Livewire::test(ManageExchangeRates::class)
            ->callAction('create', ['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.25', 'effective_at' => now()->toDateTimeString()])
            ->assertHasNoFormErrors();

        $rate = ExchangeRate::firstOrFail();
        $this->assertSame($admin->id, $rate->set_by);
        $this->assertSame('15.250000', $rate->rate);
        $this->assertFalse($admin->can('update', $rate));
        $this->assertFalse($admin->can('delete', $rate));
    }

    public function test_sold_units_cannot_be_deleted(): void
    {
        $admin = $this->userWithRole(Role::Admin);

        $this->assertFalse($admin->can('delete', StockUnit::factory()->sold()->create()));
        $this->assertTrue($admin->can('delete', StockUnit::factory()->create()));
    }
}
