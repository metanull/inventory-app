<?php

namespace App\Support\Filament;

/**
 * Permissions that must not be deleted via the UI.
 *
 * This list covers every permission referenced by App\Policies\* classes
 * plus the non-negotiable system permissions.
 */
final class CriticalPermissions
{
    /**
     * @var string[]
     */
    public const NAMES = [
        'access-admin-panel',
        'view data',
        'create data',
        'update data',
        'delete data',
        'manage users',
        'assign roles',
        'view user management',
        'manage roles',
        'view role management',
        'manage settings',
        'manage-reference-data',
    ];
}
