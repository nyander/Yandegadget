<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Models\ProductRequest;
use App\Services\ProductRequestService;

class HandleDepositPaid
{
    public function __construct(private readonly ProductRequestService $requests) {}

    public function handle(PaymentSucceeded $event): void
    {
        $payable = $event->payment->payable;

        if ($payable instanceof ProductRequest) {
            $this->requests->depositPaid($payable);
        }
    }
}
