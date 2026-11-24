<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Enums\ShipmentStatus;
use App\Enums\StockStatus;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\RelationManagers\StockUnitsRelationManager;
use App\Filament\Resources\Shipments\Pages\CreateShipment;
use App\Filament\Resources\Shipments\Pages\EditShipment;
use App\Filament\Resources\Shipments\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\StockUnits\Pages\ListStockUnits;
use App\Models\Carrier;
use App\Models\Condition;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\ShipmentService;
use Brick\Money\Money;
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

    public function test_the_owner_adds_a_batch_of_the_same_product_at_once(): void
    {
        $product = Product::factory()->create(['name' => 'iPhone 13 128GB']);
        $grade = Condition::orderBy('sort_order')->firstOrFail();

        Livewire::test(StockUnitsRelationManager::class, ['ownerRecord' => $product, 'pageClass' => EditProduct::class])
            ->callAction(TestAction::make('addSeveral')->table(), [
                'quantity' => 3,
                'condition_id' => $grade->id,
                'cost' => '250',
                'price' => '340',
                'purchased_at' => now()->toDateString(),
                'status' => StockStatus::Purchased->value,
                'location_id' => Location::where('code', 'UK-BUYING')->value('id'),
                'serials' => "IMEI-1\n\n  IMEI-2  ",
            ])
            ->assertHasNoFormErrors()
            ->assertNotified('3 items added');

        $units = $product->stockUnits()->orderBy('id')->get();
        $this->assertCount(3, $units);
        $this->assertSame(['IMEI-1', 'IMEI-2', null], $units->pluck('serial_number')->all());
        $this->assertTrue($units->every(fn (StockUnit $u) => $u->cost->isEqualTo(Money::of(250, 'GBP'))
            && $u->status === StockStatus::Purchased
            && $u->condition_id === $grade->id
            && $u->created_by === $this->admin->id));
        $this->assertCount(3, $units->pluck('reference')->unique());

        // More serials than items is refused.
        Livewire::test(ListStockUnits::class)
            ->callAction('addSeveral', [
                'product_id' => $product->id,
                'quantity' => 1,
                'condition_id' => $grade->id,
                'cost' => '250',
                'price' => '340',
                'purchased_at' => now()->toDateString(),
                'status' => StockStatus::Purchased->value,
                'location_id' => Location::where('code', 'UK-BUYING')->value('id'),
                'serials' => "A\nB",
            ])
            ->assertHasFormErrors(['serials']);
        $this->assertSame(3, $product->stockUnits()->count());
    }

    public function test_shipments_link_to_the_carriers_tracking_page(): void
    {
        $carrier = Carrier::create(['name' => 'DHL Express', 'tracking_url' => 'https://www.dhl.com/track?id={number}']);
        $shipment = Shipment::create([
            'origin_location_id' => Location::where('code', 'UK-BUYING')->value('id'),
            'destination_location_id' => Location::where('code', 'ACC-LEGON')->value('id'),
        ]);
        app(ShipmentService::class)->addUnits($shipment, StockUnit::factory()->count(1)->create());
        app(ShipmentService::class)->dispatch($shipment, [
            'carrier_id' => $carrier->id,
            'tracking_number' => 'JD 0146',
            'cost' => Money::of(80, 'GBP'),
            'dispatched_on' => now()->toDateString(),
        ], $this->admin);

        $this->assertSame('https://www.dhl.com/track?id=JD%200146', $shipment->fresh()?->trackingUrl());

        Livewire::test(EditShipment::class, ['record' => $shipment->getRouteKey()])
            ->assertActionVisible('track')
            ->assertActionHasUrl('track', 'https://www.dhl.com/track?id=JD%200146')
            ->assertActionShouldOpenUrlInNewTab('track');

        // No placeholder: the carrier's page as it is. No link at all: no button.
        $carrier->update(['tracking_url' => 'https://example.test/tracking']);
        $this->assertSame('https://example.test/tracking', $shipment->fresh()?->trackingUrl());
        $carrier->update(['tracking_url' => null]);
        Livewire::test(EditShipment::class, ['record' => $shipment->getRouteKey()])->assertActionHidden('track');
    }
}
