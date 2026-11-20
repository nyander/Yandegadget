<?php

namespace App\Filament\Resources\Conditions;

use App\Filament\Resources\Conditions\Pages\ManageConditions;
use App\Filament\Support\MoneyInput;
use App\Models\Condition;
use BackedEnum;
use Brick\Money\Money;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ConditionResource extends Resource
{
    protected static ?string $model = Condition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'grade';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('grade')
                    ->required()
                    ->maxLength(8)
                    ->unique(ignoreRecord: true)
                    ->helperText('Short label shown to customers, e.g. A, B, C.'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('deposit_currency')
                    ->label('Deposit currency')
                    ->options(['GBP' => 'GBP (£)', 'GHS' => 'GHS (GH₵)'])
                    ->default('GBP')
                    ->required()
                    ->live(),
                MoneyInput::make('deposit', fn (Get $get): string => (string) ($get('deposit_currency') ?: 'GBP'))
                    ->label('Request deposit')
                    ->required()
                    ->helperText('What a customer pays to request an item in this condition.'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('grade')->weight('bold'),
                TextColumn::make('name'),
                TextColumn::make('deposit')
                    ->formatStateUsing(fn (Money $state) => $state->formatToLocale('en_GB')),
                TextColumn::make('stock_units_count')->counts('stockUnits')->label('Units'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (Condition $record) => $record->stockUnits()->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageConditions::route('/'),
        ];
    }
}
