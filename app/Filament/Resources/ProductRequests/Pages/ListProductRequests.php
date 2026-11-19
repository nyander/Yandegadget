<?php

namespace App\Filament\Resources\ProductRequests\Pages;

use App\Filament\Resources\ProductRequests\ProductRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListProductRequests extends ListRecords
{
    protected static string $resource = ProductRequestResource::class;
}
