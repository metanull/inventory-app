<?php

namespace Tests;

use App\Enums\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The RefreshDatabase trait will handle migrations and database state.

        // Ensure all permissions exist for testing
        $this->ensurePermissionsExist();
    }

    protected function tearDown(): void
    {
        // The RefreshDatabase trait will handle database rollbacks.

        parent::tearDown();
    }

    /**
     * Ensure all application permissions exist in the database.
     * This is needed for permission middleware to work in tests.
     *
     * Note: This method is idempotent and safe to call multiple times.
     * It will not create duplicate permissions.
     */
    protected function ensurePermissionsExist(): void
    {
        foreach (Permission::cases() as $permission) {
            // Check if permission already exists to avoid exceptions
            $exists = \Spatie\Permission\Models\Permission::where('name', $permission->value)
                ->where('guard_name', 'web')
                ->exists();

            if (! $exists) {
                try {
                    \Spatie\Permission\Models\Permission::create([
                        'name' => $permission->value,
                        'guard_name' => 'web',
                    ]);
                } catch (\Spatie\Permission\Exceptions\PermissionAlreadyExists $e) {
                    // Permission was created by another test running in parallel, this is fine
                } catch (\Exception $e) {
                    // Unexpected exception, rethrow
                    throw $e;
                }
            }
        }
    }

    /**
     * Override actingAs to automatically grant data permissions to test users.
     * This maintains backward compatibility with existing tests while adding permission security.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string|null  $guard
     * @return $this
     */
    public function actingAs($user, $guard = null)
    {
        parent::actingAs($user, $guard);

        // Automatically grant all data operation permissions to test users
        // This prevents breaking existing tests while enforcing permission checks
        if ($user instanceof \App\Models\User) {
            $user->givePermissionTo([
                Permission::VIEW_DATA->value,
                Permission::CREATE_DATA->value,
                Permission::UPDATE_DATA->value,
                Permission::DELETE_DATA->value,
            ]);

            // Clear permission cache to ensure changes take effect
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        return $this;
    }
}
