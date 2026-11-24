<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ReportPeriod;
use App\Services\Reports\SalesReport;
use App\Support\ReportGlossary;
use Brick\Money\Money;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use NumberFormatter;

class SalesOverview extends StatsOverviewWidget
{
    use AuthorisesReports, InteractsWithPageFilters;

    /** Loaded with the page, so the dashboard tour can point at it. */
    protected static bool $isLazy = false;

    /** Figures change when the dates change, not by the second; refreshing every few seconds also disturbed the page tours. */
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    /**
     * Two across until each card has room for figures like GH₵1,234,567, then four.
     *
     * @return array<string, int>
     */
    protected function getColumns(): array
    {
        return ['@lg' => 2, '@4xl' => 4, '!@md' => 2, '!@xl' => 4];
    }

    protected function getStats(): array
    {
        $report = app(SalesReport::class);
        [$from, $to] = ReportPeriod::from($this->pageFilters);
        $summary = $report->summary($from, $to);
        $stock = $report->stockAge();

        $glossary = app(ReportGlossary::class);
        $margin = $summary['marginPercent'];
        $discount = $summary['averageDiscount'];

        return [
            Stat::make($glossary->label('Sales', 'dashboard:sales'), self::money($summary['revenue']))
                ->description($from->format('j M').' – '.$to->format('j M Y')),
            Stat::make($glossary->label('Gross profit', 'dashboard:gross-profit'), self::money($summary['profit']))
                ->description($margin === null ? 'No sales yet' : number_format($margin, 1).'% margin')
                ->color($summary['profit']->isNegative() ? 'danger' : 'success'),
            Stat::make($glossary->label('Items sold', 'dashboard:items-sold'), (string) $summary['units'])
                ->description($discount === null ? 'No sales yet' : number_format($discount, 1).'% average discount from listed'),
            Stat::make($glossary->label('In store', 'dashboard:in-store'), (string) $stock['units'])
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
