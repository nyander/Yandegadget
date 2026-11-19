<?php

namespace App\Models;

use App\Enums\TransactionCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property TransactionCategory $category
 * @property string|null $description
 * @property bool $derived_in_reports
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'category', 'description', 'derived_in_reports', 'sort_order'])]
class TransactionType extends Model
{
    protected function casts(): array
    {
        return [
            'category' => TransactionCategory::class,
            'derived_in_reports' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
