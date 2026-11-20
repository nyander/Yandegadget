<?php

namespace App\Models;

use App\Enums\StockStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A catalogue entry such as "iPhone 13 128GB". Each physical item for sale is a StockUnit.
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $brand
 * @property string|null $model
 * @property string|null $description
 * @property array<string, string>|null $specs
 * @property bool $is_featured
 * @property bool $is_published
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Category $category
 */
#[Fillable(['category_id', 'name', 'slug', 'brand', 'model', 'description', 'specs', 'is_featured', 'is_published'])]
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, InteractsWithMedia, Searchable, SoftDeletes;

    /** Mirrors the column defaults so new models behave the same before a refresh. */
    protected $attributes = [
        'is_featured' => false,
        'is_published' => true,
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (blank($product->slug)) {
                $product->slug = static::uniqueSlug($product->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<StockUnit, $this>
     */
    public function stockUnits(): HasMany
    {
        return $this->hasMany(StockUnit::class);
    }

    /**
     * Units a customer can see: in Ghana and not sold.
     *
     * @return HasMany<StockUnit, $this>
     */
    public function visibleUnits(): HasMany
    {
        return $this->stockUnits()->whereIn('status', StockStatus::customerVisibleValues());
    }

    /**
     * Published products with at least one unit a customer can see.
     *
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function visibleInShop(Builder $query): void
    {
        $query->where('is_published', true)->whereHas('visibleUnits');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->nonQueued()->format('webp')->fit(Fit::Crop, 600, 600);
        $this->addMediaConversion('large')->nonQueued()->format('webp')->fit(Fit::Max, 1600, 1600);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'brand' => $this->brand,
            'model' => $this->model,
            'description' => $this->description,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_published;
    }

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }
}
