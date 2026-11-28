<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Models\Concerns\BroadcastsChanges;
use App\Support\LiveUpdates;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * An in-store sale. Created only by App\Services\SaleService; never edited, only voided.
 *
 * @property int $id
 * @property string $reference
 * @property int $stock_unit_id
 * @property int $location_id
 * @property int $sold_by
 * @property int|null $customer_id
 * @property string|null $customer_name
 * @property string|null $customer_phone
 * @property int $list_price_amount
 * @property string $list_price_currency
 * @property int $listed_amount
 * @property int $floor_amount
 * @property int $price_paid_amount
 * @property int $deposit_applied_amount
 * @property int|null $product_request_id
 * @property int|null $reservation_id
 * @property string $currency
 * @property int|null $exchange_rate_id
 * @property string|null $rate
 * @property int $floor_percent
 * @property PaymentMethod $payment_method
 * @property string|null $payment_reference
 * @property CarbonImmutable $sold_at
 * @property string|null $notes
 * @property CarbonImmutable|null $voided_at
 * @property int|null $voided_by
 * @property string|null $void_reason
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Money $list_price
 * @property-read Money $listed
 * @property-read Money $floor
 * @property-read Money $price_paid
 * @property-read Money $deposit_applied
 * @property-read StockUnit $stockUnit
 * @property-read Location $location
 * @property-read User $seller
 */
class Sale extends Model
{
    use BroadcastsChanges;

    /** Sales are written by SaleService only. */
    protected $guarded = ['*'];

    protected static function booted(): void
    {
        static::creating(function (Sale $sale) {
            $sale->reference ??= 'TMP-'.Str::ulid();
        });

        static::created(function (Sale $sale) {
            if (str_starts_with($sale->reference, 'TMP-')) {
                $sale->reference = sprintf('SA-%06d', $sale->id);
                $sale->saveQuietly();
            }
        });
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    /**
     * @param  Builder<Sale>  $query
     */
    #[Scope]
    protected function completed(Builder $query): void
    {
        $query->whereNull('voided_at');
    }

    /**
     * Discount from the listed price, as a whole percentage.
     */
    public function discountPercent(): int
    {
        if ($this->listed_amount === 0) {
            return 0;
        }

        return (int) round((1 - $this->price_paid_amount / $this->listed_amount) * 100);
    }

    /**
     * @return Attribute<Money, never>
     */
    protected function listPrice(): Attribute
    {
        return Attribute::get(fn () => Money::ofMinor($this->list_price_amount, $this->list_price_currency));
    }

    /**
     * @return Attribute<Money, never>
     */
    protected function listed(): Attribute
    {
        return Attribute::get(fn () => Money::ofMinor($this->listed_amount, $this->currency));
    }

    /**
     * @return Attribute<Money, never>
     */
    protected function floor(): Attribute
    {
        return Attribute::get(fn () => Money::ofMinor($this->floor_amount, $this->currency));
    }

    /**
     * @return Attribute<Money, never>
     */
    protected function pricePaid(): Attribute
    {
        return Attribute::get(fn () => Money::ofMinor($this->price_paid_amount, $this->currency));
    }

    /**
     * @return Attribute<Money, never>
     */
    protected function depositApplied(): Attribute
    {
        return Attribute::get(fn () => Money::ofMinor($this->deposit_applied_amount, $this->currency));
    }

    /**
     * @return BelongsTo<ProductRequest, $this>
     */
    public function productRequest(): BelongsTo
    {
        return $this->belongsTo(ProductRequest::class);
    }

    /**
     * The online reservation this sale completed, if any.
     *
     * @return BelongsTo<Reservation, $this>
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * @return BelongsTo<StockUnit, $this>
     */
    public function stockUnit(): BelongsTo
    {
        return $this->belongsTo(StockUnit::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * @return BelongsTo<ExchangeRate, $this>
     */
    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
    }

    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'rate' => 'decimal:6',
            'sold_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function liveTopic(): string
    {
        return 'sales';
    }

    /**
     * @return list<string>
     */
    public function liveChannels(): array
    {
        return [LiveUpdates::BACKOFFICE, LiveUpdates::STORE];
    }
}
