<?php

namespace App\Filament\Resources\StockUnits\Schemas;

use App\Enums\StockStatus;
use App\Filament\Support\MoneyInput;
use App\Models\Location;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class StockUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        $listCurrency = (string) config('shop.list_currency');

        return $schema
            ->columns(2)
            ->components([
                Section::make('Item')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->hidden(fn ($livewire) => $livewire instanceof RelationManager)
                            ->columnSpanFull(),
                        Select::make('condition_id')
                            ->relationship('condition', 'name', fn (Builder $query) => $query->orderBy('sort_order'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->grade} – {$record->name}")
                            ->required()
                            ->preload(),
                        TextInput::make('serial_number')
                            ->label('Serial / IMEI')
                            ->maxLength(255),
                        Textarea::make('condition_notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Buying and price')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('purchased_at')
                            ->label('Bought on')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
                        MoneyInput::withCedis(MoneyInput::make('cost', $listCurrency)
                            ->label('Cost')
                            ->required(), $listCurrency),
                        MoneyInput::withCedis(MoneyInput::make('price', $listCurrency)
                            ->label('List price')
                            ->required(), $listCurrency, 'Customers see '),
                    ]),
                Section::make('Where it is')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('location_id')
                            ->relationship('location', 'name')
                            ->default(fn () => Location::where('code', 'ACC-LEGON')->value('id'))
                            ->required(),
                        Select::make('status')
                            ->label('Starting status')
                            ->options([
                                StockStatus::Purchased->value => StockStatus::Purchased->getLabel(),
                                StockStatus::InTransit->value => StockStatus::InTransit->getLabel(),
                                StockStatus::Received->value => StockStatus::Received->getLabel(),
                            ])
                            ->default(StockStatus::Purchased->value)
                            ->required()
                            ->helperText('Use "In store" for stock already in Accra.')
                            ->visibleOn('create'),
                    ]),
                Section::make('Photos of this item')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('photos')
                            ->collection('photos')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->maxSize(8192)
                            ->maxFiles(8)
                            ->helperText('Optional close-ups of marks or wear.'),
                    ]),
            ]);
    }
}
