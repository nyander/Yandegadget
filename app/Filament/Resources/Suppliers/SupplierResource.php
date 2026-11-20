<?php

namespace App\Filament\Resources\Suppliers;

use App\Enums\Role;
use App\Filament\Resources\Suppliers\Pages\ManageSuppliers;
use App\Models\Supplier;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|UnitEnum|null $navigationGroup = 'Buying';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('contact_name')->maxLength(255),
                TextInput::make('email')->email()->maxLength(255),
                TextInput::make('phone')->tel()->maxLength(32),
                Textarea::make('address')->columnSpanFull(),
                Select::make('country_code')
                    ->label('Country')
                    ->options(['GB' => 'United Kingdom', 'GH' => 'Ghana'])
                    ->default('GB')
                    ->required(),
                Select::make('user_id')
                    ->label('Supplier portal login')
                    ->relationship('user', 'email', fn (Builder $query) => $query->whereHas('roles', fn (Builder $roles) => $roles->where('name', Role::Supplier->value)))
                    ->searchable()
                    ->preload()
                    ->unique(ignoreRecord: true)
                    ->helperText('Users with the supplier role. Optional.'),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('contact_name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('user.email')->label('Portal login')->placeholder('None'),
                TextColumn::make('stock_units_count')->counts('stockUnits')->label('Units bought'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSuppliers::route('/'),
        ];
    }
}
