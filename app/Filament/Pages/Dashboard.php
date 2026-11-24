<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\GettingStartedChecklist;
use App\Filament\Widgets\SalesBreakdown;
use App\Filament\Widgets\SalesOverview;
use App\Filament\Widgets\SalesTrendChart;
use App\Filament\Widgets\StockAge;
use App\Support\ReportGlossary;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * The admin home: sales, margin and stock for a chosen date range.
 */
class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Dashboard';

    public function getSubheading(): string
    {
        return ReportGlossary::PAGES['dashboard'].' Hover over or tap a ? to see what a figure means.';
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    DatePicker::make('from')
                        ->native(false)
                        ->maxDate(now())
                        ->placeholder(today()->startOfMonth()->format('M j, Y')),
                    DatePicker::make('to')
                        ->native(false)
                        ->maxDate(now())
                        ->placeholder(today()->format('M j, Y')),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->extraAttributes(['data-tour' => 'dates']),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            GettingStartedChecklist::class,
            SalesOverview::class,
            SalesTrendChart::class,
            StockAge::class,
            SalesBreakdown::class,
        ];
    }

    public function getColumns(): int|array
    {
        return ['md' => 2];
    }
}
