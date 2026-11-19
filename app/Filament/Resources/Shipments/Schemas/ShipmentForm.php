<?php

namespace App\Filament\Resources\Shipments\Schemas;

use App\Enums\LocationType;
use App\Filament\Support\MoneyInput;
use App\Models\Location;
use App\Models\Shipment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        $locked = fn (?Shipment $record) => $record !== null && ! $record->isDraft();

        return $schema
            ->columns(2)
            ->components([
                Section::make('Route')
                    ->columns(2)
                    ->columnSpanFull()
                    ->disabled($locked)
                    ->schema([
                        Select::make('origin_location_id')
                            ->label('From')
                            ->relationship('origin', 'name', fn (Builder $query) => $query->where('type', LocationType::Buying))
                            ->default(fn () => Location::where('code', 'UK-BUYING')->value('id'))
                            ->required(),
                        Select::make('destination_location_id')
                            ->label('To')
                            ->relationship('destination', 'name', fn (Builder $query) => $query->where('type', LocationType::Store))
                            ->default(fn () => Location::where('code', 'ACC-LEGON')->value('id'))
                            ->required(),
                        Textarea::make('notes')->columnSpanFull(),
                    ]),
                Section::make('Dispatch details')
                    ->description('Filled in when the shipment is dispatched.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible($locked)
                    ->disabled()
                    ->schema([
                        Select::make('carrier_id')->relationship('carrier', 'name'),
                        TextInput::make('tracking_number'),
                        MoneyInput::make('cost', (string) config('shop.list_currency'))->label('Shipping cost'),
                        DatePicker::make('dispatched_on')->label('Dispatched'),
                        DatePicker::make('expected_on')->label('Expected'),
                    ]),
            ]);
    }
}
