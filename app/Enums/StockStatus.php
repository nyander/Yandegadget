<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Lifecycle of one physical item, from purchase in the UK to sale in Ghana.
 * Replaces the 2020 `received` and `sold` booleans.
 */
enum StockStatus: string implements HasColor, HasLabel
{
    case Purchased = 'purchased';
    case InTransit = 'in_transit';
    case Received = 'received';
    case Reserved = 'reserved';
    case Sold = 'sold';
    case Returned = 'returned';
    case WrittenOff = 'written_off';

    public function getLabel(): string
    {
        return match ($this) {
            self::Purchased => 'Purchased (UK)',
            self::InTransit => 'In transit',
            self::Received => 'In store',
            self::Reserved => 'Reserved',
            self::Sold => 'Sold',
            self::Returned => 'Returned',
            self::WrittenOff => 'Written off',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Purchased => 'gray',
            self::InTransit => 'info',
            self::Received => 'success',
            self::Reserved => 'warning',
            self::Sold => 'primary',
            self::Returned => 'danger',
            self::WrittenOff => 'danger',
        };
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Purchased => [self::InTransit, self::WrittenOff],
            self::InTransit => [self::Received, self::WrittenOff],
            self::Received => [self::Reserved, self::Sold, self::WrittenOff],
            self::Reserved => [self::Received, self::Sold],
            self::Sold => [self::Returned],
            self::Returned => [self::Received, self::WrittenOff],
            self::WrittenOff => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    /**
     * Customers only see stock that has arrived in Ghana and hasn't been sold.
     */
    public function isVisibleToCustomers(): bool
    {
        return in_array($this, [self::Received, self::Reserved], true);
    }

    /**
     * @return list<string>
     */
    public static function customerVisibleValues(): array
    {
        return array_values(array_map(
            fn (self $status) => $status->value,
            array_filter(self::cases(), fn (self $status) => $status->isVisibleToCustomers()),
        ));
    }
}
