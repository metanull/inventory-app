<?php

namespace Tests\Filament\Pages;

use App\Enums\Permission;
use App\Filament\Auth\Login as AdminLogin;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\CreatesTwoFactorUsers;

class LoginRedirectTest extends TestCase
{
    use CreatesTwoFactorUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_successful_login_for_unenrolled_user_redirects_to_setup(): void
    {
        $user = User::factory()->create([
            'two_factor_confirmed_at' => null,
            'two_factor_secret' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertRedirect(route('filament.admin.auth.two-factor-setup'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_successful_login_for_enrolled_user_triggers_fortify_challenge_redirect(): void
    {
        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'password')
            ->call('authenticate');

        // User should not be authenticated yet — 2FA challenge pending
        $this->assertGuest();
        $this->assertSame($user->getKey(), session('login.id'));

        // The Fortify GET route should now redirect to the Filament challenge page
        $this->get(route('two-factor.login'))
            ->assertRedirect(route('filament.admin.auth.two-factor-challenge'));
    }

    public function test_login_failure_raises_validation_exception(): void
    {
        $user = User::factory()->create([
            'two_factor_confirmed_at' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'wrongpassword')
            ->call('authenticate')
            ->assertHasErrors(['data.email']);

        $this->assertGuest();
    }
}
