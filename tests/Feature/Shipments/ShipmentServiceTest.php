<?php

namespace Tests\Feature\Shipments;

use App\Enums\ReceiptOutcome;
use App\Enums\Role;
use App\Enums\ShipmentStatus;
use App\Enums\StockStatus;
use App\Exceptions\ShipmentRuleViolation;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\StockUnit;
use App\Models\User;
use App\Notifications\ShipmentDispatched;
use App\Notifications\ShipmentReceived;
use App\Services\ShipmentService;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShipmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShipmentService $service;

    private User $admin;

    private Location $legon;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ShipmentService::class);
        $this->admin = User::factory()->create()->assignRole(Role::Admin->value);
        $this->legon = Location::where('code', 'ACC-LEGON')->firstOrFail();
    }

    private function draft(): Shipment
    {
        return Shipment::create([
            'origin_location_id' => Location::where('code', 'UK-BUYING')->value('id'),
            'destination_location_id' => $this->legon->id,
            'created_by' => $this->admin->id,
        ]);
    }

    private function ukUnit(): StockUnit
    {
        return StockUnit::factory()->create([
            'location_id' => Location::where('code', 'UK-BUYING')->value('id'),
        ]);
    }

    /**
     * @param  list<StockUnit>  $units
     */
    private function dispatched(array $units): Shipment
    {
        $shipment = $this->draft();
        $this->service->addUnits($shipment, $units);
        $this->service->dispatch($shipment, [
            'carrier_id' => Carrier::create(['name' => 'Test Freight'])->id,
            'cost' => Money::of(250, 'GBP'),
            'dispatched_on' => now()->toDateString(),
        ], $this->admin);

        return $shipment->refresh();
    }

    public function test_shipments_get_readable_references(): void
    {
        $shipment = $this->draft();

        $this->assertSame(sprintf('SH-%04d', $shipment->id), $shipment->fresh()?->reference);
        $this->assertSame(ShipmentStatus::Draft, $shipment->fresh()?->status);
    }

    public function test_only_purchased_units_not_on_another_open_shipment_can_be_added(): void
    {
        $unit = $this->ukUnit();
        $first = $this->draft();
        $this->service->addUnits($first, [$unit]);

        $this->expectException(ShipmentRuleViolation::class);
        $this->service->addUnits($this->draft(), [$unit]);
    }

    public function test_units_already_in_store_cannot_be_shipped(): void
    {
        $this->expectException(ShipmentRuleViolation::class);

        $this->service->addUnits($this->draft(), [StockUnit::factory()->received()->create()]);
    }

    public function test_dispatch_needs_items(): void
    {
        $this->expectException(ShipmentRuleViolation::class);

        $this->service->dispatch($this->draft(), [
            'carrier_id' => Carrier::create(['name' => 'Test Freight'])->id,
            'cost' => Money::of(100, 'GBP'),
            'dispatched_on' => now()->toDateString(),
        ], $this->admin);
    }

    public function test_dispatch_puts_units_in_transit_and_tells_staff_at_the_destination(): void
    {
        Notification::fake();
        $legonStaff = User::factory()->create(['location_id' => $this->legon->id])->assignRole(Role::Staff->value);
        $roamingStaff = User::factory()->create(['location_id' => null])->assignRole(Role::Staff->value);
        $otherStore = Location::factory()->create();
        $otherStaff = User::factory()->create(['location_id' => $otherStore->id])->assignRole(Role::Staff->value);
        $customer = User::factory()->create()->assignRole(Role::Customer->value);

        $units = [$this->ukUnit(), $this->ukUnit()];
        $shipment = $this->dispatched($units);

        $this->assertSame(ShipmentStatus::InTransit, $shipment->status);
        $this->assertSame($this->admin->id, $shipment->dispatched_by);
        $this->assertTrue($shipment->cost?->isEqualTo(Money::of(250, 'GBP')));
        foreach ($units as $unit) {
            $this->assertSame(StockStatus::InTransit, $unit->fresh()?->status);
        }

        Notification::assertSentTo([$legonStaff, $roamingStaff], ShipmentDispatched::class);
        Notification::assertNotSentTo([$otherStaff, $customer, $this->admin], ShipmentDispatched::class);
    }

    public function test_dispatched_shipments_cannot_change(): void
    {
        $shipment = $this->dispatched([$this->ukUnit()]);

        $this->expectException(ShipmentRuleViolation::class);
        $this->service->addUnits($shipment, [$this->ukUnit()]);
    }

    public function test_checking_in_moves_units_into_the_store_and_completes_the_shipment(): void
    {
        Storage::fake('public');
        $staff = User::factory()->create(['location_id' => $this->legon->id])->assignRole(Role::Staff->value);
        [$ok, $damaged, $missing] = [$this->ukUnit(), $this->ukUnit(), $this->ukUnit()];
        $shipment = $this->dispatched([$ok, $damaged, $missing]);
        Notification::fake();
        $items = $shipment->items->keyBy('stock_unit_id');

        $this->service->checkIn($items[$ok->id], ReceiptOutcome::Received, $staff, photos: [UploadedFile::fake()->image('box.jpg')]);
        $this->service->checkIn($items[$damaged->id], ReceiptOutcome::Damaged, $staff, 'Screen cracked');
        Notification::assertNothingSent();

        $this->service->checkIn($items[$missing->id], ReceiptOutcome::Missing, $staff, 'Not in the box');

        $this->assertSame(StockStatus::Received, $ok->fresh()?->status);
        $this->assertSame($this->legon->id, $ok->fresh()?->location_id);
        $this->assertSame(StockStatus::Received, $damaged->fresh()?->status);
        $this->assertSame(StockStatus::InTransit, $missing->fresh()?->status);

        $item = $items[$ok->id]->fresh();
        $this->assertNotNull($item);
        $this->assertSame($staff->id, $item->checked_by);
        $this->assertCount(1, $item->getMedia('arrival_photos'));

        $this->assertSame(ShipmentStatus::Received, $shipment->fresh()?->status);
        $this->assertNotNull($shipment->fresh()?->completed_at);
        Notification::assertSentTo($this->admin, ShipmentReceived::class);
        Notification::assertNotSentTo($staff, ShipmentReceived::class);
    }

    public function test_problems_need_a_note_and_items_cannot_be_checked_twice(): void
    {
        $staff = User::factory()->create()->assignRole(Role::Staff->value);
        $shipment = $this->dispatched([$this->ukUnit(), $this->ukUnit()]);
        [$first, $second] = $shipment->items->all();

        try {
            $this->service->checkIn($first, ReceiptOutcome::Damaged, $staff);
            $this->fail('A damaged item without a note should be rejected.');
        } catch (ShipmentRuleViolation) {
            $this->assertNull($first->fresh()?->checked_at);
        }

        $this->service->checkIn($second, ReceiptOutcome::Received, $staff);

        $this->expectException(ShipmentRuleViolation::class);
        $this->service->checkIn($second->fresh() ?? $second, ReceiptOutcome::Received, $staff);
    }

    public function test_draft_shipments_cannot_be_checked_in(): void
    {
        $shipment = $this->draft();
        $this->service->addUnits($shipment, [$this->ukUnit()]);

        $this->expectException(ShipmentRuleViolation::class);
        $this->service->checkIn($shipment->items()->firstOrFail(), ReceiptOutcome::Received, $this->admin);
    }

    public function test_units_from_a_completed_shipment_are_visible_in_the_shop(): void
    {
        $staff = User::factory()->create()->assignRole(Role::Staff->value);
        $unit = $this->ukUnit();
        $shipment = $this->dispatched([$unit]);

        $this->assertFalse($unit->product->visibleUnits()->exists());

        $this->service->checkIn($shipment->items()->firstOrFail(), ReceiptOutcome::Received, $staff);

        $this->assertTrue($unit->product->visibleUnits()->exists());
    }
}
