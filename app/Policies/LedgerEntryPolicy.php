<?php

namespace App\Policies;

use App\Enums\Permission;

class LedgerEntryPolicy extends CataloguePolicy
{
    protected Permission $permission = Permission::ManageFinances;
}
