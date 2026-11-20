<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Enums\ShipmentStatus;
use App\Enums\StockStatus;
use App\Filament\Resources\Shipments\Pages\CreateShipment;
use App\Filament\Resources\Shipments\Pages\EditShipment;
use App\Filament\Resources\Shipments\RelationManagers\ItemsRelationManager;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\StockUnit;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShipmentAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create()->assignRole(Role::Admin->value);
        $this->actingAs($this->admin);
        Filament::setCurrentPanel('admin');
    }

    public function test_admin_builds_and_dispatches_a_shipment(): void
    {
        $carrier = Carrier::create(['name' => 'DFS Worldwide Logistics']);
        $units = StockUnit::factory()->count(2)->create();
        $alreadyInStore = StockUnit::factory()->received()->create();

        Livewire::test(CreateShipment::class)
            ->fillForm([
                'origin_location_id' => Location::where('code', 'UK-BUYING')->value('id'),
                'destination_location_id' => Location::where('code', 'ACC-LEGON')->value('id'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $shipment = Shipment::firstOrFail();
        $this->assertSame($this->admin->id, $shipment->created_by);

        Livewire::test(ItemsRelationManager::class, ['ownerRecord' => $shipment, 'pageClass' => EditShipment::class])
            ->callAction(TestAction::make('addItems')->table(), ['stock_unit_ids' => $units->modelKeys()])
            ->assertHasNoFormErrors();

        $this->assertSame(2, $shipment->items()->count());

        Livewire::test(ItemsRelationManager::class, ['ownerRecord' => $shipment, 'pageClass' => EditShipment::class])
            ->callAction(TestAction::make('addItems')->table(), ['stock_unit_ids' => [$alreadyInStore->id]]);

        $this->assertSame(2, $shipment->items()->count());

        Livewire::test(EditShipment::class, ['record' => $shipment->getRouteKey()])
            ->callAction('dispatch', [
                'carrier_id' => $carrier->id,
                'cost' => '320.50',
                'dispatched_on' => now()->toDateString(),
                'expected_on' => now()->addWeeks(4)->toDateString(),
                'tracking_number' => 'DFS123',
            ])
            ->assertHasNoActionErrors();

        $shipment->refresh();
        $this->assertSame(ShipmentStatus::InTransit, $shipment->status);
        $this->assertSame(32050, $shipment->cost_amount);
        $this->assertSame('DFS123', $shipment->tracking_number);
        $this->assertSame(StockStatus::InTransit, $units->first()?->fresh()?->status);
        $this->assertFalse($this->admin->can('delete', $shipment));
    }

    public function test_shipment_pages_load(): void
    {
        $shipment = Shipment::create([
            'origin_location_id' => Location::where('code', 'UK-BUYING')->value('id'),
            'destination_location_id' => Location::where('code', 'ACC-LEGON')->value('id'),
        ]);

        foreach (['/admin/shipments', '/admin/shipments/create', "/admin/shipments/{$shipment->id}/edit", '/admin/carriers'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
