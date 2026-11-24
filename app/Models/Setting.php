<?php

namespace App\Models;

use App\Models\Concerns\BroadcastsChanges;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Read and write through App\Services\ShopSettings, not directly.
 *
 * @property string $key
 * @property mixed $value
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    use BroadcastsChanges;

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    public function liveTopic(): string
    {
        return 'settings';
    }
}
