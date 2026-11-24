<?php

namespace App\Services\Reports;

use App\Enums\StockStatus;
use App\Models\Sale;
use App\Models\StockUnit;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Figures for the admin dashboard: sales and margin over a range, what sold by
 * category, condition and store, and how long stock has been sitting.
 */
class SalesReport
{
    /** Stock age bands: under each 'max' days since arrival. */
    public const AGE_BANDS = [
        ['label' => 'Under 30 days', 'min' => 0, 'max' => 30],
        ['label' => '30–59 days', 'min' => 30, 'max' => 60],
        ['label' => '60–89 days', 'min' => 60, 'max' => 90],
        ['label' => '90 days or more', 'min' => 90, 'max' => PHP_INT_MAX],
    ];

    public function __construct(
        private readonly ReportCurrency $currency,
        private readonly SaleFigures $figures,
    ) {}

    /**
     * @param  array<string, string>  $only  Only sales matching these groups, e.g. ['category' => 'Phones'].
     * @return array{units: int, revenue: Money, cost: Money, profit: Money, marginPercent: float|null, averageDiscount: float|null}
     */
    public function summary(CarbonImmutable $from, CarbonImmutable $to, array $only = []): array
    {
        $sales = $this->sales($from, $to, $only);
        $totals = $this->totals($sales);

        return [
            'units' => $sales->count(),
            ...$totals,
            'averageDiscount' => $sales->isEmpty() ? null : round($sales->avg(fn (Sale $sale) => $sale->discountPercent()) ?? 0, 1),
        ];
    }

    /**
     * Units sold, revenue and profit grouped by category, condition or location.
     *
     * Without a $sort, conditions read best to worst (as graded) and everything
     * else biggest sales first. Ties always go alphabetically.
     *
     * @param  'category'|'condition'|'location'  $by
     * @param  array<string, string>  $only  Only sales matching these groups, e.g. ['location' => 'Legon'].
     * @param  'label'|'units'|'revenue'|'profit'|'margin'|'grade'|null  $sort
     * @param  'asc'|'desc'  $direction
     * @return list<array{label: string, units: int, revenue: Money, profit: Money, marginPercent: float|null}>
     */
    public function breakdown(CarbonImmutable $from, CarbonImmutable $to, string $by, array $only = [], ?string $sort = null, string $direction = 'desc'): array
    {
        $sales = $this->sales($from, $to, $only);

        $gradeOrder = $sales->mapWithKeys(fn (Sale $sale) => [
            $sale->stockUnit->condition->name => $sale->stockUnit->condition->sort_order,
        ]);

        if ($sort === null) {
            [$sort, $direction] = $by === 'condition' ? ['grade', 'asc'] : ['revenue', 'desc'];
        }

        $key = fn (array $row): int|float|string => match ($sort) {
            'label' => mb_strtolower($row['label']),
            'units' => $row['units'],
            'revenue' => $row['revenue']->getMinorAmount()->toInt(),
            'profit' => $row['profit']->getMinorAmount()->toInt(),
            'margin' => $row['marginPercent'] ?? -INF,
            'grade' => $gradeOrder[$row['label']] ?? PHP_INT_MAX,
        };

        return array_values($sales
            ->groupBy(fn (Sale $sale) => self::labelFor($sale, $by))
            ->map(function (Collection $group, string $label) {
                $totals = $this->totals($group);

                return [
                    'label' => $label,
                    'units' => $group->count(),
                    'revenue' => $totals['revenue'],
                    'profit' => $totals['profit'],
                    'marginPercent' => $totals['marginPercent'],
                ];
            })
            ->sort(function (array $a, array $b) use ($key, $direction) {
                $order = $direction === 'asc' ? $key($a) <=> $key($b) : $key($b) <=> $key($a);

                return $order !== 0 ? $order : strnatcasecmp($a['label'], $b['label']);
            })
            ->all());
    }

