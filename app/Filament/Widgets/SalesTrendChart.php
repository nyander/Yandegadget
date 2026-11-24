<?php

namespace App\Filament\Widgets;

use App\Services\Reports\SalesReport;
use App\Support\ReportGlossary;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class SalesTrendChart extends ChartWidget
{
    use AuthorisesReports;

    /** Loaded with the page, so the dashboard tour can point at it. */
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    public function getHeading(): string|Htmlable|null
    {
        return app(ReportGlossary::class)->label('Last 12 months', 'dashboard:trend');
    }

    protected ?string $description = 'Sales and gross profit per month, in cedis';

    protected function getData(): array
    {
        $months = app(SalesReport::class)->monthly();

        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => array_column($months, 'revenue'),
                    'backgroundColor' => '#059669',
                    'borderColor' => '#059669',
                ],
                [
                    'label' => 'Gross profit',
                    'data' => array_column($months, 'profit'),
                    'backgroundColor' => '#d97706',
                    'borderColor' => '#d97706',
                ],
            ],
            'labels' => array_column($months, 'month'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
