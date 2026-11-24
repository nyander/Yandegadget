<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Concerns\BroadcastsChanges;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A line in the owner's ledger (rent, cash, loans, equipment). Sales, stock,
 * item costs and shipping come from their own tables, never from here.
 *
 * @property int $id
 * @property int $transaction_type_id
 * @property CarbonImmutable $occurred_on
 * @property string $description
 * @property int $amount
 * @property string $currency
 * @property Money $money
 * @property int|null $location_id
 * @property string|null $notes
 * @property int|null $recorded_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read TransactionType $transactionType
 */
#[Fillable(['transaction_type_id', 'occurred_on', 'description', 'money', 'location_id', 'notes', 'recorded_by'])]
class LedgerEntry extends Model
{
    use BroadcastsChanges;

    /**
     * @return BelongsTo<TransactionType, $this>
     */
    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class);
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
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    protected function casts(): array
    {
        return [
            'money' => MoneyCast::class.':amount,currency',
            'occurred_on' => 'date',
            'amount' => 'integer',
        ];
    }

    public function liveTopic(): string
    {
        return 'finance';
    }
}
