<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\BuyingListStatus;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Something to look for on the next UK buying trip.
 *
 * @property int $id
 * @property string $description
 * @property int|null $category_id
 * @property int|null $condition_id
 * @property int $quantity
 * @property Money|null $target_cost
 * @property BuyingListStatus $status
 * @property string|null $source
 * @property string|null $notes
 * @property int|null $added_by
 * @property CarbonImmutable|null $bought_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Category|null $category
 * @property-read Condition|null $condition
 * @property-read User|null $adder
 */
#[Fillable(['description', 'category_id', 'condition_id', 'quantity', 'target_cost', 'status', 'source', 'notes', 'added_by', 'bought_at'])]
class BuyingListItem extends Model
{
    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'to_buy',
        'quantity' => 1,
        'target_cost_currency' => 'GBP',
    ];

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Condition, $this> */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
    }

    /** @return BelongsTo<User, $this> */
    public function adder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    protected function casts(): array
    {
        return [
            'target_cost' => MoneyCast::class.':target_cost_amount,target_cost_currency',
            'status' => BuyingListStatus::class,
            'quantity' => 'integer',
            'bought_at' => 'immutable_datetime',
        ];
    }
}
