<?php

namespace App\Reports;

/**
 * A finished financial statement, shared by the admin page, CSV and PDF.
 */
final readonly class Statement
{
    /**
     * @param  list<StatementSection>  $sections
     * @param  list<string>  $notes
     */
    public function __construct(
        public string $title,
        public string $period,
        public string $currency,
        public array $sections,
        public array $notes = [],
    ) {}

    public function find(string $label): ?StatementLine
    {
        foreach ($this->sections as $section) {
            foreach ([...$section->lines, $section->total] as $line) {
                if ($line?->label === $label) {
                    return $line;
                }
            }
        }

        return null;
    }

    /**
     * Rows for a CSV export: section, line, amount.
     *
     * @return list<array{0: string, 1: string, 2: string}>
     */
    public function rows(): array
    {
        $rows = [];

        foreach ($this->sections as $section) {
            foreach ($section->lines as $line) {
                $rows[] = [$section->title ?? '', $line->label, (string) $line->amount->getAmount()];
            }

            if ($section->total !== null) {
                $rows[] = [$section->title ?? '', $section->total->label, (string) $section->total->amount->getAmount()];
            }
        }

        return $rows;
    }
}
