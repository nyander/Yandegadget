<?php

namespace App\Filament\Widgets;

use App\Services\Reports\SalesReport;
use Filament\Widgets\Widget;

class StockAge extends Widget
{
    use AuthorisesReports;

    /** Loaded with the page, so the dashboard tour can point at it. */
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected string $view = 'filament.widgets.stock-age';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return ['stock' => app(SalesReport::class)->stockAge()];
    }
}
