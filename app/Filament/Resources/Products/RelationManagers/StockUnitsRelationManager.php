<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Resources\StockUnits\Schemas\StockUnitForm;
use App\Filament\Resources\StockUnits\Tables\StockUnitsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StockUnitsRelationManager extends RelationManager
{
    /** Loaded with the page, so the page tour can point at it. */
    protected static bool $isLazy = false;

    protected static string $relationship = 'stockUnits';

    protected static ?string $title = 'Stock units';

    public function form(Schema $schema): Schema
    {
        return StockUnitForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return StockUnitsTable::configure($table)
            ->recordTitleAttribute('reference')
            ->headerActions([
                CreateAction::make()
                    ->label('Add unit')
                    ->mutateDataUsing(fn (array $data): array => [...$data, 'created_by' => auth()->id()]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
