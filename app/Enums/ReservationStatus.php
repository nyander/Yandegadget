<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * An online reservation: the item is held while the customer pays, then kept
 * for them to collect, then sold to them in store (or cancelled and refunded).
 */
enum ReservationStatus: string implements HasColor, HasLabel
{
    case AwaitingPayment = 'awaiting_payment';
    case Reserved = 'reserved';
    case Collected = 'collected';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::AwaitingPayment => 'Waiting for payment',
            self::Reserved => 'Paid, ready to collect',
            self::Collected => 'Collected',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::AwaitingPayment => 'gray',
            self::Reserved => 'success',
            self::Collected => 'primary',
            self::Cancelled => 'danger',
        };
    }

    /** Still holding the item. */
    public function holdsItem(): bool
    {
        return in_array($this, [self::AwaitingPayment, self::Reserved], true);
    }
}
