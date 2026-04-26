<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingRegistrationsPageAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_manage_users_permission_cannot_access_pending_registrations_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin/pending-registrations-page')
            ->assertForbidden();
    }

    public function test_users_with_manage_users_permission_can_access_pending_registrations_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::MANAGE_USERS->value,
        ]);

        $this->actingAs($user)->get('/admin/pending-registrations-page')
            ->assertOk();
    }

    public function test_pending_registrations_does_not_appear_in_nav_for_unpermitted_users(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Pending Registrations');
    }

    public function test_pending_registrations_appears_in_nav_for_permitted_users(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::MANAGE_USERS->value,
        ]);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Pending Registrations');
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get('/admin/pending-registrations-page')
            ->assertRedirect('/admin/login');
    }
}
