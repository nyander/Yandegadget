<?php

namespace Tests\Feature\Admin;

use App\Enums\ReceiptOutcome;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Filament\Resources\Shipments\Pages\EditShipment;
use App\Filament\Resources\Shipments\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\StockBatches\Pages\EditStockBatch;
use App\Filament\Resources\StockBatches\Pages\ListStockBatches;
use App\Filament\Resources\StockUnits\Pages\EditStockUnit;
use App\Filament\Resources\StockUnits\Pages\ListStockUnits;
use App\Models\Carrier;
use App\Models\Condition;
use App\Models\ExchangeRate;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\StockBatch;
use App\Models\StockUnit;
use App\Models\User;
use App\Services\ShipmentService;
use App\Services\StockIntake;
use Brick\Money\Money;
use Database\Seeders\LocationSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class BulkStockTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        ExchangeRate::create(['base_currency' => 'GBP', 'quote_currency' => 'GHS', 'rate' => '15.000000', 'effective_at' => now()->subDay()]);
        $this->admin = User::factory()->create()->assignRole(Role::Admin->value);
        $this->actingAs($this->admin);
        Filament::setCurrentPanel('admin');
        $this->product = Product::factory()->create(['name' => 'Apple Watch Series 8 45mm']);
    }

    /** @return list<StockUnit> */
    private function buyBulk(int $quantity, string $status = 'purchased', ?string $location = 'UK-BUYING'): array
    {
        return app(StockIntake::class)->addSeveral($this->product, [
            'condition_id' => Condition::orderBy('sort_order')->value('id'),
            'cost' => Money::of(40, 'GBP'),
            'price' => Money::of(65, 'GBP'),
            'purchased_at' => now()->toDateString(),
            'status' => $status,
            'location_id' => Location::where('code', $location)->value('id'),
        ], $quantity, [], $this->admin);
    }

    public function test_add_several_records_a_bulk_batch_and_shows_cedis_while_typing(): void
    {
        Livewire::test(ListStockBatches::class)
            ->mountAction('addSeveral')
            ->fillForm(['product_id' => $this->product->id, 'quantity' => 5, 'price' => '65'])
            ->assertMountedActionModalSee('GH₵975.00')
            ->fillForm([
                'condition_id' => Condition::orderBy('sort_order')->value('id'),
                'cost' => '40',
                'purchased_at' => now()->toDateString(),
                'status' => 'direct',
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $batch = StockBatch::sole();
        $this->assertSame(5, $batch->quantity);
        $this->assertSame(sprintf('BT-%04d', $batch->id), $batch->reference);
        $this->assertSame(5, $batch->units()->count());
        // Bought for direct delivery: still to ship, from the supplier straight to Accra.
        $this->assertTrue($batch->units->every(fn (StockUnit $u) => $u->status === StockStatus::Purchased
            && $u->location->code === LocationSeeder::DIRECT));
    }

    public function test_editing_a_batch_updates_its_unsold_items_only(): void
    {
        $units = $this->buyBulk(3, 'received', 'ACC-LEGON');
        $units[0]->forceFill(['status' => StockStatus::Sold])->save();
        $batch = StockBatch::sole();

        Livewire::test(EditStockBatch::class, ['record' => $batch->getRouteKey()])
            ->assertSeeText('Saving updates all 2 of the 3 items not yet sold')
            ->fillForm(['price' => '59'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('2 items updated');

        $this->assertTrue(Money::of(65, 'GBP')->isEqualTo($units[0]->fresh()?->price));
        $this->assertTrue(Money::of(59, 'GBP')->isEqualTo($units[1]->fresh()?->price));
        $this->assertTrue(Money::of(59, 'GBP')->isEqualTo($batch->fresh()?->price));
    }

    public function test_edit_selected_changes_several_items_and_leaves_empty_boxes_alone(): void
    {
        $one = StockUnit::factory()->received()->create(['price' => Money::of(100, 'GBP')]);
        $two = StockUnit::factory()->received()->create(['price' => Money::of(120, 'GBP'), 'condition_notes' => 'Box dented']);

        Livewire::test(ListStockUnits::class)
            ->selectTableRecords([$one->getKey(), $two->getKey()])
            ->callAction(TestAction::make('editSelected')->table()->bulk(), ['price' => '90'])
            ->assertNotified('2 items updated');

        $this->assertTrue(Money::of(90, 'GBP')->isEqualTo($two->fresh()?->price));
        $this->assertSame('Box dented', $two->fresh()?->condition_notes);
    }

    public function test_a_whole_batch_goes_on_a_shipment_and_is_checked_in_with_one_tap(): void
    {
        $this->buyBulk(50);
        $batch = StockBatch::sole();
        $single = StockUnit::factory()->create();
        $shipment = Shipment::create([
            'origin_location_id' => Location::where('code', 'UK-BUYING')->value('id'),
            'destination_location_id' => Location::where('code', 'ACC-LEGON')->value('id'),
        ]);

        Livewire::test(ItemsRelationManager::class, ['ownerRecord' => $shipment, 'pageClass' => EditShipment::class])
            ->callAction(TestAction::make('addItems')->table(), ['batch_ids' => [$batch->id], 'stock_unit_ids' => [$single->id]])
            ->assertNotified('51 items added');

        $this->assertSame(51, $shipment->items()->count());

        // The Dispatch button is also at the bottom of the page.
        Livewire::test(EditShipment::class, ['record' => $shipment->getRouteKey()])
            ->callAction(TestAction::make('dispatchBottom')->schemaComponent('bottomActions', schema: 'content'), [
                'carrier_id' => Carrier::create(['name' => 'DHL Express'])->id,
                'cost' => '300',
                'dispatched_on' => now()->toDateString(),
            ])
            ->assertHasNoActionErrors();
        $this->assertTrue($shipment->fresh()?->status->value === 'in_transit');

        $staff = User::factory()->create()->assignRole(Role::Staff->value);
        $damaged = $shipment->items()->whereHas('stockUnit', fn ($q) => $q->where('batch_id', $batch->id))->firstOrFail();
        app(ShipmentService::class)->checkIn($damaged, ReceiptOutcome::Damaged, $staff, 'Cracked strap');

        $this->actingAs($staff)
            ->get(route('staff.shipments.show', $shipment))
            ->assertInertia(fn (Assert $page) => $page->where('items.0.batch.reference', $batch->reference));

        $this->actingAs($staff)
            ->post(route('staff.shipments.batches.check-in', ['shipment' => $shipment, 'batch' => $batch]))
            ->assertRedirect();

        $this->assertSame(50, $batch->units()->where('status', StockStatus::Received)->count());
        $this->assertSame(ReceiptOutcome::Damaged, $damaged->fresh()?->outcome);
        $this->assertSame(1, $shipment->items()->whereNull('checked_at')->count());
    }

    public function test_the_shop_shows_a_batch_as_one_line_and_reserving_picks_a_free_item(): void
    {
        $units = $this->buyBulk(3, 'received', 'ACC-LEGON');
        $units[0]->forceFill(['status' => StockStatus::Reserved])->save();

        $this->get(route('shop.show', $this->product))->assertInertia(fn (Assert $page) => $page
            ->has('product.units', 1)
            ->where('product.units.0.bulk', true)
            ->where('product.units.0.available', 2)
            ->where('product.units.0.reference', $units[1]->reference));

        // A link to the reserved one still finds a free one from the same batch.
        $customer = User::factory()->create()->assignRole(Role::Customer->value);
        $this->actingAs($customer)
            ->get(route('reservations.create', ['product' => $this->product->slug, 'reference' => $units[0]->reference]))
            ->assertInertia(fn (Assert $page) => $page->where('item.reference', $units[1]->reference));
    }

    public function test_editing_an_item_shows_its_saved_cost_and_price(): void
    {
        $unit = StockUnit::factory()->received()->create(['cost' => Money::of(40, 'GBP'), 'price' => Money::of(65, 'GBP')]);

        Livewire::test(EditStockUnit::class, ['record' => $unit->getRouteKey()])
            ->assertSchemaStateSet(['cost' => '40', 'price' => '65'])
            ->call('save')
            ->assertHasNoFormErrors();
    }
}
