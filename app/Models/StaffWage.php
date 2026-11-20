<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\WagePeriod;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A wage paid at a daily, weekly or monthly rate from `starts_on` until
 * `ends_on` (or ongoing). Reports prorate it into the range being viewed.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property int|null $location_id
 * @property int $rate_amount
 * @property string $rate_currency
 * @property Money $rate
 * @property WagePeriod $period
 * @property CarbonImmutable $starts_on
 * @property CarbonImmutable|null $ends_on
 * @property string|null $notes
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['user_id', 'name', 'location_id', 'rate', 'period', 'starts_on', 'ends_on', 'notes'])]
class StaffWage extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    protected function casts(): array
    {
        return [
            'rate' => MoneyCast::class.':rate_amount,rate_currency',
            'period' => WagePeriod::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }
}
