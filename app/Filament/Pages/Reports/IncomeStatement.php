<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Support\ReportPeriod;
use App\Reports\Statement;
use App\Services\Reports\IncomeStatementReport;
use App\Support\ReportGlossary;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class IncomeStatement extends StatementPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Income statement';

    public function mount(): void
    {
        [$from, $to] = ReportPeriod::from(null);

        $this->form->fill(['from' => $from->toDateString(), 'to' => $to->toDateString()]);
    }

    public function getSubheading(): string
    {
        return ReportGlossary::PAGES['income-statement'].' Hover over or tap a ? to see what a line means.';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(['sm' => 2, 'lg' => 4])
            ->components([
                DatePicker::make('from')->native(false)->live()->maxDate(now()),
                DatePicker::make('to')->native(false)->live()->maxDate(now()),
            ]);
    }

    public function statement(): Statement
    {
        [$from, $to] = ReportPeriod::from($this->data);

        return app(IncomeStatementReport::class)->build($from, $to);
    }
}
