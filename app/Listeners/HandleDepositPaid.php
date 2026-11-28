<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Models\ProductRequest;
use App\Models\Reservation;
use App\Services\ProductRequestService;
use App\Services\ReservationService;

class HandleDepositPaid
{
    public function __construct(
        private readonly ProductRequestService $requests,
        private readonly ReservationService $reservations,
    ) {}

    public function handle(PaymentSucceeded $event): void
    {
        $payable = $event->payment->payable;

        if ($payable instanceof ProductRequest) {
            $this->requests->depositPaid($payable);
        }

        if ($payable instanceof Reservation) {
            $this->reservations->paid($payable);
        }
    }
}
