<?php

namespace App\Filament\Resources\StockUnits;

use App\Filament\Resources\StockUnits\Pages\CreateStockUnit;
use App\Filament\Resources\StockUnits\Pages\EditStockUnit;
use App\Filament\Resources\StockUnits\Pages\ListStockUnits;
use App\Filament\Resources\StockUnits\Schemas\StockUnitForm;
use App\Filament\Resources\StockUnits\Tables\StockUnitsTable;
use App\Models\StockUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StockUnitResource extends Resource
{
    protected static ?string $model = StockUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return StockUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockUnits::route('/'),
            'create' => CreateStockUnit::route('/create'),
            'edit' => EditStockUnit::route('/{record}/edit'),
        ];
    }
}
