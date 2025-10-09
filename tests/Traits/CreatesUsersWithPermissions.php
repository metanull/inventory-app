<?php

namespace Tests\Traits;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;

trait CreatesUsersWithPermissions
{
    /**
     * Create a user with specific permissions (feature-based testing)
     */
    protected function createUserWithPermissions(array $permissions): User
    {
        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Reset cached permissions to ensure they're available
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }

    /**
     * Create user with visitor (read-only) permissions
     */
    protected function createVisitorUser(): User
    {
        return $this->createUserWithPermissions([PermissionEnum::VIEW_DATA->value]);
    }

    /**
     * Create user with data management permissions
     */
    protected function createDataUser(): User
    {
        return $this->createUserWithPermissions(PermissionEnum::dataOperations());
    }

    /**
     * Create user with user management permissions
     */
    protected function createUserManagerUser(): User
    {
        return $this->createUserWithPermissions(PermissionEnum::userManagement());
    }

    /**
     * Create user with administrative permissions
     */
    protected function createAdminUser(): User
    {
        return $this->createUserWithPermissions(PermissionEnum::administrative());
    }

    /**
     * Create user with no permissions
     */
    protected function createUnprivilegedUser(): User
    {
        return User::factory()->create();
    }

    /**
     * Create user with specific feature permissions
     */
    protected function createUserWithFeatures(array $features): User
    {
        $permissions = [];
        foreach ($features as $feature) {
            if (method_exists(PermissionEnum::class, $feature)) {
                $permissions = array_merge($permissions, PermissionEnum::$feature());
            }
        }

        return $this->createUserWithPermissions($permissions);
    }
}
