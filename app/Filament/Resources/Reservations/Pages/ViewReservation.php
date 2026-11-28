<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Reservation;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\View\View;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    public function getTitle(): string
    {
        /** @var Reservation $reservation */
        $reservation = $this->getRecord();

        return "{$reservation->reference} · {$reservation->stockUnit->product->name}";
    }

    public function getFooter(): ?View
    {
        return view('filament.live-refresh', ['every' => '10s']);
    }

    protected function getHeaderActions(): array
    {
        return [
            ReservationResource::sellAction()->button(),
            ReservationResource::cancelAction()->button()->outlined(),
        ];
    }
}
