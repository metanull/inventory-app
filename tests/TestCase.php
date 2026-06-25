<?php

namespace Tests;

use App\Enums\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions only for test classes that opted into a real database
        // via the RefreshDatabase trait. Tests that do not touch the DB (HTTP
        // response tests, pure-service tests, etc.) should not declare
        // RefreshDatabase and will skip this step entirely.
        if (in_array(RefreshDatabase::class, class_uses_recursive(static::class))) {
            $this->ensurePermissionsExist();
        }
    }

    /**
     * Ensure all application permissions exist in the database.
     * Called automatically when the concrete test class uses RefreshDatabase.
     */
    protected function ensurePermissionsExist(): void
    {
        foreach (Permission::cases() as $permission) {
            $exists = \Spatie\Permission\Models\Permission::where('name', $permission->value)
                ->where('guard_name', 'web')
                ->exists();

            if (! $exists) {
                try {
                    \Spatie\Permission\Models\Permission::create([
                        'name' => $permission->value,
                        'guard_name' => 'web',
                    ]);
                } catch (PermissionAlreadyExists $e) {
                    // Created by a parallel worker between the exists() check and create()
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }
    }
}
