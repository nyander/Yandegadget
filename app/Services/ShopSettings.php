<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Admin-editable shop settings with code defaults.
 */
class ShopSettings
{
    public const NEGOTIATION_FLOOR = 'negotiation_floor_percent';

    /** @var array<string, mixed> */
    private const DEFAULTS = [
        // Lowest price staff can accept, as a percentage of the listed price (70% in 2020).
        self::NEGOTIATION_FLOOR => 70,
    ];

    public function negotiationFloorPercent(): int
    {
        return (int) $this->get(self::NEGOTIATION_FLOOR);
    }

    public function get(string $key): mixed
    {
        $stored = Cache::rememberForever("settings.{$key}", fn () => Setting::find($key)?->value);

        return $stored ?? self::DEFAULTS[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("settings.{$key}");
    }
}
