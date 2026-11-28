<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\PaymentStatus;
use App\Enums\ReservationPayment;
use App\Enums\ReservationStatus;
use App\Models\Concerns\BroadcastsChanges;
use App\Support\LiveUpdates;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

/**
 * A customer reserving an item that's in the store and paying online, in full
 * or a deposit. Change it through App\Services\ReservationService only.
 *
 * @property int $id
 * @property string $reference
 * @property int $customer_id
 * @property int $stock_unit_id
 * @property ReservationStatus $status
 * @property ReservationPayment $payment_option
 * @property Money $price
 * @property Money $due
 * @property CarbonImmutable|null $hold_expires_at
 * @property CarbonImmutable|null $paid_at
 * @property CarbonImmutable|null $collect_by
 * @property CarbonImmutable|null $collected_at
 * @property CarbonImmutable|null $cancelled_at
 * @property string|null $cancelled_by
 * @property string|null $cancel_reason
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $customer
 * @property-read StockUnit $stockUnit
 * @property-read Payment|null $latestPayment
 */
class Reservation extends Model
{
    use BroadcastsChanges;

    protected $guarded = ['*'];

    protected $attributes = [
        'status' => 'awaiting_payment',
    ];

    protected static function booted(): void
    {
        static::creating(function (Reservation $reservation) {
            $reservation->reference ??= 'TMP-'.Str::ulid();
        });

        static::created(function (Reservation $reservation) {
            if (str_starts_with($reservation->reference, 'TMP-')) {
                $reservation->reference = sprintf('RV-%05d', $reservation->id);
                $reservation->saveQuietly();
            }
        });
    }

    /** The payment that went through, if any. */
    public function paidPayment(): ?Payment
    {
        return $this->payments()
            ->whereIn('status', [PaymentStatus::Succeeded, PaymentStatus::Refunded])
            ->latest('paid_at')
            ->first();
    }

    /** What's left to pay in store. */
    public function balance(): Money
    {
        return $this->status === ReservationStatus::Reserved || $this->status === ReservationStatus::Collected
            ? $this->price->minus($this->due)
            : $this->price;
    }

    /**
     * @return MorphMany<Payment, $this>
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * @return MorphOne<Payment, $this>
     */
    public function latestPayment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable')->latestOfMany();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<StockUnit, $this>
     */
    public function stockUnit(): BelongsTo
    {
        return $this->belongsTo(StockUnit::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'payment_option' => ReservationPayment::class,
            'price' => MoneyCast::class.':price_amount,price_currency',
            'due' => MoneyCast::class.':due_amount,due_currency',
            'hold_expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'collect_by' => 'datetime',
            'collected_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function liveTopic(): string
    {
        return 'reservations';
    }

    /**
     * @return list<string>
     */
    public function liveChannels(): array
    {
        return [LiveUpdates::BACKOFFICE, LiveUpdates::STORE, LiveUpdates::userChannel($this->customer_id)];
    }
}
