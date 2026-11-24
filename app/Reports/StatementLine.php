<?php

namespace App\Reports;

use Brick\Money\Money;

final readonly class StatementLine
{
    public function __construct(
        public string $label,
        public Money $amount,
        public ?string $hint = null,
    ) {}

    public function isNegative(): bool
    {
        return $this->amount->isNegative();
    }

    /**
     * Accounting style: 1,234.50, with negatives in brackets.
     */
    public function formatted(): string
    {
        return self::format($this->amount);
    }

    /** Negatives in brackets. $wholeUnits rounds to whole cedis for compact dashboard tables. */
    public static function format(Money $amount, bool $wholeUnits = false): string
    {
        $decimals = $wholeUnits ? 0 : $amount->getCurrency()->getDefaultFractionDigits();
        $number = number_format($amount->abs()->getAmount()->toFloat(), $decimals);

        return $amount->isNegative() ? "({$number})" : $number;
    }
}
