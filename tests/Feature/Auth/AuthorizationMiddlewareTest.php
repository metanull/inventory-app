<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Create test routes
        Route::get('/test-permission', function () {
            return response()->json(['message' => 'success']);
        })->middleware(['auth:sanctum', 'permission:view data']);

        Route::get('/test-role', function () {
            return response()->json(['message' => 'success']);
        })->middleware(['auth:sanctum', 'role:Regular User']);

        Route::get('/test-no-role', function () {
            return response()->json(['message' => 'success']);
        })->middleware(['auth:sanctum', 'role:Manager of Users']);
    }

    /** @test */
    public function it_denies_access_to_unauthenticated_users()
    {
        $response = $this->getJson('/test-permission');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_denies_access_to_users_without_permission()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/test-permission');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_allows_access_to_users_with_permission()
    {
        $user = User::factory()->create();
        $role = Role::findByName('Regular User');
        $user->assignRole($role);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/test-permission');

        $response->assertStatus(200)
            ->assertJson(['message' => 'success']);
    }

    /** @test */
    public function it_denies_access_to_users_without_role()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/test-no-role');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_allows_access_to_users_with_correct_role()
    {
        $user = User::factory()->create();
        $role = Role::findByName('Manager of Users');
        $user->assignRole($role);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/test-no-role');

        $response->assertStatus(200)
            ->assertJson(['message' => 'success']);
    }

    /** @test */
    public function it_denies_access_to_users_with_direct_permissions_but_no_roles()
    {
        // According to requirements: "No Role: No access"
        // Users must have roles, direct permissions alone are not sufficient
        $user = User::factory()->create();
        $permission = Permission::findByName('view data');
        $user->givePermissionTo($permission);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/test-permission');

        $response->assertStatus(403)
            ->assertJsonFragment(['reason' => 'User has no assigned roles']);
    }

    /** @test */
    public function custom_role_middleware_denies_users_without_any_roles()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/test-role');

        $response->assertStatus(403);
        $response->assertJsonFragment(['reason' => 'User has no assigned roles']);
    }

    /** @test */
    public function custom_role_middleware_allows_users_with_correct_role()
    {
        $user = User::factory()->create();
        $role = Role::findByName('Regular User');
        $user->assignRole($role);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/test-role');

        $response->assertStatus(200);
    }
}
