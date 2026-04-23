<?php

namespace Tests\Web\Admin;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_filament_login_screen_renders_at_admin_login(): void
    {
        $response = $this->get('/admin/login');

        $response
            ->assertOk()
            ->assertSee('Sign in')
            ->assertSee(config('app.name'));
    }

    public function test_admin_dashboard_redirects_guests_to_filament_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(url('/admin/login'));
    }

    public function test_user_with_administrative_permission_can_access_filament_dashboard(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::MANAGE_USERS);

        $response = $this->actingAs($user)->get('/admin');

        $response
            ->assertOk()
            ->assertSee('Dashboard');
    }
}
