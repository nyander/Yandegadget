<?php

namespace App\Filament\Resources\StaffWages\Pages;

use App\Filament\Resources\StaffWages\StaffWageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStaffWages extends ManageRecords
{
    protected static string $resource = StaffWageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add a wage'),
        ];
    }
}
