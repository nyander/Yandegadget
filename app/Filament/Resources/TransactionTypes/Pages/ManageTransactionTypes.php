<?php

namespace App\Filament\Resources\TransactionTypes\Pages;

use App\Filament\Resources\TransactionTypes\TransactionTypeResource;
use App\Models\TransactionType;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTransactionTypes extends ManageRecords
{
    protected static string $resource = TransactionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New type')
                ->mutateDataUsing(fn (array $data) => [...$data, 'sort_order' => (int) TransactionType::max('sort_order') + 1]),
        ];
    }
}
