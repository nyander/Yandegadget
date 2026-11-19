<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Cash = 'cash';
    case MobileMoney = 'mobile_money';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::MobileMoney => 'Mobile money',
            self::Card => 'Card',
            self::BankTransfer => 'Bank transfer',
        };
    }

    /** Methods with a transaction reference worth recording. */
    public function takesReference(): bool
    {
        return $this !== self::Cash;
    }
}
