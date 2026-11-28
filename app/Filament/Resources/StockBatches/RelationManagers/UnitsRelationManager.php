<?php

namespace App\Filament\Resources\StockBatches\RelationManagers;

use App\Filament\Resources\StockUnits\StockUnitResource;
use App\Filament\Resources\StockUnits\Tables\StockUnitsTable;
use App\Models\StockUnit;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

/** The items in a bulk batch, each with its own reference and status. */
class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $title = 'Items in this batch';

    public function table(Table $table): Table
    {
        return StockUnitsTable::configure($table)
            ->recordTitleAttribute('reference')
            ->recordUrl(fn (StockUnit $record) => StockUnitResource::getUrl('edit', ['record' => $record]));
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
