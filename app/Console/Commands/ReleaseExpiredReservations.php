<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reservations:release')]
#[Description('Put back on sale items not paid for in time, and refund reservations not collected in time')]
class ReleaseExpiredReservations extends Command
{
    public function handle(ReservationService $reservations): int
    {
        $released = $reservations->releaseExpired();

        $this->info("Released {$released} ".str('reservation')->plural($released).'.');

        return self::SUCCESS;
    }
}
