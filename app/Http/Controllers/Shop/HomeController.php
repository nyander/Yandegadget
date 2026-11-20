<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Presenters\ShopProductPresenter;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(ShopProductPresenter $presenter): Response
    {
        $featured = ProductController::baseQuery()
            ->where('is_featured', true)
            ->latest('products.updated_at')
            ->limit(8)
            ->get();

        $newArrivals = ProductController::baseQuery()
            ->whereNotIn('products.id', $featured->modelKeys())
            ->latest('products.created_at')
            ->limit(8)
            ->get();

        return Inertia::render('shop/home', [
            'featured' => $featured->map(fn (Product $p) => $presenter->card($p))->all(),
            'newArrivals' => $newArrivals->map(fn (Product $p) => $presenter->card($p))->all(),
            'categories' => ProductController::categories(),
        ]);
    }
}
