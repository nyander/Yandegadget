<?php

namespace App\Http\Presenters;

use App\Enums\StockStatus;
use App\Models\Product;
use App\Models\StockUnit;
use App\Services\Pricing;
use Brick\Money\Money;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Shapes products for the customer-facing shop. Never exposes cost, supplier
 * or GBP list prices.
 */
class ShopProductPresenter
{
    public function __construct(private readonly Pricing $pricing) {}

    /**
     * Expects `from_price_amount`, `in_store_count` and `reserved_count` from ProductController::baseQuery().
     *
     * @return array<string, mixed>
     */
    public function card(Product $product): array
    {
        $fromAmount = $product->getAttribute('from_price_amount');
        $thumb = $product->getFirstMedia('gallery');

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'brand' => $product->brand,
            'category' => [
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ],
            'image' => $thumb ? $this->image($thumb) : null,
            'from_price' => $fromAmount !== null
                ? $this->pricing->presentForCustomer(Money::ofMinor((int) $fromAmount, (string) config('shop.list_currency')))
                : null,
            'in_store_count' => (int) $product->getAttribute('in_store_count'),
            'reserved_count' => (int) $product->getAttribute('reserved_count'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(Product $product): array
    {
        $units = $product->visibleUnits()
            ->with(['condition', 'media'])
            ->get()
            ->sortBy([
                fn (StockUnit $a, StockUnit $b) => $a->status === StockStatus::Received ? ($b->status === StockStatus::Received ? 0 : -1) : 1,
                fn (StockUnit $a, StockUnit $b) => $a->price_amount <=> $b->price_amount,
            ])
            ->values();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'brand' => $product->brand,
            'model' => $product->model,
            'description' => $product->description,
            'specs' => $product->specs ?? [],
            'category' => [
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ],
            'images' => $product->getMedia('gallery')->map(fn (Media $media) => $this->image($media))->values()->all(),
            'units' => $units->map(fn (StockUnit $unit) => [
                'reference' => $unit->reference,
                'status' => $unit->status === StockStatus::Received ? 'in_store' : 'reserved',
                'price' => $this->pricing->presentForCustomer($unit->price),
                'condition' => [
                    'grade' => $unit->condition->grade,
                    'name' => $unit->condition->name,
                    'description' => $unit->condition->description,
                ],
                'condition_notes' => $unit->condition_notes,
                'photos' => $unit->getMedia('photos')->map(fn (Media $media) => $this->image($media))->values()->all(),
            ])->all(),
        ];
    }

    /**
     * @return array{thumb: string, large: string, alt: string}
     */
    private function image(Media $media): array
    {
        return [
            'thumb' => $media->getUrl('thumb'),
            'large' => $media->getUrl('large'),
            'alt' => (string) ($media->getCustomProperty('alt') ?? $media->name),
        ];
    }
}
