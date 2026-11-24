<?php

namespace App\Services\Reports;

use App\Enums\BuyingListStatus;
use App\Enums\StockStatus;
use App\Models\BuyingListItem;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockUnit;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * The details behind one row of the dashboard's "What sold" and "Stock age"
 * tables, with a plain suggestion to help decide what to buy next.
 *
 * @phpstan-type SaleRow array{sale: Sale, product: Product, condition: Condition, revenue: Money, profit: Money, cost: Money, ukCost: Money, days: int}
 */
class BuyingInsights
{
    /** Aim to hold about this many months of sales in stock or on the way (shipping takes weeks). */
    public const MONTHS_OF_COVER = 2;

    /** Below this margin, buying more only makes sense at a lower cost. */
    public const THIN_MARGIN = 15.0;

    public function __construct(
        private readonly ReportCurrency $currency,
        private readonly SaleFigures $figures,
    ) {}

    /**
     * One row of "What sold": the sales behind it, best sellers, stock and a suggestion.
     *
     * @param  'category'|'condition'|'location'  $by
     * @return array<string, mixed>
     */
    public function forGroup(CarbonImmutable $from, CarbonImmutable $to, string $by, string $label): array
    {
        $sales = Sale::query()
            ->completed()
            ->whereBetween('sold_at', [$from->startOfDay(), $to->endOfDay()])
            ->with(['stockUnit.product.category', 'stockUnit.condition', 'location'])
            ->latest('sold_at')
            ->get()
            ->filter(fn (Sale $sale) => $this->labelFor($sale, $by) === $label)
            ->values();

        $rows = $sales->map(function (Sale $sale) {
            $revenue = $this->figures->revenue($sale);
            $cost = $this->figures->cost($sale);
            $unit = $sale->stockUnit;
            $arrived = $unit->received_at ?? $unit->purchased_at;

            return [
                'sale' => $sale,
                'product' => $unit->product,
                'condition' => $unit->condition,
                'revenue' => $revenue,
                'profit' => $revenue->minus($cost),
                'cost' => $cost,
                'ukCost' => $unit->cost,
                'days' => max(0, (int) $arrived->diffInDays($sale->sold_at)),
            ];
        });

        $units = $rows->count();
        $revenue = $this->sum($rows->map(fn (array $row) => $row['revenue']));
        $profit = $this->sum($rows->map(fn (array $row) => $row['profit']));
        $months = max(1.0, $from->startOfDay()->diffInDays($to->endOfDay()) / 30.44);
        $perMonth = round($units / $months, 1);

        $stock = $this->stockQuery($by, $label);
        $inStore = (clone $stock)->whereIn('status', [StockStatus::Received, StockStatus::Reserved])->count();
        $onTheWay = (clone $stock)->whereIn('status', [StockStatus::Purchased, StockStatus::InTransit])->count();

        $bestSellers = $this->bestSellers($rows);
        $margin = $revenue->isZero() ? null : $this->percent($profit, $revenue);
        $suggestion = $this->suggest($units, $perMonth, $inStore + $onTheWay, $margin);

        return [
            'title' => $label,
            'by' => $by,
            'units' => $units,
            'revenue' => $revenue,
            'profit' => $profit,
            'marginPercent' => $margin,
            'averageProfit' => $units > 0 ? $profit->dividedBy($units, RoundingMode::HalfUp) : null,
            'averageDays' => $units > 0 ? round($rows->avg('days') ?? 0, 0) : null,
            'perMonth' => $perMonth,
            'inStore' => $inStore,
            'onTheWay' => $onTheWay,
            'onList' => $this->openOnList($by, $label),
            'bestSellers' => $bestSellers,
            'recent' => $rows->take(8)->map(fn (array $row) => [
                'date' => $row['sale']->sold_at,
                'reference' => $row['sale']->reference,
                'product' => $row['product']->name,
                'condition' => $row['condition']->name,
                'revenue' => $row['revenue'],
                'profit' => $row['profit'],
                'days' => $row['days'],
            ])->all(),
            'suggestion' => $suggestion,
            'prefill' => $this->prefill($by, $label, $bestSellers, $suggestion['quantity']),
        ];
    }

