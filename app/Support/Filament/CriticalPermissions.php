<?php

namespace App\Support\Filament;

use App\Enums\Permission;

/**
 * Permissions that must not be deleted via the UI.
 *
 * This list covers every permission defined in the Permission enum —
 * they are referenced by App\Policies\* classes and must always exist.
 */
final class CriticalPermissions
{
    /**
     * @return string[]
     */
    public static function names(): array
    {
        return array_map(fn (Permission $p): string => $p->value, Permission::cases());
    }
}
