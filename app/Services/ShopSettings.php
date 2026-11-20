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

    public const CANCELLATION_FEE = 'request_cancellation_fee_percent';

    public const COLLECTION_DAYS = 'request_collection_days';

    /** Getting-started steps the owner ticked off by hand. */
    public const ONBOARDING_DONE = 'onboarding_done';

    public const ONBOARDING_DISMISSED = 'onboarding_dismissed';

    /** @var array<string, mixed> */
    private const DEFAULTS = [
        // Lowest price staff can accept, as a percentage of the listed price (70% in 2020).
        self::NEGOTIATION_FLOOR => 70,
        // Share of the deposit kept when a customer cancels after the shop has found the item.
        self::CANCELLATION_FEE => 25,
        // Days a found item stays reserved in store before it goes back on sale.
        self::COLLECTION_DAYS => 14,
        self::ONBOARDING_DONE => [],
        self::ONBOARDING_DISMISSED => false,
    ];

    public function negotiationFloorPercent(): int
    {
        return (int) $this->get(self::NEGOTIATION_FLOOR);
    }

    public function cancellationFeePercent(): int
    {
        return (int) $this->get(self::CANCELLATION_FEE);
    }

    public function collectionDays(): int
    {
        return (int) $this->get(self::COLLECTION_DAYS);
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
