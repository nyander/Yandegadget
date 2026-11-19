<?php

namespace App\Http\Controllers\Shop;

use App\Enums\StockStatus;
use App\Http\Controllers\Controller;
use App\Http\Presenters\ShopProductPresenter;
use App\Models\Category;
use App\Models\Product;
use App\Services\Pricing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public const SORTS = ['newest', 'price_asc', 'price_desc'];

    public function __construct(private readonly ShopProductPresenter $presenter) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:100'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(self::SORTS)],
        ]);

        $category = filled($filters['category'] ?? null)
            ? Category::where('slug', $filters['category'])->where('is_active', true)->first()
            : null;

        $search = trim((string) ($filters['q'] ?? ''));
        $sort = $filters['sort'] ?? 'newest';

        $query = self::baseQuery()
            ->when($category, fn (Builder $q, Category $c) => $q->where('category_id', $c->id))
            ->when($search !== '', fn (Builder $q) => $q->whereIn('products.id', Product::search($search)->keys()));

        match ($sort) {
            'price_asc' => $query->orderByRaw('from_price_amount is null')->orderBy('from_price_amount'),
            'price_desc' => $query->orderByRaw('from_price_amount is null')->orderByDesc('from_price_amount'),
            default => $query->latest('products.created_at')->latest('products.id'),
        };

        $products = $query
            ->paginate((int) config('shop.per_page'))
            ->withQueryString()
            ->through(fn (Product $product) => $this->presenter->card($product));

        return Inertia::render('shop/index', [
            'products' => $products,
            'categories' => self::categories(),
            'filters' => [
                'category' => $category?->slug,
                'q' => $search,
                'sort' => $sort,
            ],
            'activeCategory' => $category?->only(['name', 'slug']),
        ]);
    }

    public function show(Product $product, Pricing $pricing): Response
    {
        abort_unless(
            Product::visibleInShop()->whereKey($product->id)->exists(),
            404,
        );

        $product->load(['category', 'media']);

        $rate = $pricing->rate((string) config('shop.list_currency'), (string) config('shop.display_currency'));

        return Inertia::render('shop/show', [
            'product' => $this->presenter->detail($product),
            'rateUpdatedAt' => $rate?->effective_at->toIso8601String(),
        ]);
    }

    /**
     * Visible products with the counts and "from" price that product cards need.
     *
     * @return Builder<Product>
     */
    public static function baseQuery(): Builder
    {
        return Product::query()
            ->visibleInShop()
            ->whereHas('category', fn (Builder $q) => $q->where('is_active', true))
            ->with(['category', 'media'])
            ->withMin(['stockUnits as from_price_amount' => fn (Builder $q) => $q->where('status', StockStatus::Received)], 'price_amount')
            ->withCount([
                'stockUnits as in_store_count' => fn (Builder $q) => $q->where('status', StockStatus::Received),
                'stockUnits as reserved_count' => fn (Builder $q) => $q->where('status', StockStatus::Reserved),
            ]);
    }

    /**
     * Active categories that currently have something in the shop.
     *
     * @return array<int, array{name: string, slug: string, count: int}>
     */
    public static function categories(): array
    {
        return Category::query()
            ->where('is_active', true)
            ->withCount(['products' => function (Builder $q) {
                /** @var Builder<Product> $q */
                $q->visibleInShop();
            }])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Category $c) => $c->getAttribute('products_count') > 0)
            ->map(fn (Category $c) => ['name' => $c->name, 'slug' => $c->slug, 'count' => (int) $c->getAttribute('products_count')])
            ->values()
            ->all();
    }
}
