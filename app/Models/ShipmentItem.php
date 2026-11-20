<?php

namespace App\Models;

use App\Enums\ReceiptOutcome;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A stock unit on a shipment, and what staff found when it arrived.
 *
 * @property int $id
 * @property int $shipment_id
 * @property int $stock_unit_id
 * @property ReceiptOutcome|null $outcome
 * @property string|null $receipt_notes
 * @property CarbonImmutable|null $checked_at
 * @property int|null $checked_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Shipment $shipment
 * @property-read StockUnit $stockUnit
 * @property-read User|null $checker
 */
#[Fillable(['shipment_id', 'stock_unit_id'])]
class ShipmentItem extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function isChecked(): bool
    {
        return $this->checked_at !== null;
    }

    /**
     * @return BelongsTo<Shipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * @return BelongsTo<StockUnit, $this>
     */
    public function stockUnit(): BelongsTo
    {
        return $this->belongsTo(StockUnit::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('arrival_photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/heic']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->nonQueued()->format('webp')->fit(Fit::Crop, 400, 400);
    }

    protected function casts(): array
    {
        return [
            'outcome' => ReceiptOutcome::class,
            'checked_at' => 'datetime',
        ];
    }
}
