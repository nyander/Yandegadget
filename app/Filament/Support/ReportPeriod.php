<?php

namespace App\Filament\Support;

use Carbon\CarbonImmutable;

/**
 * Reads a from/to date range out of a filter form, defaulting to this month so far.
 */
final class ReportPeriod
{
    /**
     * @param  array<string, mixed>|null  $filters
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public static function from(?array $filters): array
    {
        $today = CarbonImmutable::today();
        $from = self::date($filters['from'] ?? null) ?? $today->startOfMonth();
        $to = self::date($filters['to'] ?? null) ?? $today;

        return $from->greaterThan($to) ? [$to, $from] : [$from, $to];
    }

    public static function date(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
