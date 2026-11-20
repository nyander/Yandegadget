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
    public function __construct(
        private readonly ReportCurrency $currency,
        private readonly SaleFigures $figures,
    ) {}

    /**
     * @return array{units: int, revenue: Money, cost: Money, profit: Money, marginPercent: float|null, averageDiscount: float|null}
     */
    public function summary(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $sales = $this->sales($from, $to);
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
     * @param  'category'|'condition'|'location'  $by
     * @return list<array{label: string, units: int, revenue: Money, profit: Money, marginPercent: float|null}>
     */
    public function breakdown(CarbonImmutable $from, CarbonImmutable $to, string $by): array
    {
        $sales = $this->sales($from, $to);

        $groups = $sales->groupBy(fn (Sale $sale) => match ($by) {
            'category' => $sale->stockUnit->product->category->name ?? 'Uncategorised',
            'condition' => $sale->stockUnit->condition->name,
            'location' => $sale->location->name,
        });

        return array_values($groups
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
            ->sortByDesc(fn (array $row) => $row['units'])
            ->all());
    }

    /**
     * Stock in store or reserved, bucketed by days since it arrived.
     *
     * @return array{units: int, value: Money, averageDays: float|null, buckets: list<array{label: string, units: int, value: Money}>}
     */
    public function stockAge(): array
    {
        $now = CarbonImmutable::now();
        $buckets = [
            ['label' => 'Under 30 days', 'max' => 30],
            ['label' => '30–59 days', 'max' => 60],
            ['label' => '60–89 days', 'max' => 90],
            ['label' => '90 days or more', 'max' => PHP_INT_MAX],
        ];
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
     * @return Collection<int, Sale>
     */
    private function sales(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return Sale::query()
            ->completed()
            ->whereBetween('sold_at', [$from->startOfDay(), $to->endOfDay()])
            ->with(['stockUnit.product.category', 'stockUnit.condition', 'location'])
            ->get()
            ->toBase();
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
