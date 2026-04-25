<?php

namespace Tests\Filament;

use App\Enums\Permission;
use App\Filament\Auth\Login as AdminLogin;
use App\Filament\Auth\TwoFactorChallenge;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\CreatesTwoFactorUsers;

class AdminPanelTest extends TestCase
{
    use CreatesTwoFactorUsers, RefreshDatabase;

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
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $response = $this->actingAs($user)->get('/admin');

        $response
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_user_without_administrative_permissions_cannot_access_filament_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    public function test_manager_role_is_seeded_with_admin_panel_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $role = Role::findByName('Manager of Users');

        $this->assertTrue($role->hasPermissionTo(Permission::ACCESS_ADMIN_PANEL->value));
    }

    public function test_visitor_role_is_seeded_with_admin_panel_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $role = Role::findByName('Visitor');

        $this->assertTrue($role->hasPermissionTo(Permission::ACCESS_ADMIN_PANEL->value));
    }

    public function test_regular_user_role_is_seeded_with_filament_reference_data_access(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $role = Role::findByName('Regular User');

        $this->assertTrue($role->hasPermissionTo(Permission::ACCESS_ADMIN_PANEL->value));
        $this->assertTrue($role->hasPermissionTo(Permission::MANAGE_REFERENCE_DATA->value));
    }

    public function test_filament_login_uses_existing_fortify_two_factor_flow(): void
    {
        $user = $this->createUserWithTotp(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'password')
            ->call('authenticate');

        $this->assertGuest();
        $this->assertSame($user->getKey(), session('login.id'));
        $this->assertSame('admin', session('filament.auth.panel'));

        // The legacy Fortify GET route now redirects to the Filament challenge page
        $this->get(route('two-factor.login'))
            ->assertRedirect(route('filament.admin.auth.two-factor-challenge'));

        // Complete the challenge via the Filament challenge page
        $this->mockTotpProvider(true);

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', '123456')
            ->call('submit');

        $this->assertAuthenticatedAs($user);
    }

    public function test_filament_login_rejects_users_without_panel_access_before_two_factor_challenge(): void
    {
        $user = $this->createUserWithTotp(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::MANAGE_USERS->value);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['data.email']);

        $this->assertGuest();
        $this->assertNull(session('login.id'));
        $this->get(route('two-factor.login'))->assertRedirect(route('login'));
    }
}
