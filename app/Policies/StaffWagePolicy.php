<?php

namespace App\Policies;

use App\Enums\Permission;

class StaffWagePolicy extends CataloguePolicy
{
    protected Permission $permission = Permission::ManageFinances;
}
