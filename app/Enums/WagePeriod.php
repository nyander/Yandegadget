<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The unit a staff wage is quoted in. 2020 assumed every wage was daily.
 */
enum WagePeriod: string implements HasLabel
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public function getLabel(): string
    {
        return match ($this) {
            self::Daily => 'Per day',
            self::Weekly => 'Per week',
            self::Monthly => 'Per month',
        };
    }
}
