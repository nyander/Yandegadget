<?php

namespace App\Services;

use App\Enums\Permission;
use App\Enums\ReceiptOutcome;
use App\Enums\ShipmentStatus;
use App\Enums\StockStatus;
use App\Exceptions\ShipmentRuleViolation;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\StockUnit;
use App\Models\User;
use App\Notifications\ShipmentDispatched;
use App\Notifications\ShipmentReceived;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Every change to a shipment goes through here, so the rules live in one place:
 * build in the UK → dispatch → check in item by item in Ghana.
 */
class ShipmentService
{
    /**
     * @param  iterable<StockUnit>|iterable<int>  $units
     * @return int How many units were added.
     */
    public function addUnits(Shipment $shipment, iterable $units): int
    {
        $this->ensureDraft($shipment);

        $ids = collect($units)->map(fn ($unit) => $unit instanceof StockUnit ? $unit->id : (int) $unit)->unique();

        return DB::transaction(function () use ($shipment, $ids) {
            $ready = StockUnit::readyToShip()->whereKey($ids)->lockForUpdate()->get();

            if ($ready->count() !== $ids->count()) {
                throw new ShipmentRuleViolation('Only items bought in the UK that aren\'t on another shipment can be added.');
            }

            foreach ($ready as $unit) {
                $shipment->items()->create(['stock_unit_id' => $unit->id]);
            }

            return $ready->count();
        });
    }

    public function removeItem(ShipmentItem $item): void
    {
        $this->ensureDraft($item->shipment);

        $item->delete();
    }

    /**
     * Hands the shipment to the carrier: every unit goes in transit and staff at
     * the destination are told it's on the way.
     *
     * @param  array<string, mixed>  $details  carrier_id, cost (Money), dispatched_on, and optionally expected_on and tracking_number.
     */
    public function dispatch(Shipment $shipment, array $details, User $by): void
    {
        $this->ensureDraft($shipment);

        if (blank($details['carrier_id'] ?? null) || ! ($details['cost'] ?? null) instanceof Money || blank($details['dispatched_on'] ?? null)) {
            throw new ShipmentRuleViolation('A carrier, shipping cost and dispatch date are needed to dispatch.');
        }

        DB::transaction(function () use ($shipment, $details, $by) {
            $items = $shipment->items()->with('stockUnit')->lockForUpdate()->get();

            if ($items->isEmpty()) {
                throw new ShipmentRuleViolation('Add at least one item before dispatching.');
            }

            foreach ($items as $item) {
                $item->stockUnit->transitionTo(StockStatus::InTransit);
            }

            $shipment->fill([
                'carrier_id' => $details['carrier_id'],
                'cost' => $details['cost'],
                'dispatched_on' => $details['dispatched_on'],
                'expected_on' => $details['expected_on'] ?? null,
                'tracking_number' => $details['tracking_number'] ?? null,
            ]);
            $shipment->status = ShipmentStatus::InTransit;
            $shipment->dispatched_at = now();
            $shipment->dispatched_by = $by->id;
            $shipment->save();
        });

        Notification::send($this->receivingStaff($shipment, except: $by), new ShipmentDispatched($shipment));
    }

    /**
     * Records what staff found for one item. Items that arrived go into the
     * store at the destination; missing ones stay in transit for the admin to
     * chase or write off. The last check completes the shipment.
     *
     * @param  list<UploadedFile>  $photos
     */
    public function checkIn(ShipmentItem $item, ReceiptOutcome $outcome, User $by, ?string $notes = null, array $photos = []): void
    {
        $shipment = $item->shipment;

        if ($shipment->status !== ShipmentStatus::InTransit) {
            throw new ShipmentRuleViolation('Items can only be checked in while the shipment is in transit.');
        }

        if ($outcome->isIssue() && blank($notes)) {
            throw new ShipmentRuleViolation('Add a note describing the problem.');
        }

        $completed = DB::transaction(function () use ($item, $outcome, $by, $notes, $shipment) {
            $item = ShipmentItem::whereKey($item->id)->lockForUpdate()->firstOrFail();

            if ($item->isChecked()) {
                throw new ShipmentRuleViolation('This item has already been checked in.');
            }

            if ($outcome->arrived()) {
                $unit = $item->stockUnit;
                $unit->location_id = $shipment->destination_location_id;
                $unit->transitionTo(StockStatus::Received);
            }

            $item->outcome = $outcome;
            $item->receipt_notes = $notes;
            $item->checked_at = now();
            $item->checked_by = $by->id;
            $item->save();

            if ($shipment->items()->whereNull('checked_at')->doesntExist()) {
                $shipment->status = ShipmentStatus::Received;
                $shipment->completed_at = now();
                $shipment->save();

                return true;
            }

            return false;
        });

        foreach ($photos as $photo) {
            $item->addMedia($photo)->toMediaCollection('arrival_photos');
        }

        if ($completed) {
            Notification::send($this->shipmentManagers(), new ShipmentReceived($shipment->refresh()));
        }
    }

    private function ensureDraft(Shipment $shipment): void
    {
        if (! $shipment->isDraft()) {
            throw new ShipmentRuleViolation("{$shipment->reference} has been dispatched and can't be changed.");
        }
    }

    /**
     * Staff who can receive stock at the shipment's destination. Staff without
     * a location receive for every store.
     *
     * @return Collection<int, User>
     */
    public function receivingStaff(Shipment $shipment, ?User $except = null): Collection
    {
        return User::permission(Permission::ReceiveShipments->value)
            ->where(fn ($q) => $q->whereNull('location_id')->orWhere('location_id', $shipment->destination_location_id))
            ->when($except, fn ($q, User $user) => $q->whereKeyNot($user->id))
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    private function shipmentManagers(): Collection
    {
        return User::permission(Permission::ManageShipments->value)->get();
    }
}
