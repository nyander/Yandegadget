<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** How much the customer pays online: everything, or a deposit with the rest in store. */
enum ReservationPayment: string implements HasLabel
{
    case Full = 'full';
    case Deposit = 'deposit';

    public function getLabel(): string
    {
        return match ($this) {
            self::Full => 'Paid in full',
            self::Deposit => 'Deposit',
        };
    }
}
