<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ShipmentStatus: string implements HasColor, HasLabel
{
    /** Being put together in the UK. Items can be added and removed. */
    case Draft = 'draft';

    /** Handed to the carrier. Staff in Ghana check items in as they arrive. */
    case InTransit = 'in_transit';

    /** Every item has been checked in. */
    case Received = 'received';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InTransit => 'In transit',
            self::Received => 'Received',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::InTransit => 'info',
            self::Received => 'success',
        };
    }
}
