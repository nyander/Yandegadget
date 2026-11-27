<?php

namespace Database\Seeders\Support;

use App\Models\Product;
use App\Models\StockUnit;

/**
 * Gives demo products two sample gallery photos and each demo item one photo
 * of its own, skipping anything that already has photos. Local demo data only.
 */
final class SamplePhotos
{
    /**
     * @return array{products: int, items: int}
     */
    public static function addMissing(): array
    {
        $products = 0;
        $items = 0;

        Product::with(['category', 'media'])->each(function (Product $product) use (&$products) {
            if ($product->getMedia('gallery')->isNotEmpty()) {
                return;
            }

            foreach ([0, 1] as $variant) {
                $path = SampleProductImage::product($product->category->slug ?? '', $product->name, (string) $product->brand, $variant);
                $product->addMedia($path)->usingFileName("sample-{$product->slug}-{$variant}.jpg")->toMediaCollection('gallery');
            }

            $products++;
        });

        StockUnit::with(['product.category', 'condition', 'media'])->each(function (StockUnit $unit) use (&$items) {
            if ($unit->getMedia('photos')->isNotEmpty()) {
                return;
            }

            $path = SampleProductImage::item(
                $unit->product->category->slug ?? '',
                $unit->product->name,
                $unit->reference,
                $unit->condition->grade,
                $unit->condition->name,
            );
            $unit->addMedia($path)->usingFileName('sample-'.strtolower($unit->reference).'.jpg')->toMediaCollection('photos');
            $items++;
        });

        return ['products' => $products, 'items' => $items];
    }
}
