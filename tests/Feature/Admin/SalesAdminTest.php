<?php

namespace Tests\Feature\Admin;

use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Filament\Pages\ShopSettings as ShopSettingsPage;
use App\Filament\Resources\Sales\Pages\ListSales;
use App\Models\ExchangeRate;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\SaleService;
use App\Services\ShopSettings;
use Brick\Money\Money;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $this->admin = User::factory()->create()->assignRole(Role::Admin->value);
        $this->actingAs($this->admin);
        Filament::setCurrentPanel('admin');
    }

    public function test_admin_sees_sales_and_can_void_one(): void
    {
        $unit = StockUnit::factory()->received()->priced(100)->create();
        $sale = app(SaleService::class)->record($unit, Money::of(1400, 'GHS'), PaymentMethod::Cash, $this->admin);

        $this->get('/admin/sales')->assertOk()->assertSee($sale->reference);

        Livewire::test(ListSales::class)
            ->callAction(TestAction::make('void')->table($sale), ['reason' => 'Customer changed their mind'])
            ->assertHasNoFormErrors();

        $this->assertTrue($sale->fresh()?->isVoided());
        $this->assertSame(StockStatus::Received, $unit->fresh()?->status);
    }

    public function test_admin_sets_the_negotiation_floor(): void
    {
        $this->get('/admin/shop-settings')->assertOk();

        Livewire::test(ShopSettingsPage::class)
            ->assertSchemaStateSet([ShopSettings::NEGOTIATION_FLOOR => 70], 'form')
            ->fillForm([ShopSettings::NEGOTIATION_FLOOR => 80])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(80, app(ShopSettings::class)->negotiationFloorPercent());

        Livewire::test(ShopSettingsPage::class)
            ->fillForm([ShopSettings::NEGOTIATION_FLOOR => 150])
            ->call('save')
            ->assertHasFormErrors([ShopSettings::NEGOTIATION_FLOOR]);
    }
}
