<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * A customer request: deposit → the shop looks → finds an item → it's in
 * store and reserved → the customer buys it.
 */
enum RequestStatus: string implements HasColor, HasLabel
{
    case AwaitingDeposit = 'awaiting_deposit';
    case Open = 'open';
    case Sourced = 'sourced';
    case ReadyForCollection = 'ready';
    case Fulfilled = 'fulfilled';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::AwaitingDeposit => 'Awaiting deposit',
            self::Open => 'Looking for it',
            self::Sourced => 'Found, on its way',
            self::ReadyForCollection => 'Ready in store',
            self::Fulfilled => 'Collected',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::AwaitingDeposit => 'gray',
            self::Open => 'info',
            self::Sourced => 'warning',
            self::ReadyForCollection => 'success',
            self::Fulfilled => 'primary',
            self::Cancelled => 'danger',
        };
    }

    /** Statuses where the deposit has been paid and the shop owes the customer something. */
    public function isActive(): bool
    {
        return in_array($this, [self::Open, self::Sourced, self::ReadyForCollection], true);
    }
}
