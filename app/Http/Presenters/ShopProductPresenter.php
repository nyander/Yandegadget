<?php

namespace App\Http\Presenters;

use App\Enums\StockStatus;
use App\Models\Product;
use App\Models\StockUnit;
use App\Services\Pricing;
use Brick\Money\Money;
use Illuminate\Support\Collection;
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
            'units' => $this->groupBulk($units)->map(fn (array $group) => [
                'reference' => $group['unit']->reference,
                'status' => $group['available'] > 0 ? 'in_store' : 'reserved',
                // Bulk stock shows as one line: how many of them are still available.
                'bulk' => $group['unit']->batch_id !== null,
                'available' => $group['available'],
                'price' => $this->pricing->presentForCustomer($group['unit']->price),
                'condition' => [
                    'grade' => $group['unit']->condition->grade,
                    'name' => $group['unit']->condition->name,
                    'description' => $group['unit']->condition->description,
                ],
                'condition_notes' => $group['unit']->condition_notes,
                'photos' => $group['unit']->getMedia('photos')->map(fn (Media $media) => $this->image($media))->values()->all(),
                'reserve_url' => $group['available'] > 0
                    ? route('reservations.create', ['product' => $product->slug, 'reference' => $group['unit']->reference])
                    : null,
            ])->values()->all(),
        ];
    }

    /**
     * One-off items stay one each; items from the same bulk purchase become one
     * entry showing the first available unit and how many are left.
     *
     * @param  Collection<int, StockUnit>  $units
     * @return Collection<int, array{unit: StockUnit, available: int}>
     */
    private function groupBulk(Collection $units): Collection
    {
        $groups = collect();

        foreach ($units as $unit) {
            $key = $unit->batch_id !== null ? "batch-{$unit->batch_id}" : "unit-{$unit->id}";
            $inStore = $unit->status === StockStatus::Received ? 1 : 0;

            if (! $groups->has($key)) {
                $groups->put($key, ['unit' => $unit, 'available' => $inStore]);

                continue;
            }

            $group = $groups->get($key);
            $group['available'] += $inStore;
            // Show (and reserve) an available one if the first was reserved.
            if ($group['unit']->status !== StockStatus::Received && $inStore === 1) {
                $group['unit'] = $unit;
            }
            $groups->put($key, $group);
        }

        return $groups->values();
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
