<?php

namespace App\Filament\Resources\LedgerEntries\Pages;

use App\Filament\Resources\LedgerEntries\LedgerEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLedgerEntries extends ManageRecords
{
    protected static string $resource = LedgerEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New entry')
                ->mutateDataUsing(fn (array $data) => [...$data, 'recorded_by' => auth()->id()]),
        ];
    }
}
