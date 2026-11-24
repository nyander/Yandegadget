<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One change to a shop setting, written by App\Services\ShopSettings::set().
 *
 * @property int $id
 * @property string $key
 * @property mixed $old_value
 * @property mixed $new_value
 * @property int|null $user_id
 * @property CarbonImmutable $created_at
 * @property-read User|null $user
 */
#[Fillable(['key', 'old_value', 'new_value', 'user_id'])]
class SettingChange extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['old_value' => 'json', 'new_value' => 'json', 'created_at' => 'immutable_datetime'];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
