<?php

namespace App\Policies;

use App\Enums\Permission;

class TransactionTypePolicy extends CataloguePolicy
{
    protected Permission $permission = Permission::ManageFinances;
}
