<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Support\ReportPeriod;
use App\Reports\Statement;
use App\Services\Reports\BalanceSheetReport;
use App\Support\ReportGlossary;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BalanceSheet extends StatementPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Balance sheet';

    public function mount(): void
    {
        $this->form->fill(['as_of' => today()->toDateString()]);
    }

    public function getSubheading(): string
    {
        return ReportGlossary::PAGES['balance-sheet'].' Hover over or tap a ? to see what a line means.';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(['sm' => 2, 'lg' => 4])
            ->components([
                DatePicker::make('as_of')->label('As at')->native(false)->live()->maxDate(now()),
            ]);
    }

    public function statement(): Statement
    {
        $asOf = ReportPeriod::date($this->data['as_of'] ?? null) ?? CarbonImmutable::today();

        return app(BalanceSheetReport::class)->build($asOf);
    }
}
