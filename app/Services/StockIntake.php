<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Records several items of one product bought together (same grade, cost
 * and price), each still its own stock unit with its own reference.
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
            $units = [];

            for ($i = 0; $i < $quantity; $i++) {
                $units[] = $product->stockUnits()->create([
                    ...$shared,
                    'serial_number' => $serials[$i] ?? null,
                    'created_by' => $by->id,
                ]);
            }

            return $units;
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
