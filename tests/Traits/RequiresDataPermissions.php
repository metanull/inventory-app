<?php

namespace Tests\Traits;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;

/**
 * Compatibility trait for tests requiring data operation permissions
 *
 * @deprecated This trait is deprecated. Use CreatesUsersWithPermissions instead.
 *
 * Migration guide:
 * - Replace `createAuthenticatedUserWithDataPermissions()` with `createUserWith(PermissionEnum::dataOperations())`
 * - Replace `actAsRegularUser()` with `actingAsDataUser()`
 * - Replace `use RequiresDataPermissions;` with `use CreatesUsersWithPermissions;`
 */
trait RequiresDataPermissions
{
    use CreatesUsersWithPermissions;

    /**
     * Create an authenticated user with data operation permissions
     *
     * @deprecated Use createUserWith(PermissionEnum::dataOperations()) or actingAsDataUser() instead
     */
    protected function createAuthenticatedUserWithDataPermissions(): User
    {
        return $this->createUserWith(PermissionEnum::dataOperations());
    }

    /**
     * Act as a regular user (with data operation permissions)
     *
     * @deprecated Use actingAsDataUser() instead
     */
    protected function actAsRegularUser(): void
    {
        $this->actingAsDataUser();
    }
}
