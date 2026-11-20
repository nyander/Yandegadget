<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Condition;
use Brick\Money\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Categories and conditions carried over from the 2020 seeders.
 */
class CatalogueReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Phones', 'Appliances', 'TV & Audio', 'Computing', 'Gaming', 'Cameras', 'Smart Tech'];

        foreach ($categories as $index => $name) {
            Category::updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'sort_order' => $index + 1,
            ]);
        }

        // Deposits are the 2020 amounts, which were charged in GBP. Revisit once
        // the payment provider is chosen (see docs/REBUILD_PLAN.md, decision 2).
        $conditions = [
            ['A', 'Mint', 'Mint condition, with the original packaging and all original accessories.', 80_00],
            ['B', 'Good', 'Good condition, with all essential accessories. No packaging.', 70_00],
            ['C', 'Working', 'Shows wear but works, with all essential accessories. No packaging.', 50_00],
        ];

        foreach ($conditions as $index => [$grade, $name, $description, $depositPence]) {
            Condition::updateOrCreate(['grade' => $grade], [
                'name' => $name,
                'description' => $description,
                'deposit' => Money::ofMinor($depositPence, 'GBP'),
                'sort_order' => $index + 1,
            ]);
        }
    }
}
