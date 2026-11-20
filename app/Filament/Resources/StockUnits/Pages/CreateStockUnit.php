<?php

namespace App\Filament\Resources\StockUnits\Pages;

use App\Filament\Resources\StockUnits\StockUnitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockUnit extends CreateRecord
{
    protected static string $resource = StockUnitResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [...$data, 'created_by' => auth()->id()];
    }
}
