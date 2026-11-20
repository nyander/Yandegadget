<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ReportPeriod;
use App\Services\Reports\SalesReport;
use Brick\Money\Money;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use NumberFormatter;

class SalesOverview extends StatsOverviewWidget
{
    use AuthorisesReports, InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $report = app(SalesReport::class);
        [$from, $to] = ReportPeriod::from($this->pageFilters);
        $summary = $report->summary($from, $to);
        $stock = $report->stockAge();

        $margin = $summary['marginPercent'];
        $discount = $summary['averageDiscount'];

        return [
            Stat::make('Sales', self::money($summary['revenue']))
                ->description($from->format('j M').' – '.$to->format('j M Y')),
            Stat::make('Gross profit', self::money($summary['profit']))
                ->description($margin === null ? 'No sales yet' : number_format($margin, 1).'% margin')
                ->color($summary['profit']->isNegative() ? 'danger' : 'success'),
            Stat::make('Items sold', (string) $summary['units'])
                ->description($discount === null ? 'No sales yet' : number_format($discount, 1).'% average discount from listed'),
            Stat::make('In store', (string) $stock['units'])
                ->description(self::money($stock['value']).' at cost'.($stock['averageDays'] === null ? '' : ', '.number_format($stock['averageDays'], 0).' days on average')),
        ];
    }

    /**
     * Whole cedis, so large figures fit on the cards.
     */
    private static function money(Money $money): string
    {
        $formatter = new NumberFormatter((string) config('shop.display_locale'), NumberFormatter::CURRENCY);
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

        return (string) $formatter->formatCurrency($money->getAmount()->toFloat(), $money->getCurrency()->getCurrencyCode());
    }
}
