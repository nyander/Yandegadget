<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\StockStatus;
use App\Exceptions\InvalidStockTransition;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Database\Factories\StockUnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * One physical item, with its own condition, cost and price.
 *
 * @property int $id
 * @property string $reference
 * @property int $product_id
 * @property int $condition_id
 * @property int|null $supplier_id
 * @property int $location_id
 * @property StockStatus $status
 * @property int $cost_amount
 * @property string $cost_currency
 * @property Money $cost
 * @property int $price_amount
 * @property string $price_currency
 * @property Money $price
 * @property string|null $condition_notes
 * @property string|null $serial_number
 * @property CarbonImmutable $purchased_at
 * @property CarbonImmutable|null $shipped_at
 * @property CarbonImmutable|null $received_at
 * @property CarbonImmutable|null $sold_at
 * @property int|null $created_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Product $product
 * @property-read Condition $condition
 * @property-read Supplier|null $supplier
 * @property-read Location $location
 */
#[Fillable([
    'product_id', 'condition_id', 'supplier_id', 'location_id', 'status',
    'cost', 'price', 'condition_notes', 'serial_number', 'purchased_at', 'created_by',
])]
class StockUnit extends Model implements HasMedia
{
    /** @use HasFactory<StockUnitFactory> */
    use HasFactory, InteractsWithMedia;

    protected $attributes = [
        'status' => 'purchased',
    ];

    protected static function booted(): void
    {
        static::creating(function (StockUnit $unit) {
            // Placeholder until the ID is known; replaced in `created`.
            $unit->reference ??= 'TMP-'.Str::ulid();

            // Stock entered straight into a later status, e.g. items already in the store.
            if (in_array($unit->status, [StockStatus::InTransit, StockStatus::Received], true)) {
                $unit->shipped_at ??= now();
            }
            if ($unit->status === StockStatus::Received) {
                $unit->received_at ??= now();
            }
        });

        static::created(function (StockUnit $unit) {
            if (str_starts_with($unit->reference, 'TMP-')) {
                $unit->reference = sprintf('YG-%06d', $unit->id);
                $unit->saveQuietly();
            }
        });
    }

    /**
     * Moves the unit to the next step of its lifecycle and records when it happened.
     *
     * @throws InvalidStockTransition
     */
    public function transitionTo(StockStatus $next): void
    {
        if (! $this->status->canTransitionTo($next)) {
            throw InvalidStockTransition::between($this->status, $next);
        }

        $this->status = $next;

        match ($next) {
            StockStatus::InTransit => $this->shipped_at = now(),
            StockStatus::Received => $this->received_at ??= now(),
            StockStatus::Sold => $this->sold_at = now(),
            default => null,
        };

        $this->save();
    }

    /**
     * @param  Builder<StockUnit>  $query
     */
    #[Scope]
    protected function visibleToCustomers(Builder $query): void
    {
        $query->whereIn('status', StockStatus::customerVisibleValues());
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * @return BelongsTo<Condition, $this>
     */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->nonQueued()->format('webp')->fit(Fit::Crop, 600, 600);
        $this->addMediaConversion('large')->nonQueued()->format('webp')->fit(Fit::Max, 1600, 1600);
    }

    protected function casts(): array
    {
        return [
            'status' => StockStatus::class,
            'cost' => MoneyCast::class.':cost_amount,cost_currency',
            'price' => MoneyCast::class.':price_amount,price_currency',
            'purchased_at' => 'date',
            'shipped_at' => 'datetime',
            'received_at' => 'datetime',
            'sold_at' => 'datetime',
        ];
    }
}
