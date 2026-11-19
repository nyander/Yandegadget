<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $grade
 * @property string $name
 * @property string|null $description
 * @property int $deposit_amount
 * @property string $deposit_currency
 * @property Money $deposit
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['grade', 'name', 'description', 'deposit', 'sort_order'])]
class Condition extends Model
{
    /**
     * @return HasMany<StockUnit, $this>
     */
    public function stockUnits(): HasMany
    {
        return $this->hasMany(StockUnit::class);
    }

    protected function casts(): array
    {
        return [
            'deposit' => MoneyCast::class.':deposit_amount,deposit_currency',
            'sort_order' => 'integer',
        ];
    }
}
