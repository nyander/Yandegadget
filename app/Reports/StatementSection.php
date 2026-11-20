<?php

namespace App\Reports;

/**
 * A titled group of lines with a total, or (with no title and no lines) a
 * standalone total such as gross profit.
 */
final readonly class StatementSection
{
    /**
     * @param  list<StatementLine>  $lines
     */
    public function __construct(
        public ?string $title,
        public array $lines,
        public ?StatementLine $total = null,
    ) {}

    public static function total(StatementLine $line): self
    {
        return new self(null, [], $line);
    }

    public function isStandaloneTotal(): bool
    {
        return $this->title === null && $this->lines === [];
    }
}
