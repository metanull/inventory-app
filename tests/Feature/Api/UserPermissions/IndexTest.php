<?php

namespace Tests\Feature\Api\UserPermissions;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions if they don't exist
        foreach (Permission::cases() as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(
                ['name' => $permission->value],
                ['guard_name' => 'web']
            );
        }

        $this->user = User::factory()->create();
    }

    public function test_returns_empty_array_for_user_with_no_permissions(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('user.permissions'));

        $response->assertOk();
        $response->assertJson(['data' => []]);
    }

    public function test_returns_user_permissions(): void
    {
        $this->user->givePermissionTo([
            Permission::VIEW_DATA->value,
            Permission::CREATE_DATA->value,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('user.permissions'));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'permissions' => [
                    Permission::VIEW_DATA->value,
                    Permission::CREATE_DATA->value,
                ],
            ],
        ]);
    }

    public function test_returns_permissions_from_role(): void
    {
        $role = Role::create(['name' => 'Test Role', 'guard_name' => 'web']);
        $role->givePermissionTo([
            Permission::VIEW_DATA->value,
            Permission::MANAGE_USERS->value,
        ]);

        $this->user->assignRole($role);

        $response = $this->actingAs($this->user)
            ->getJson(route('user.permissions'));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'permissions' => [
                    Permission::VIEW_DATA->value,
                    Permission::MANAGE_USERS->value,
                ],
            ],
        ]);
    }

    public function test_returns_combined_direct_and_role_permissions(): void
    {
        // Direct permission
        $this->user->givePermissionTo(Permission::VIEW_DATA->value);

        // Role permission
        $role = Role::create(['name' => 'Test Role', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::MANAGE_USERS->value);
        $this->user->assignRole($role);

        $response = $this->actingAs($this->user)
            ->getJson(route('user.permissions'));

        $response->assertOk();
        $json = $response->json('data.permissions');

        $this->assertContains(Permission::VIEW_DATA->value, $json);
        $this->assertContains(Permission::MANAGE_USERS->value, $json);
        $this->assertCount(2, $json);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson(route('user.permissions'));

        $response->assertUnauthorized();
    }

    public function test_only_returns_own_permissions(): void
    {
        // Give permissions to this user
        $this->user->givePermissionTo(Permission::VIEW_DATA->value);

        // Create another user with different permissions
        $otherUser = User::factory()->create();
        $otherUser->givePermissionTo([
            Permission::MANAGE_USERS->value,
            Permission::MANAGE_SETTINGS->value,
        ]);

        // Request as first user
        $response = $this->actingAs($this->user)
            ->getJson(route('user.permissions'));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'permissions' => [
                    Permission::VIEW_DATA->value,
                ],
            ],
        ]);

        // Should NOT include other user's permissions
        $json = $response->json('data.permissions');
        $this->assertNotContains(Permission::MANAGE_USERS->value, $json);
        $this->assertNotContains(Permission::MANAGE_SETTINGS->value, $json);
    }
}
