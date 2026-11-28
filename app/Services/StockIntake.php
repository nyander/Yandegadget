<?php

namespace App\Services;

use App\Enums\StockStatus;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\StockUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Records several items of one product bought together (same grade, cost and
 * price) as a bulk batch: each is still its own stock unit with its own
 * reference, but they share their details and are edited together.
 */
class StockIntake
{
    public const MAX_AT_ONCE = 50;

    /**
     * @param  array<string, mixed>  $shared  Stock unit fields every item gets (condition_id, cost, price, ...).
     * @param  list<string>  $serials  One per item, in order; fewer than $quantity leaves the rest blank.
     * @return list<StockUnit>
     */
    public function addSeveral(Product $product, array $shared, int $quantity, array $serials, User $by): array
    {
        $quantity = max(1, min(self::MAX_AT_ONCE, $quantity));

        return DB::transaction(function () use ($product, $shared, $quantity, $serials, $by): array {
            $batch = StockBatch::create([
                'product_id' => $product->id,
                'condition_id' => $shared['condition_id'],
                'supplier_id' => $shared['supplier_id'] ?? null,
                'quantity' => $quantity,
                'cost' => $shared['cost'],
                'price' => $shared['price'],
                'purchased_at' => $shared['purchased_at'],
                'condition_notes' => $shared['condition_notes'] ?? null,
                'created_by' => $by->id,
            ]);

            $units = [];

            for ($i = 0; $i < $quantity; $i++) {
                $units[] = $product->stockUnits()->create([
                    ...$shared,
                    'batch_id' => $batch->id,
                    'serial_number' => $serials[$i] ?? null,
                    'created_by' => $by->id,
                ]);
            }

            return $units;
        });
    }

    /**
     * Changes a bulk batch: the new details go on every item not yet sold.
     * Sold items keep what they had, so past sales and reports don't change.
     *
     * @param  array<string, mixed>  $changes  condition_id, supplier_id, cost, price, purchased_at, condition_notes.
     * @return int How many items were updated.
     */
    public function updateBatch(StockBatch $batch, array $changes): int
    {
        $fields = array_intersect_key($changes, array_flip(['condition_id', 'supplier_id', 'cost', 'price', 'purchased_at', 'condition_notes']));

        return DB::transaction(function () use ($batch, $fields): int {
            $batch->fill($fields)->save();
            $updated = 0;

            foreach ($batch->unsoldUnits()->lockForUpdate()->get() as $unit) {
                $unit->fill($fields)->save();
                $updated++;
            }

            return $updated;
        });
    }

    /**
     * Changes several items at once (any items, bulk or one-off). Same rule: sold items aren't touched.
     *
     * @param  iterable<StockUnit>  $units
     * @param  array<string, mixed>  $changes
     * @return int How many items were updated.
     */
    public function updateUnits(iterable $units, array $changes): int
    {
        $fields = array_filter(
            array_intersect_key($changes, array_flip(['condition_id', 'supplier_id', 'cost', 'price', 'condition_notes'])),
            fn (mixed $value) => $value !== null && $value !== '',
        );

        if ($fields === []) {
            return 0;
        }

        return DB::transaction(function () use ($units, $fields): int {
            $updated = 0;

            foreach ($units as $unit) {
                if (in_array($unit->status, [StockStatus::Sold, StockStatus::WrittenOff], true)) {
                    continue;
                }

                $unit->fill($fields)->save();
                $updated++;
            }

            return $updated;
        });
    }

    /**
     * Serial numbers typed one per line, blanks and spaces removed.
     *
     * @return list<string>
     */
    public static function parseSerials(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text) ?: []), fn (string $line) => $line !== ''));
    }
}
