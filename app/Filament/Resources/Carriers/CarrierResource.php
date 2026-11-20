<?php

namespace App\Filament\Resources\Carriers;

use App\Filament\Resources\Carriers\Pages\ManageCarriers;
use App\Models\Carrier;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CarrierResource extends Resource
{
    protected static ?string $model = Carrier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeEuropeAfrica;

    protected static string|UnitEnum|null $navigationGroup = 'Shipping';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('contact_name')->maxLength(255),
                TextInput::make('email')->email()->maxLength(255),
                TextInput::make('phone')->tel()->maxLength(32),
                Textarea::make('address')->columnSpanFull(),
                TextInput::make('postcode')->maxLength(16),
                TextInput::make('tracking_url')
                    ->label('Tracking page')
                    ->url()
                    ->maxLength(255),
                Textarea::make('notes')->columnSpanFull(),
                Toggle::make('is_active')->label('Available for new shipments')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('email'),
                TextColumn::make('shipments_count')->counts('shipments')->label('Shipments'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (Carrier $record) => $record->shipments()->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCarriers::route('/'),
        ];
    }
}
