<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $products = Product::query()
            ->visibleInShop()
            ->latest('updated_at')
            ->get(['slug', 'updated_at']);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('slug');

        return response()
            ->view('sitemap', ['products' => $products, 'categories' => $categories])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            ...array_map(
                fn (string $path) => "Disallow: /{$path}",
                ['admin', 'staff', 'supplier', 'requests', 'dashboard', 'settings', 'payments', 'login', 'register'],
            ),
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain');
    }
}
