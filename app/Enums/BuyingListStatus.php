<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BuyingListStatus: string implements HasColor, HasLabel
{
    case ToBuy = 'to_buy';
    case Bought = 'bought';
    case Dropped = 'dropped';

    public function getLabel(): string
    {
        return match ($this) {
            self::ToBuy => 'To buy',
            self::Bought => 'Bought',
            self::Dropped => 'Not needed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ToBuy => 'warning',
            self::Bought => 'success',
            self::Dropped => 'gray',
        };
    }
}