    /**
     * One row of "Stock age": the items sitting in that band, oldest first.
     *
     * @return array<string, mixed>
     */
    public function forAgeBand(int $band): array
    {
        $bands = SalesReport::AGE_BANDS;
        $range = $bands[$band] ?? $bands[0];
        $now = CarbonImmutable::now();

        $items = StockUnit::query()
            ->whereIn('status', [StockStatus::Received, StockStatus::Reserved])
            ->with(['product.category', 'condition', 'location'])
            ->get()
            ->map(function (StockUnit $unit) use ($now) {
                $arrived = $unit->received_at ?? $unit->created_at ?? $now;

                return [
                    'unit' => $unit,
                    'days' => (int) $arrived->diffInDays($now),
                    'cost' => $this->currency->convert($unit->cost, $now),
                    'price' => $this->currency->convert($unit->price, $now),
                ];
            })
            ->filter(fn (array $item) => $item['days'] >= $range['min'] && $item['days'] < $range['max'])
            ->sortByDesc('days')
            ->values();

        $byCategory = $items
            ->groupBy(fn (array $item) => $item['unit']->product->category->name ?? 'Uncategorised')
            ->map->count()
            ->sortDesc()
            ->all();

        return [
            'title' => $range['label'],
            'units' => $items->count(),
            'value' => $this->sum($items->map(fn (array $item) => $item['cost'])),
            'byCategory' => $byCategory,
            'items' => $items->map(fn (array $item) => [
                'id' => $item['unit']->id,
                'reference' => $item['unit']->reference,
                'product' => $item['unit']->product->name,
                'category' => $item['unit']->product->category->name ?? 'Uncategorised',
                'condition' => $item['unit']->condition->name,
                'location' => $item['unit']->location->name,
                'status' => $item['unit']->status,
                'days' => $item['days'],
                'cost' => $item['cost'],
                'price' => $item['price'],
            ])->all(),
            'advice' => match (true) {
                $items->isEmpty() => null,
                $range['min'] >= 90 => 'These have been in the store three months or more. Think about a lower price, and don\'t buy more of the same until they sell.',
                $range['min'] >= 60 => 'These are slowing down. Check the prices, and hold off buying more of the same.',
                default => 'Recently arrived stock. Nothing to worry about yet.',
            },
        ];
    }

    /** @param 'category'|'condition'|'location' $by */
    private function labelFor(Sale $sale, string $by): string
    {
        return match ($by) {
            'category' => $sale->stockUnit->product->category->name ?? 'Uncategorised',
            'condition' => $sale->stockUnit->condition->name,
            'location' => $sale->location->name,
        };
    }

    /**
     * Stock units in the same group as the row.
     *
     * @param  'category'|'condition'|'location'  $by
     * @return Builder<StockUnit>
     */
    private function stockQuery(string $by, string $label): Builder
    {
        return match ($by) {
            'category' => StockUnit::query()->whereHas('product.category', fn (Builder $q) => $q->where('name', $label)),
            'condition' => StockUnit::query()->whereHas('condition', fn (Builder $q) => $q->where('name', $label)),
            'location' => StockUnit::query()->whereHas('location', fn (Builder $q) => $q->where('name', $label)),
        };
    }

    /** @param 'category'|'condition'|'location' $by */
    private function openOnList(string $by, string $label): int
    {
        $query = BuyingListItem::query()->where('status', BuyingListStatus::ToBuy);

        return match ($by) {
            'category' => $query->whereHas('category', fn (Builder $q) => $q->where('name', $label))->count(),
            'condition' => $query->whereHas('condition', fn (Builder $q) => $q->where('name', $label))->count(),
            'location' => $query->count(),
        };
    }

