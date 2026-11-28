<?php

namespace App\Filament\Resources\StockBatches\Pages;

use App\Filament\Resources\StockBatches\StockBatchResource;
use App\Filament\Resources\StockUnits\Actions\AddSeveralUnitsAction;
use Filament\Resources\Pages\ListRecords;

class ListStockBatches extends ListRecords
{
    protected static string $resource = StockBatchResource::class;

    public function getSubheading(): string
    {
        return 'Things you bought several of at once, like 50 watches. Edit a batch to change all its unsold items together. One-off and second-hand items stay under Stock units.';
    }

    protected function getHeaderActions(): array
    {
        return [
            AddSeveralUnitsAction::make()->label('Add bulk stock')->color('primary'),
        ];
    }
}
