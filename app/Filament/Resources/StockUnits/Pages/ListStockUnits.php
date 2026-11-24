<?php

namespace App\Filament\Resources\StockUnits\Pages;

use App\Filament\Resources\StockUnits\Actions\AddSeveralUnitsAction;
use App\Filament\Resources\StockUnits\StockUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStockUnits extends ListRecords
{
    protected static string $resource = StockUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            AddSeveralUnitsAction::make(),
            CreateAction::make(),
        ];
    }
}
