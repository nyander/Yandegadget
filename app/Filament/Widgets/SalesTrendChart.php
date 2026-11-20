<?php

namespace App\Filament\Widgets;

use App\Services\Reports\SalesReport;
use Filament\Widgets\ChartWidget;

class SalesTrendChart extends ChartWidget
{
    use AuthorisesReports;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Last 12 months';

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
