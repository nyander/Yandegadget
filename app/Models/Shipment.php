<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\ShipmentStatus;
use App\Models\Concerns\BroadcastsChanges;
use App\Support\LiveUpdates;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Database\Seeders\LocationSeeder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * One consignment from the UK to a store. Change its status through App\Services\ShipmentService.
 *
 * @property int $id
 * @property string $reference
 * @property ShipmentStatus $status
 * @property int $origin_location_id
 * @property int $destination_location_id
 * @property int|null $supplier_id
 * @property int|null $carrier_id
 * @property string|null $tracking_number
 * @property int|null $cost_amount
 * @property string|null $cost_currency
 * @property Money|null $cost
 * @property CarbonImmutable|null $dispatched_on
 * @property CarbonImmutable|null $expected_on
 * @property CarbonImmutable|null $dispatched_at
 * @property CarbonImmutable|null $completed_at
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $dispatched_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Location $origin
 * @property-read Location $destination
 * @property-read Carrier|null $carrier
 */
#[Fillable([
    'origin_location_id', 'destination_location_id', 'supplier_id', 'carrier_id', 'tracking_number',
    'cost', 'dispatched_on', 'expected_on', 'notes', 'created_by',
])]
class Shipment extends Model
{
    use BroadcastsChanges;

    protected $attributes = [
        'status' => 'draft',
    ];

    protected static function booted(): void
    {
        static::creating(function (Shipment $shipment) {
            $shipment->reference ??= 'TMP-'.Str::ulid();
        });

        static::created(function (Shipment $shipment) {
            if (str_starts_with($shipment->reference, 'TMP-')) {
                $shipment->reference = sprintf('SH-%04d', $shipment->id);
                $shipment->saveQuietly();
            }
        });
    }

    public function isDraft(): bool
    {
        return $this->status === ShipmentStatus::Draft;
    }

    /**
     * Draft or in transit: its items are still on the move.
     *
     * @param  Builder<Shipment>  $query
     */
    #[Scope]
    protected function open(Builder $query): void
    {
        $query->whereIn('status', [ShipmentStatus::Draft, ShipmentStatus::InTransit]);
    }

    /**
     * @return HasMany<ShipmentItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    /**
     * The carrier's tracking page for this shipment, if the carrier has one.
     * "{number}" in the carrier's link is replaced with the tracking number.
     */
    public function trackingUrl(): ?string
    {
        $link = $this->carrier?->tracking_url;

        if (blank($link)) {
            return null;
        }

        if (str_contains($link, '{number}')) {
            return filled($this->tracking_number)
                ? str_replace('{number}', rawurlencode(trim((string) $this->tracking_number)), $link)
                : null;
        }

        return $link;
    }

    /**
     * The supplier sending it, when they ship straight to Accra.
     *
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /** Sent by a supplier straight to Accra, not from the UK base. */
    public function isDirectFromSupplier(): bool
    {
        return $this->origin->code === LocationSeeder::DIRECT;
    }

    /**
     * @return BelongsTo<Carrier, $this>
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
            'cost' => MoneyCast::class.':cost_amount,cost_currency',
            'dispatched_on' => 'date',
            'expected_on' => 'date',
            'dispatched_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function liveTopic(): string
    {
        return 'shipments';
    }

    /**
     * @return list<string>
     */
    public function liveChannels(): array
    {
        return [LiveUpdates::BACKOFFICE, LiveUpdates::STORE];
    }
}
