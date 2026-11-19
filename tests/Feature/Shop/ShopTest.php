<?php

namespace Tests\Feature\Shop;

use App\Enums\StockStatus;
use App\Models\Category;
use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\StockUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ExchangeRate::create([
            'base_currency' => 'GBP',
            'quote_currency' => 'GHS',
            'rate' => '15.000000',
            'effective_at' => now()->subHour(),
        ]);
    }

    private function productWith(StockStatus $status, array $productAttributes = [], int|string $price = 100): Product
    {
        $product = Product::factory()->create($productAttributes);
        StockUnit::factory()->for($product)->status($status)->priced($price)->create();

        return $product;
    }

    public function test_only_received_or_reserved_stock_from_published_products_is_listed(): void
    {
        $inStore = $this->productWith(StockStatus::Received);
        $reserved = $this->productWith(StockStatus::Reserved);
        $inTransit = $this->productWith(StockStatus::InTransit);
        $sold = $this->productWith(StockStatus::Sold);
        $unpublished = $this->productWith(StockStatus::Received, ['is_published' => false]);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/index')
                ->where('products.total', 2)
                ->where('products.data', fn ($products) => collect($products)->pluck('id')->sort()->values()->all()
                    === collect([$inStore->id, $reserved->id])->sort()->values()->all()));

        $this->get(route('shop.show', $inTransit))->assertNotFound();
        $this->get(route('shop.show', $sold))->assertNotFound();
        $this->get(route('shop.show', $unpublished))->assertNotFound();
    }

    public function test_prices_are_shown_in_cedis_and_never_expose_cost_or_gbp(): void
    {
        $product = $this->productWith(StockStatus::Received, price: 80);

        $this->get(route('shop.show', $product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/show')
                ->where('product.units.0.price.currency', 'GHS')
                ->where('product.units.0.price.amount', 120000)
                ->missing('product.units.0.cost')
                ->missing('product.units.0.supplier'));

        $this->get(route('shop.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data.0.from_price.amount', 120000)
                ->where('products.data.0.from_price.currency', 'GHS'));
    }

    public function test_from_price_is_the_cheapest_unit_in_store(): void
    {
        $product = Product::factory()->create();
        StockUnit::factory()->for($product)->received()->priced(300)->create();
        StockUnit::factory()->for($product)->received()->priced(200)->create();
        StockUnit::factory()->for($product)->status(StockStatus::InTransit)->priced(50)->create();

        $this->get(route('shop.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data.0.from_price.amount', 300000)
                ->where('products.data.0.in_store_count', 2));
    }

    public function test_falls_back_to_list_price_when_no_rate_is_set(): void
    {
        ExchangeRate::query()->delete();
        $product = $this->productWith(StockStatus::Received, price: 80);

        $this->get(route('shop.show', $product))
            ->assertInertia(fn (Assert $page) => $page
                ->where('product.units.0.price.currency', 'GBP')
                ->where('product.units.0.price.amount', 8000));
    }

    public function test_it_filters_by_category(): void
    {
        $phones = Category::where('slug', 'phones')->firstOrFail();
        $gaming = Category::where('slug', 'gaming')->firstOrFail();
        $phone = $this->productWith(StockStatus::Received, ['category_id' => $phones->id]);
        $this->productWith(StockStatus::Received, ['category_id' => $gaming->id]);

        $this->get(route('shop.index', ['category' => 'phones']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.total', 1)
                ->where('products.data.0.id', $phone->id)
                ->where('activeCategory.slug', 'phones'));
    }

    public function test_it_sorts_by_price(): void
    {
        $cheap = $this->productWith(StockStatus::Received, price: 50);
        $dear = $this->productWith(StockStatus::Received, price: 500);
        $middle = $this->productWith(StockStatus::Received, price: 200);

        $this->get(route('shop.index', ['sort' => 'price_asc']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data', fn ($products) => collect($products)->pluck('id')->all() === [$cheap->id, $middle->id, $dear->id]));

        $this->get(route('shop.index', ['sort' => 'price_desc']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data', fn ($products) => collect($products)->pluck('id')->all() === [$dear->id, $middle->id, $cheap->id]));
    }

    public function test_it_searches_by_name_and_brand(): void
    {
        $iphone = $this->productWith(StockStatus::Received, ['name' => 'iPhone 13 128GB', 'brand' => 'Apple']);
        $this->productWith(StockStatus::Received, ['name' => 'Bravia 55 TV', 'brand' => 'Sony']);

        $this->get(route('shop.index', ['q' => 'iphone']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.total', 1)
                ->where('products.data.0.id', $iphone->id));

        $this->get(route('shop.index', ['q' => 'apple']))
            ->assertInertia(fn (Assert $page) => $page->where('products.total', 1));
    }

    public function test_invalid_sort_is_rejected(): void
    {
        $this->get(route('shop.index', ['sort' => 'drop table']))->assertSessionHasErrors('sort');
    }

    public function test_home_page_shows_featured_and_new_arrivals(): void
    {
        $featured = $this->productWith(StockStatus::Received, ['is_featured' => true]);
        $plain = $this->productWith(StockStatus::Received);
        $this->productWith(StockStatus::InTransit, ['is_featured' => true]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/home')
                ->has('featured', 1)
                ->where('featured.0.id', $featured->id)
                ->has('newArrivals', 1)
                ->where('newArrivals.0.id', $plain->id));
    }

    public function test_hidden_categories_hide_their_products(): void
    {
        $phones = Category::where('slug', 'phones')->firstOrFail();
        $this->productWith(StockStatus::Received, ['category_id' => $phones->id]);
        $phones->update(['is_active' => false]);

        $this->get(route('shop.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.total', 0)
                ->has('categories', 0));
    }

    public function test_product_photos_are_resized_and_served(): void
    {
        Storage::fake('public');
        $product = $this->productWith(StockStatus::Received);
        $product->addMedia(UploadedFile::fake()->image('front.jpg', 2000, 2000))->toMediaCollection('gallery');

        $media = $product->getFirstMedia('gallery');
        $this->assertNotNull($media);
        $this->assertTrue($media->hasGeneratedConversion('thumb'));
        $this->assertTrue($media->hasGeneratedConversion('large'));

        $this->get(route('shop.show', $product))
            ->assertInertia(fn (Assert $page) => $page
                ->has('product.images', 1)
                ->where('product.images.0.thumb', fn ($url) => str_ends_with($url, '-thumb.webp')));
    }
}
