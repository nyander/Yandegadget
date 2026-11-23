<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ReportPeriod;
use App\Services\Reports\SalesReport;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class SalesBreakdown extends Widget
{
    use AuthorisesReports, InteractsWithPageFilters;

    /** Loaded with the page, so the dashboard tour can point at it. */
    protected static bool $isLazy = false;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.sales-breakdown';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $report = app(SalesReport::class);
        [$from, $to] = ReportPeriod::from($this->pageFilters);

        return [
            'tables' => [
                'By category' => $report->breakdown($from, $to, 'category'),
                'By condition' => $report->breakdown($from, $to, 'condition'),
                'By store' => $report->breakdown($from, $to, 'location'),
            ],
            'period' => $from->format('j M').' – '.$to->format('j M Y'),
        ];
    }
}
