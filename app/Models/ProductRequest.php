<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\CancellationCause;
use App\Enums\PaymentStatus;
use App\Enums\RequestStatus;
use App\Models\Concerns\BroadcastsChanges;
use App\Support\LiveUpdates;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

/**
 * A customer asking the shop to find an item. Change its status through
 * App\Services\ProductRequestService.
 *
 * @property int $id
 * @property string $reference
 * @property RequestStatus $status
 * @property int $customer_id
 * @property int $category_id
 * @property int $condition_id
 * @property string $description
 * @property string|null $notes
 * @property int $deposit_amount
 * @property string $deposit_currency
 * @property Money $deposit
 * @property int|null $stock_unit_id
 * @property CarbonImmutable|null $paid_at
 * @property CarbonImmutable|null $sourced_at
 * @property CarbonImmutable|null $ready_at
 * @property CarbonImmutable|null $collection_reminded_at
 * @property CarbonImmutable|null $fulfilled_at
 * @property CarbonImmutable|null $cancelled_at
 * @property string|null $cancel_reason
 * @property CancellationCause|null $cancelled_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $customer
 * @property-read Category $category
 * @property-read Condition $condition
 * @property-read StockUnit|null $stockUnit
 */
#[Fillable(['customer_id', 'category_id', 'condition_id', 'description', 'notes', 'deposit'])]
class ProductRequest extends Model
{
    use BroadcastsChanges;

    protected $attributes = [
        'status' => 'awaiting_deposit',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProductRequest $request) {
            $request->reference ??= 'TMP-'.Str::ulid();
        });

        static::created(function (ProductRequest $request) {
            if (str_starts_with($request->reference, 'TMP-')) {
                $request->reference = sprintf('RQ-%05d', $request->id);
                $request->saveQuietly();
            }
        });
    }

    /**
     * The successful deposit payment, if any.
     */
    public function paidDeposit(): ?Payment
    {
        return $this->payments()
            ->whereIn('status', [PaymentStatus::Succeeded, PaymentStatus::Refunded])
            ->latest('paid_at')
            ->first();
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
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Condition, $this>
     */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
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
            'status' => RequestStatus::class,
            'deposit' => MoneyCast::class.':deposit_amount,deposit_currency',
            'paid_at' => 'datetime',
            'sourced_at' => 'datetime',
            'ready_at' => 'datetime',
            'collection_reminded_at' => 'datetime',
            'fulfilled_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'cancelled_by' => CancellationCause::class,
        ];
    }

    public function liveTopic(): string
    {
        return 'requests';
    }

    /**
     * @return list<string>
     */
    public function liveChannels(): array
    {
        return [LiveUpdates::BACKOFFICE, LiveUpdates::userChannel($this->customer_id)];
    }
}
