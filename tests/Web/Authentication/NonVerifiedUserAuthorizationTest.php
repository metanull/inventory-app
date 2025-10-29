<?php

namespace Tests\Feature\Auth;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission as PermissionModel;
use Tests\TestCase;

/**
 * Test authorization features for users without permissions (non-verified users).
 * These tests verify FEATURES, not roles or data structure.
 */
class NonVerifiedUserAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that users without permissions can access welcome page and see account under review message
     */
    public function test_user_without_permissions_sees_account_under_review_message(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertSee('Account Under Review');
        $response->assertSee('Your account has been successfully created, but it requires verification');
        $response->assertSee('Please wait for an administrator');
    }

    /**
     * Test that users without VIEW_DATA permission don't see inventory links
     */
    public function test_user_without_view_data_permission_does_not_see_inventory_links(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertDontSee('Items');
        $response->assertDontSee('Partners');
        $response->assertDontSee('Projects');
        $response->assertDontSee('Collections');
        $response->assertDontSee('Countries');
        $response->assertDontSee('Languages');
        $response->assertDontSee('Contexts');
        $response->assertDontSee('Glossary');
        $response->assertDontSee('Tags');
        $response->assertDontSee('Authors');
    }

    /**
     * Test that users without VIEW_DATA permission cannot access data routes
     */
    public function test_user_without_view_data_permission_cannot_access_items_page(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get(route('items.index'));

        $response->assertStatus(403);
    }

    /**
     * Test that users with VIEW_DATA permission see inventory content
     */
    public function test_user_with_view_data_permission_sees_inventory_content(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $permission = PermissionModel::findByName(Permission::VIEW_DATA->value);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo($permission);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($user)->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertSee('Items');
        $response->assertSee('Partners');
        $response->assertSee('Projects');
        $response->assertSee('Collections');
        $response->assertSee('Countries');
        $response->assertSee('Languages');
        $response->assertSee('Contexts');
        $response->assertSee('Glossary');
        $response->assertSee('Tags');
        $response->assertSee('Authors');
        $response->assertDontSee('Account Under Review');
    }

    /**
     * Test that users with VIEW_DATA permission can access data routes
     */
    public function test_user_with_view_data_permission_can_access_items_page(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $permission = PermissionModel::findByName(Permission::VIEW_DATA->value);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo($permission);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($user)->get(route('items.index'));

        $response->assertStatus(200);
    }

    /**
     * Test that users with MANAGE_USERS but not VIEW_DATA see admin but not inventory
     */
    public function test_user_with_manage_users_permission_sees_administration_but_not_inventory(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $permission = PermissionModel::findByName(Permission::MANAGE_USERS->value);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo($permission);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($user)->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertSee('Administration');
        $response->assertSee('User Management');
        $response->assertDontSee('Items');
        $response->assertDontSee('Partners');
        $response->assertDontSee('Projects');
        $response->assertDontSee('Collections');
        $response->assertDontSee('Countries');
        $response->assertDontSee('Languages');
        $response->assertDontSee('Contexts');
        $response->assertDontSee('Glossary');
        $response->assertDontSee('Tags');
        $response->assertDontSee('Authors');
        $response->assertDontSee('Account Under Review');
    }

    /**
     * Test that users with MANAGE_USERS but not VIEW_DATA cannot access data routes
     */
    public function test_user_with_manage_users_permission_cannot_access_items_page(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $permission = PermissionModel::findByName(Permission::MANAGE_USERS->value);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo($permission); // Give MANAGE_USERS but not VIEW_DATA

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($user)->get(route('items.index'));

        $response->assertStatus(403);
    }

    /**
     * Test that users with MANAGE_USERS permission can access user management
     */
    public function test_user_with_manage_users_permission_can_access_user_management(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $permission = PermissionModel::findByName(Permission::MANAGE_USERS->value);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo($permission);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertStatus(200);
    }
}
