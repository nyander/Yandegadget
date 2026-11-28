<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Resources\Pages\ListRecords;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    public function getSubheading(): string
    {
        return 'Items customers have reserved and paid for online. Keep them aside; when they come in, sell to them and the online payment comes off the price. Not collected in time? They\'re refunded in full automatically.';
    }
}
