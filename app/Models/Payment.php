<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Models\Concerns\BroadcastsChanges;
use App\Support\LiveUpdates;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An online payment taken through a gateway. Written by App\Services\Payments\PaymentService only.
 *
 * @property int $id
 * @property string $payable_type
 * @property int $payable_id
 * @property int $customer_id
 * @property string $provider
 * @property string $reference
 * @property string|null $provider_transaction_id
 * @property PaymentStatus $status
 * @property int $amount
 * @property string $currency
 * @property string|null $exchange_rate
 * @property string|null $channel
 * @property CarbonImmutable|null $paid_at
 * @property int $refunded_amount
 * @property string|null $refund_status
 * @property CarbonImmutable|null $refunded_at
 * @property array<string, mixed>|null $provider_response
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Money $money
 * @property-read Model $payable
 * @property-read User $customer
 */
class Payment extends Model
{
    use BroadcastsChanges;

    protected $guarded = ['*'];

    /**
     * @return Attribute<Money, never>
     */
    protected function money(): Attribute
    {
        return Attribute::get(fn () => Money::ofMinor($this->amount, $this->currency));
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'exchange_rate' => 'decimal:6',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'provider_response' => 'array',
        ];
    }

    public function liveTopic(): string
    {
        return 'payments';
    }

    /**
     * @return list<string>
     */
    public function liveChannels(): array
    {
        return [LiveUpdates::BACKOFFICE, LiveUpdates::userChannel($this->customer_id)];
    }
}
