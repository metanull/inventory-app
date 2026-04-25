<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_manage_users_permission_cannot_see_user_resource(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Users');

        $this->actingAs($user)->get('/admin/users')
            ->assertForbidden();
    }

    public function test_users_with_manage_users_permission_can_access_user_index(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::MANAGE_USERS->value,
        ]);

        $this->actingAs($user)->get('/admin/users')
            ->assertOk();
    }

    public function test_users_without_assign_roles_permission_cannot_delete_users(): void
    {
        $manager = User::factory()->create(['email_verified_at' => now()]);
        $manager->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::MANAGE_USERS->value,
        ]);

        $target = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($manager)->get("/admin/users/{$target->getKey()}/edit")
            ->assertOk()
            ->assertDontSee('Delete');
    }

    public function test_users_with_assign_roles_permission_can_delete_other_users(): void
    {
        $manager = User::factory()->create(['email_verified_at' => now()]);
        $manager->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::MANAGE_USERS->value,
            Permission::ASSIGN_ROLES->value,
        ]);

        $target = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($manager)->get("/admin/users/{$target->getKey()}/edit")
            ->assertOk()
            ->assertSee('Delete');
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $manager = User::factory()->create(['email_verified_at' => now()]);
        $manager->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::MANAGE_USERS->value,
            Permission::ASSIGN_ROLES->value,
        ]);

        $response = $this->actingAs($manager)->delete("/admin/users/{$manager->getKey()}");

        $this->assertDatabaseHas('users', ['id' => $manager->id]);
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get('/admin/users')
            ->assertRedirect('/admin/login');
    }
}
