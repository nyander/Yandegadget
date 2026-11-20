<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * What staff in Ghana found when checking an item in.
 */
enum ReceiptOutcome: string implements HasColor, HasLabel
{
    case Received = 'received';
    case Damaged = 'damaged';
    case Missing = 'missing';

    public function getLabel(): string
    {
        return match ($this) {
            self::Received => 'Arrived OK',
            self::Damaged => 'Arrived damaged',
            self::Missing => 'Missing',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Received => 'success',
            self::Damaged => 'warning',
            self::Missing => 'danger',
        };
    }

    /** Damaged items still go into the store, flagged for the admin to review. */
    public function arrived(): bool
    {
        return $this !== self::Missing;
    }

    public function isIssue(): bool
    {
        return $this !== self::Received;
    }
}
