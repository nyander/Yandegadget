<?php

namespace Tests\Feature\Admin;

use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Filament\Resources\ProductRequests\Pages\ListProductRequests;
use App\Models\Category;
use App\Models\Condition;
use App\Models\ExchangeRate;
use App\Models\ProductRequest;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\ProductRequestService;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RequestAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_matches_an_open_request_to_a_unit(): void
    {
        config(['services.payments.driver' => 'fake']);
        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $admin = User::factory()->create()->assignRole(Role::Admin->value);
        $customer = User::factory()->create()->assignRole(Role::Customer->value);
        $service = app(ProductRequestService::class);
        $request = $service->create($customer, [
            'category_id' => Category::where('slug', 'phones')->value('id'),
            'condition_id' => Condition::where('grade', 'B')->value('id'),
            'description' => 'iPhone 13',
        ]);
        $service->depositPaid($request);
        $unit = StockUnit::factory()->received()->create();

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        $this->get('/admin/product-requests')->assertOk()->assertSee($request->reference);
        $this->get('/admin/payments')->assertOk();

        Livewire::test(ListProductRequests::class)
            ->callAction(TestAction::make('match')->table($request), ['stock_unit_id' => $unit->id])
            ->assertHasNoFormErrors();

        $this->assertSame(RequestStatus::ReadyForCollection, $request->fresh()?->status);
        $this->assertSame(StockStatus::Reserved, $unit->fresh()?->status);
        $this->assertFalse($admin->can('update', ProductRequest::first()));
    }
}
