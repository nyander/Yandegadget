<?php

namespace App\Policies;

use App\Enums\StockStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StockUnitPolicy extends CataloguePolicy
{
    /**
     * Sold units are part of the sales record and can't be deleted.
     */
    public function delete(User $user, Model $model): bool
    {
        return parent::delete($user, $model)
            && $model->getAttribute('status') !== StockStatus::Sold;
    }
}
