<?php

namespace App\Filament\Widgets;

use App\Enums\Permission;
use App\Models\User;

trait AuthorisesReports
{
    public static function canView(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return (bool) $user?->can(Permission::ViewReports->value);
    }
}
