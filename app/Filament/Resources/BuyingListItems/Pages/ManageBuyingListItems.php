<?php

namespace App\Filament\Resources\BuyingListItems\Pages;

use App\Filament\Resources\BuyingListItems\BuyingListItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBuyingListItems extends ManageRecords
{
    protected static string $resource = BuyingListItemResource::class;

    protected ?string $subheading = 'What to look for on your next UK buying trip. Add items here, or from the dashboard: click a row in "What sold".';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add an item')
                ->mutateDataUsing(fn (array $data) => [...$data, 'added_by' => auth()->id(), 'source' => 'Added by hand']),
        ];
    }
}
