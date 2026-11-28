<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\StockStatus;
use App\Models\Concerns\BroadcastsChanges;
use App\Support\LiveUpdates;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Items of one product bought together in bulk (e.g. 50 watches at £40 each).
 * Each item is still its own stock unit, but they share their details, are
 * edited together (App\Services\StockIntake::updateBatch()), shipped together
 * and shown as one line in the shop. One-off and second-hand items have no batch.
 *
 * @property int $id
 * @property string $reference
 * @property int $product_id
 * @property int $condition_id
 * @property int|null $supplier_id
 * @property int $quantity
 * @property Money $cost
 * @property Money $price
 * @property CarbonImmutable $purchased_at
 * @property string|null $condition_notes
 * @property int|null $created_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Product $product
 * @property-read Condition $condition
 * @property-read Supplier|null $supplier
 */
#[Fillable(['product_id', 'condition_id', 'supplier_id', 'quantity', 'cost', 'price', 'purchased_at', 'condition_notes', 'created_by'])]
class StockBatch extends Model
{
    use BroadcastsChanges;

    protected static function booted(): void
    {
        static::creating(function (StockBatch $batch) {
            $batch->reference ??= 'TMP-'.Str::ulid();
        });

        static::created(function (StockBatch $batch) {
            if (str_starts_with($batch->reference, 'TMP-')) {
                $batch->reference = sprintf('BT-%04d', $batch->id);
                $batch->saveQuietly();
            }
        });
    }

    /**
     * @return HasMany<StockUnit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(StockUnit::class, 'batch_id');
    }

    /**
     * Items still to sell: not sold, returned or written off. Edits apply to these.
     *
     * @return HasMany<StockUnit, $this>
     */
    public function unsoldUnits(): HasMany
    {
        return $this->units()->whereIn('status', [StockStatus::Purchased, StockStatus::InTransit, StockStatus::Received, StockStatus::Reserved]);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * @return BelongsTo<Condition, $this>
     */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    protected function casts(): array
    {
        return [
            'cost' => MoneyCast::class.':cost_amount,cost_currency',
            'price' => MoneyCast::class.':price_amount,price_currency',
            'purchased_at' => 'immutable_date',
        ];
    }

    public function liveTopic(): string
    {
        return 'stock';
    }

    /**
     * @return list<string>
     */
    public function liveChannels(): array
    {
        return [LiveUpdates::BACKOFFICE, LiveUpdates::STORE];
    }
}