    /**
     * The group a sale falls in: its category, condition or store name.
     *
     * @param  'category'|'condition'|'location'  $by
     */
    public static function labelFor(Sale $sale, string $by): string
    {
        return match ($by) {
            'category' => $sale->stockUnit->product->category->name ?? 'Uncategorised',
            'condition' => $sale->stockUnit->condition->name,
            'location' => $sale->location->name,
        };
    }

    /**
     * Stock in store or reserved, bucketed by days since it arrived.
     *
     * @return array{units: int, value: Money, averageDays: float|null, buckets: list<array{label: string, units: int, value: Money}>}
     */
    public function stockAge(): array
    {
        $now = CarbonImmutable::now();
        $buckets = self::AGE_BANDS;
        $rows = array_map(fn (array $b) => ['label' => $b['label'], 'units' => 0, 'value' => $this->currency->zero()], $buckets);

        $units = StockUnit::query()
            ->whereIn('status', [StockStatus::Received, StockStatus::Reserved])
            ->get(['id', 'cost_amount', 'cost_currency', 'received_at', 'created_at']);

        $days = [];
        $total = $this->currency->zero();

        foreach ($units as $unit) {
            $arrived = $unit->received_at ?? $unit->created_at ?? $now;
            $age = (int) $arrived->diffInDays($now);
            $value = $this->currency->convert($unit->cost, $now);
            $days[] = $age;
            $total = $total->plus($value);

            foreach ($buckets as $i => $bucket) {
                if ($age < $bucket['max']) {
                    $rows[$i]['units']++;
                    $rows[$i]['value'] = $rows[$i]['value']->plus($value);
                    break;
                }
            }
        }

        return [
            'units' => count($days),
            'value' => $total,
            'averageDays' => $days === [] ? null : round(array_sum($days) / count($days), 1),
            'buckets' => $rows,
        ];
    }

    /**
     * Revenue and gross profit per calendar month, oldest first.
     *
     * @return list<array{month: string, revenue: float, profit: float}>
     */
    public function monthly(int $months = 12): array
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths($months - 1);
        $sales = $this->sales($start, CarbonImmutable::now());
        $byMonth = $sales->groupBy(fn (Sale $sale) => $sale->sold_at->format('Y-m'));

        $rows = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->addMonths($i);
            $totals = $this->totals($byMonth->get($month->format('Y-m'), collect()));
            $rows[] = [
                'month' => $month->format('M Y'),
                'revenue' => $totals['revenue']->getAmount()->toFloat(),
                'profit' => $totals['profit']->getAmount()->toFloat(),
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, string>  $only
     * @return Collection<int, Sale>
     */
    private function sales(CarbonImmutable $from, CarbonImmutable $to, array $only = []): Collection
    {
        return Sale::query()
            ->completed()
            ->whereBetween('sold_at', [$from->startOfDay(), $to->endOfDay()])
            ->with(['stockUnit.product.category', 'stockUnit.condition', 'location'])
            ->get()
            ->toBase()
            ->filter(fn (Sale $sale) => self::matches($sale, $only))
            ->values();
    }

    /**
     * Whether a sale is in every one of the given groups.
     *
     * @param  array<string, string>  $only
     */
    public static function matches(Sale $sale, array $only): bool
    {
        foreach ($only as $by => $label) {
            if (! in_array($by, ['category', 'condition', 'location'], true) || self::labelFor($sale, $by) !== $label) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  Collection<int, Sale>  $sales
     * @return array{revenue: Money, cost: Money, profit: Money, marginPercent: float|null}
     */
    private function totals(Collection $sales): array
    {
        $revenue = $this->currency->zero();
        $cost = $this->currency->zero();

        foreach ($sales as $sale) {
            $revenue = $revenue->plus($this->figures->revenue($sale));
            $cost = $cost->plus($this->figures->cost($sale));
        }

        $profit = $revenue->minus($cost);

        return [
            'revenue' => $revenue,
            'cost' => $cost,
            'profit' => $profit,
            'marginPercent' => $revenue->isZero()
                ? null
                : $profit->getAmount()->dividedBy($revenue->getAmount(), 4, RoundingMode::HalfUp)->multipliedBy(100)->toFloat(),
        ];
    }
}
