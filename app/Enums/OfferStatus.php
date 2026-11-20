<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OfferStatus: string implements HasColor, HasLabel
{
    case Submitted = 'submitted';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Withdrawn = 'withdrawn';

    public function getLabel(): string
    {
        return match ($this) {
            self::Submitted => 'Waiting for review',
            self::Accepted => 'Bought',
            self::Declined => 'Declined',
            self::Withdrawn => 'Withdrawn',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Submitted => 'info',
            self::Accepted => 'success',
            self::Declined => 'danger',
            self::Withdrawn => 'gray',
        };
    }
}