    /**
     * The products that sold most in the group, with what they cost in the UK.
     *
     * @param  Collection<int, SaleRow>  $rows
     * @return list<array<string, mixed>>
     */
    private function bestSellers(Collection $rows): array
    {
        return array_values($rows
            ->groupBy(fn (array $row) => $row['product']->id)
            ->map(function (Collection $group) {
                $first = $group->first();
                $ukCosts = $group->pluck('ukCost')->filter(fn (Money $m) => $m->getCurrency()->getCurrencyCode() === 'GBP');
                $conditions = $group->groupBy(fn (array $row) => $row['condition']->id);

                return [
                    'product' => $first['product']->name,
                    'productId' => $first['product']->id,
                    'categoryId' => $first['product']->category_id,
                    'conditionId' => (int) $conditions->sortByDesc(fn (Collection $c) => $c->count())->keys()->first(),
                    'units' => $group->count(),
                    'profit' => $this->sum($group->map(fn (array $row) => $row['profit'])),
                    'averageDays' => (int) round($group->avg('days') ?? 0),
                    'averageUkCost' => $ukCosts->isEmpty()
                        ? null
                        : Money::ofMinor((int) round($ukCosts->avg(fn (Money $m) => $m->getMinorAmount()->toInt()) ?? 0), 'GBP'),
                ];
            })
            ->sort(fn (array $a, array $b) => [$b['units'], $b['profit']->getMinorAmount()->toInt()] <=> [$a['units'], $a['profit']->getMinorAmount()->toInt()])
            ->take(5)
            ->all());
    }

    /**
     * A plain suggestion: buy more, hold off, or buy only cheaper.
     *
     * @return array{tone: string, text: string, quantity: int}
     */
    private function suggest(int $units, float $perMonth, int $stock, ?float $margin): array
    {
        if ($units === 0) {
            return ['tone' => 'hold', 'text' => 'Nothing sold in this period. Don\'t buy more until it starts selling.', 'quantity' => 1];
        }

        $target = (int) ceil($perMonth * self::MONTHS_OF_COVER);
        $short = max(0, $target - $stock);
        $cover = $perMonth > 0 ? round($stock / $perMonth, 1) : null;

        if ($margin !== null && $margin < self::THIN_MARGIN) {
            return [
                'tone' => 'careful',
                'text' => sprintf('The margin is thin (%.1f%%). Only buy more if you can pay less in the UK than last time.', $margin),
                'quantity' => max(1, $short),
            ];
        }

        if ($short > 0) {
            return [
                'tone' => 'buy',
                'text' => sprintf(
                    'Selling about %s a month, with %d in stock or on the way. Buy about %d more to cover the next %d months.',
                    rtrim(rtrim(number_format($perMonth, 1), '0'), '.'),
                    $stock,
                    $short,
                    self::MONTHS_OF_COVER,
                ),
                'quantity' => $short,
            ];
        }

        return [
            'tone' => 'hold',
            'text' => sprintf('You have enough for about %s months at the pace you\'re selling, counting what\'s on the way.', $cover === null ? 'several' : rtrim(rtrim(number_format($cover, 1), '0'), '.')),
            'quantity' => 1,
        ];
    }

    /**
     * Starting values for "Add to buying list": the best seller in the group.
     *
     * @param  'category'|'condition'|'location'  $by
     * @param  list<array<string, mixed>>  $bestSellers
     * @return array<string, mixed>
     */
    private function prefill(string $by, string $label, array $bestSellers, int $quantity): array
    {
        $top = $bestSellers[0] ?? null;

        $categoryId = $by === 'category'
            ? Category::where('name', $label)->value('id')
            : ($top['categoryId'] ?? null);
        $conditionId = $by === 'condition'
            ? Condition::where('name', $label)->value('id')
            : ($top['conditionId'] ?? null);

        return [
            'description' => $top['product'] ?? $label,
            'category_id' => $categoryId,
            'condition_id' => $conditionId,
            'quantity' => max(1, $quantity),
            'target_cost' => $top['averageUkCost'] ?? null,
        ];
    }

    /** @param Collection<int, Money> $amounts */
    private function sum(Collection $amounts): Money
    {
        return $amounts->reduce(fn (Money $carry, Money $amount) => $carry->plus($amount), $this->currency->zero());
    }

    private function percent(Money $part, Money $whole): float
    {
        return $part->getAmount()->dividedBy($whole->getAmount(), 4, RoundingMode::HalfUp)->multipliedBy(100)->toFloat();
    }
}
