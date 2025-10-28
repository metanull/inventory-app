<?php

namespace Tests\Traits;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;

/**
 * Standardized user creation for tests
 *
 * This trait provides consistent methods to create and authenticate users
 * with specific permissions.
 *
 * Usage:
 * - Use actingAsVisitor(), actingAsDataUser(), actingAsUserManager(), actingAsAdmin() for authenticated users
 * - Use createUserWith([...permissions]) for custom permission sets
 * - Use actingAsUnprivileged() for users with no permissions
 */
trait CreatesUsersWithPermissions
{
    /**
     * Create a user with specific permissions
     *
     * @param  array  $permissions  Array of permission names/values
     * @param  bool  $verified  Whether the user's email is verified (default: true)
     */
    protected function createUserWith(array $permissions, bool $verified = true): User
    {
        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Reset cached permissions to ensure they're available
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $user = User::factory()->create([
            'email_verified_at' => $verified ? now() : null,
        ]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    /**
     * Create and authenticate as a visitor (read-only) user
     */
    protected function actingAsVisitor(): self
    {
        $user = $this->createUserWith([PermissionEnum::VIEW_DATA->value]);
        $this->actingAs($user);

        return $this;
    }

    /**
     * Create and authenticate as a data user (CRUD operations)
     */
    protected function actingAsDataUser(): self
    {
        $user = $this->createUserWith(PermissionEnum::dataOperations());
        $this->actingAs($user);

        return $this;
    }

    /**
     * Create and authenticate as a user manager
     */
    protected function actingAsUserManager(): self
    {
        $user = $this->createUserWith(PermissionEnum::userManagement());
        $this->actingAs($user);

        return $this;
    }

    /**
     * Create and authenticate as an admin user
     */
    protected function actingAsAdmin(): self
    {
        $user = $this->createUserWith(PermissionEnum::administrative());
        $this->actingAs($user);

        return $this;
    }

    /**
     * Create and authenticate as an unprivileged user (no permissions)
     */
    protected function actingAsUnprivileged(): self
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        return $this;
    }
}
