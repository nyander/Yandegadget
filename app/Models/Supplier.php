<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A UK seller the shop buys from. Optionally linked to a supplier-portal login.
 *
 * @property int $id
 * @property string $name
 * @property string|null $contact_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property string $country_code
 * @property int|null $user_id
 * @property string|null $notes
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'contact_name', 'email', 'phone', 'address', 'country_code', 'user_id', 'notes'])]
class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<StockUnit, $this>
     */
    public function stockUnits(): HasMany
    {
        return $this->hasMany(StockUnit::class);
    }
}
