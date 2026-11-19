<?php

namespace Database\Seeders;

use App\Enums\StockStatus;
use App\Models\Carrier;
use App\Models\Category;
use App\Models\Condition;
use App\Models\ExchangeRate;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\StockUnit;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ShipmentService;
use Brick\Money\Money;
use Illuminate\Database\Seeder;

/**
 * Sample stock for local development. Never run in production.
 */
class DemoCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::exists()) {
            return;
        }

        // Demo rate only. The admin sets the real rate under Settings → Exchange rates.
        ExchangeRate::create([
            'base_currency' => 'GBP',
            'quote_currency' => 'GHS',
            'rate' => '15.000000',
            'effective_at' => now()->subDay(),
        ]);

        $supplier = Supplier::factory()->create(['name' => 'Demo UK Trader Ltd']);

        $grades = Condition::pluck('id', 'grade');

        // [category slug, name, brand, featured, [[grade, cost, price, status], ...]]
        $catalogue = [
            ['phones', 'iPhone 13 128GB', 'Apple', true, [['A', 290, 395, 'received'], ['B', 250, 340, 'received'], ['C', 200, 275, 'in_transit']]],
            ['phones', 'Galaxy S22 256GB', 'Samsung', false, [['B', 210, 289, 'received']]],
            ['phones', 'Pixel 7 128GB', 'Google', false, [['A', 190, 260, 'reserved']]],
            ['computing', 'MacBook Air M1 13"', 'Apple', true, [['A', 420, 560, 'received'], ['B', 380, 505, 'received']]],
            ['computing', 'ThinkPad T14 Gen 2', 'Lenovo', false, [['B', 260, 355, 'received'], ['A', 300, 410, 'in_transit']]],
            ['tv-audio', 'Bravia 55" 4K TV', 'Sony', true, [['B', 310, 430, 'received']]],
            ['tv-audio', 'Soundbar HW-Q600A', 'Samsung', false, [['A', 120, 170, 'received'], ['B', 95, 140, 'in_transit']]],
            ['gaming', 'PlayStation 5 Disc', 'Sony', true, [['A', 330, 445, 'received']]],
            ['gaming', 'Nintendo Switch OLED', 'Nintendo', false, [['B', 190, 255, 'received'], ['C', 150, 205, 'sold']]],
            ['cameras', 'EOS 250D with 18–55mm', 'Canon', false, [['B', 280, 375, 'received']]],
            ['appliances', 'Nespresso Vertuo Next', 'Nespresso', false, [['A', 45, 70, 'received']]],
            ['smart-tech', 'Apple Watch Series 8 45mm', 'Apple', false, [['B', 160, 220, 'purchased']]],
        ];

        $toShip = [];

        foreach ($catalogue as [$categorySlug, $name, $brand, $featured, $units]) {
            $product = Product::create([
                'category_id' => Category::where('slug', $categorySlug)->value('id'),
                'name' => $name,
                'brand' => $brand,
                'description' => "{$brand} {$name}, bought in the UK and checked before shipping to Accra.",
                'is_featured' => $featured,
            ]);

            foreach ($units as [$grade, $cost, $price, $status]) {
                // In-transit stock is created as purchased, then sent on a real shipment below.
                $unit = StockUnit::factory()
                    ->for($product)
                    ->status($status === 'in_transit' ? StockStatus::Purchased : StockStatus::from($status))
                    ->create([
                        'condition_id' => $grades[$grade],
                        'supplier_id' => $supplier->id,
                        'cost' => Money::of($cost, 'GBP'),
                        'price' => Money::of($price, 'GBP'),
                    ]);

                if ($status === 'in_transit') {
                    $toShip[] = $unit;
                }
            }
        }

        $this->shipDemoStock($toShip);
    }

    /**
     * @param  list<StockUnit>  $units
     */
    private function shipDemoStock(array $units): void
    {
        $admin = User::where('email', 'admin@example.test')->first();

        if ($units === [] || $admin === null) {
            return;
        }

        $shipment = Shipment::create([
            'origin_location_id' => Location::where('code', 'UK-BUYING')->value('id'),
            'destination_location_id' => Location::where('code', 'ACC-LEGON')->value('id'),
            'notes' => 'Demo shipment.',
            'created_by' => $admin->id,
        ]);

        $service = app(ShipmentService::class);
        $service->addUnits($shipment, $units);
        $service->dispatch($shipment, [
            'carrier_id' => Carrier::firstOrCreate(['name' => 'DHL Express'])->id,
            'cost' => Money::of(180, 'GBP'),
            'dispatched_on' => now()->subDays(10)->toDateString(),
            'expected_on' => now()->addDays(4)->toDateString(),
            'tracking_number' => 'DEMO-123456',
        ], $admin);
    }
}
