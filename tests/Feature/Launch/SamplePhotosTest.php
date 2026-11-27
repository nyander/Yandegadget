<?php

namespace Tests\Feature\Launch;

use App\Models\Product;
use App\Models\StockUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SamplePhotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_products_and_items_get_labelled_sample_photos_once(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create(['name' => 'iPhone 13 128GB']);
        $unit = StockUnit::factory()->for($product)->received()->create();

        $this->artisan('demo:photos')
            ->expectsOutputToContain('Added sample photos to 1 products and 1 items.')
            ->assertSuccessful();

        $this->assertCount(2, $product->fresh()?->getMedia('gallery') ?? []);
        $this->assertCount(1, $unit->fresh()?->getMedia('photos') ?? []);
        $this->assertStringStartsWith('sample-', (string) $product->fresh()?->getFirstMedia('gallery')?->file_name);

        // Run again: nothing that already has photos is touched.
        $this->artisan('demo:photos')->expectsOutputToContain('Added sample photos to 0 products and 0 items.');
    }

    public function test_it_refuses_to_run_on_the_live_site(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->artisan('demo:photos')->assertFailed();
    }
}
