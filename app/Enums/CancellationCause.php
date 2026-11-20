<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Why a customer request ended without a sale. It decides the refund
 * (see App\Services\RefundPolicy).
 */
enum CancellationCause: string implements HasLabel
{
    /** The customer changed their mind. */
    case Customer = 'customer';

    /** The shop couldn't supply it, or the item wasn't as described. Always a full refund. */
    case Shop = 'shop';

    /** The item was ready but not collected in time. Treated like the customer cancelling. */
    case NotCollected = 'not_collected';

    public function getLabel(): string
    {
        return match ($this) {
            self::Customer => 'Customer changed their mind',
            self::Shop => 'Shop couldn\'t supply it',
            self::NotCollected => 'Not collected in time',
        };
    }
}
