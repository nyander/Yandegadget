<?php

namespace Tests\Feature\Shipments;

use App\Enums\ReceiptOutcome;
use App\Enums\Role;
use App\Enums\StockStatus;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\StockUnit;
use App\Models\User;
use App\Notifications\ShipmentDispatched;
use App\Services\ShipmentService;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StaffCheckInTest extends TestCase
{
    use RefreshDatabase;

    private Shipment $shipment;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $legon = Location::where('code', 'ACC-LEGON')->firstOrFail();
        $admin = User::factory()->create()->assignRole(Role::Admin->value);
        $this->staff = User::factory()->create(['location_id' => $legon->id])->assignRole(Role::Staff->value);

        $service = app(ShipmentService::class);
        $this->shipment = Shipment::create([
            'origin_location_id' => Location::where('code', 'UK-BUYING')->value('id'),
            'destination_location_id' => $legon->id,
        ]);
        $service->addUnits($this->shipment, StockUnit::factory()->count(2)->create());
        $service->dispatch($this->shipment, [
            'carrier_id' => Carrier::create(['name' => 'DHL Express'])->id,
            'cost' => Money::of(180, 'GBP'),
            'dispatched_on' => now()->toDateString(),
            'expected_on' => now()->addWeeks(3)->toDateString(),
        ], $admin);
    }

    public function test_staff_see_shipments_on_the_way(): void
    {
        $this->actingAs($this->staff)
            ->get(route('staff.shipments.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('staff/shipments/index')
                ->has('inTransit', 1)
                ->where('inTransit.0.reference', $this->shipment->reference)
                ->where('inTransit.0.items_count', 2)
                ->where('inTransit.0.checked_count', 0));
    }

    public function test_opening_a_shipment_lists_items_and_marks_its_notification_read(): void
    {
        $this->assertSame(1, $this->staff->unreadNotifications()->count());
        $this->assertSame(ShipmentDispatched::class, $this->staff->notifications()->value('type'));

        $this->actingAs($this->staff)
            ->get(route('staff.shipments.show', $this->shipment))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('staff/shipments/show')
                ->has('items', 2)
                ->where('items.0.checked', false)
                ->missing('items.0.cost'));

        $this->assertSame(0, $this->staff->unreadNotifications()->count());
    }

    public function test_staff_check_items_in_with_photos(): void
    {
        Storage::fake('public');
        $item = $this->shipment->items()->firstOrFail();

        $this->actingAs($this->staff)
            ->from(route('staff.shipments.show', $this->shipment))
            ->post(route('staff.shipments.items.check-in', [$this->shipment, $item]), [
                'outcome' => ReceiptOutcome::Received->value,
                'photos' => [UploadedFile::fake()->image('front.jpg'), UploadedFile::fake()->image('back.jpg')],
            ])
            ->assertRedirect(route('staff.shipments.show', $this->shipment))
            ->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertSame(ReceiptOutcome::Received, $item->outcome);
        $this->assertSame(StockStatus::Received, $item->stockUnit->status);
        $this->assertCount(2, $item->getMedia('arrival_photos'));
    }

    public function test_damage_needs_a_note(): void
    {
        $item = $this->shipment->items()->firstOrFail();

        $this->actingAs($this->staff)
            ->post(route('staff.shipments.items.check-in', [$this->shipment, $item]), ['outcome' => 'damaged'])
            ->assertSessionHasErrors('notes');

        $this->assertNull($item->fresh()?->checked_at);
    }

    public function test_non_images_are_rejected(): void
    {
        $item = $this->shipment->items()->firstOrFail();

        $this->actingAs($this->staff)
            ->post(route('staff.shipments.items.check-in', [$this->shipment, $item]), [
                'outcome' => 'received',
                'photos' => [UploadedFile::fake()->create('virus.exe', 10)],
            ])
            ->assertSessionHasErrors('photos.0');
    }

    public function test_items_must_belong_to_the_shipment_in_the_url(): void
    {
        $other = Shipment::create([
            'origin_location_id' => $this->shipment->origin_location_id,
            'destination_location_id' => $this->shipment->destination_location_id,
        ]);

        $this->actingAs($this->staff)
            ->post(route('staff.shipments.items.check-in', [$other, $this->shipment->items()->firstOrFail()]), ['outcome' => 'received'])
            ->assertNotFound();
    }

    public function test_customers_and_staff_from_other_stores_are_kept_out(): void
    {
        $customer = User::factory()->create()->assignRole(Role::Customer->value);
        $this->actingAs($customer)->get(route('staff.shipments.index'))->assertForbidden();

        $elsewhere = User::factory()->create(['location_id' => Location::factory()->create()->id])->assignRole(Role::Staff->value);
        $this->actingAs($elsewhere)->get(route('staff.shipments.show', $this->shipment))->assertForbidden();
        $this->actingAs($elsewhere)
            ->post(route('staff.shipments.items.check-in', [$this->shipment, $this->shipment->items()->firstOrFail()]), ['outcome' => 'received'])
            ->assertForbidden();
    }

    public function test_draft_shipments_are_not_shown_to_staff(): void
    {
        $draft = Shipment::create([
            'origin_location_id' => $this->shipment->origin_location_id,
            'destination_location_id' => $this->shipment->destination_location_id,
        ]);

        $this->actingAs($this->staff)->get(route('staff.shipments.show', $draft))->assertNotFound();
    }
}
