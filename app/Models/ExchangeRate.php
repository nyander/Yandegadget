<?php

namespace App\Models;

use App\Models\Concerns\BroadcastsChanges;
use App\Support\LiveUpdates;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A rate in force from `effective_at` until the next one. Rates are never
 * edited, only added, so past prices can always be explained.
 *
 * @property int $id
 * @property string $base_currency
 * @property string $quote_currency
 * @property string $rate
 * @property CarbonImmutable $effective_at
 * @property int|null $set_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['base_currency', 'quote_currency', 'rate', 'effective_at', 'set_by'])]
class ExchangeRate extends Model
{
    use BroadcastsChanges;

    public static function current(string $base, string $quote): ?self
    {
        return static::asOf($base, $quote, now());
    }

    /**
     * The rate that was in force at a point in time.
     */
    public static function asOf(string $base, string $quote, DateTimeInterface $at): ?self
    {
        return static::query()
            ->where('base_currency', $base)
            ->where('quote_currency', $quote)
            ->where('effective_at', '<=', $at)
            ->latest('effective_at')
            ->latest('id')
            ->first();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:6',
            'effective_at' => 'datetime',
        ];
    }

    public function liveTopic(): string
    {
        return 'rates';
    }

    /**
     * @return list<string>
     */
    public function liveChannels(): array
    {
        return [LiveUpdates::BACKOFFICE, LiveUpdates::STORE];
    }
}
