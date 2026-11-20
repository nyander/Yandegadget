<?php

namespace App\Exceptions;

use App\Enums\StockStatus;
use DomainException;

class InvalidStockTransition extends DomainException
{
    public static function between(StockStatus $from, StockStatus $to): self
    {
        return new self("A stock unit can't move from [{$from->value}] to [{$to->value}].");
    }
}
