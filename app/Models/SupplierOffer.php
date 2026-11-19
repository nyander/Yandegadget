<?php

namespace App\Models;

use App\Enums\OfferStatus;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * An item a supplier offers to the shop. Written through App\Services\SupplierOfferService.
 *
 * @property int $id
 * @property string $reference
 * @property OfferStatus $status
 * @property int $supplier_id
 * @property int $submitted_by
 * @property int $category_id
 * @property int $condition_id
 * @property string $title
 * @property string|null $brand
 * @property string|null $model
 * @property string|null $serial_number
 * @property string|null $description
 * @property int $asking_price_amount
 * @property string $asking_price_currency
 * @property int|null $agreed_price_amount
 * @property int|null $stock_unit_id
 * @property int|null $reviewed_by
 * @property CarbonImmutable|null $reviewed_at
 * @property string|null $decline_reason
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Money $asking_price
 * @property-read Money|null $agreed_price
 * @property-read Supplier $supplier
 * @property-read Category $category
 * @property-read Condition $condition
 * @property-read StockUnit|null $stockUnit
 */
class SupplierOffer extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = ['*'];

    protected $attributes = [
        'status' => 'submitted',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupplierOffer $offer) {
            $offer->reference ??= 'TMP-'.Str::ulid();
        });

        static::created(function (SupplierOffer $offer) {
            if (str_starts_with($offer->reference, 'TMP-')) {
                $offer->reference = sprintf('OF-%05d', $offer->id);
                $offer->saveQuietly();
            }
        });
    }

    /**
     * @return Attribute<Money, never>
     */
    protected function askingPrice(): Attribute
    {
        return Attribute::get(fn () => Money::ofMinor($this->asking_price_amount, $this->asking_price_currency));
    }

    /**
     * @return Attribute<Money|null, never>
     */
    protected function agreedPrice(): Attribute
    {
        return Attribute::get(fn () => $this->agreed_price_amount === null ? null : Money::ofMinor($this->agreed_price_amount, $this->asking_price_currency));
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Condition, $this>
     */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
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
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->nonQueued()->format('webp')->fit(Fit::Crop, 400, 400);
    }

    protected function casts(): array
    {
        return [
            'status' => OfferStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }
}
