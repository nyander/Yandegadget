<?php

namespace App\Filament\Resources\ExchangeRates;

use App\Filament\Resources\ExchangeRates\Pages\ManageExchangeRates;
use App\Models\ExchangeRate;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ExchangeRateResource extends Resource
{
    protected static ?string $model = ExchangeRate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyPound;

    protected static ?string $navigationLabel = 'Exchange rates';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('base_currency')
                    ->options(['GBP' => 'GBP'])
                    ->default('GBP')
                    ->required(),
                Select::make('quote_currency')
                    ->options(['GHS' => 'GHS'])
                    ->default('GHS')
                    ->required(),
                TextInput::make('rate')
                    ->label('1 GBP equals (GHS)')
                    ->numeric()
                    ->minValue(0.000001)
                    ->step('0.000001')
                    ->required(),
                DateTimePicker::make('effective_at')
                    ->label('Takes effect')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('effective_at', 'desc')
            ->columns([
                TextColumn::make('pair')
                    ->state(fn (ExchangeRate $record) => "{$record->base_currency} → {$record->quote_currency}"),
                TextColumn::make('rate'),
                TextColumn::make('effective_at')->dateTime()->sortable(),
                TextColumn::make('setBy.name')->label('Set by')->placeholder('System'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageExchangeRates::route('/'),
        ];
    }
}
