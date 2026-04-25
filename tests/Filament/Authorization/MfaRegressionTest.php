<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Filament\Auth\Login as AdminLogin;
use App\Filament\Auth\TwoFactorChallenge;
use App\Filament\Auth\TwoFactorSetup;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;
use Tests\Traits\CreatesTwoFactorUsers;

class MfaRegressionTest extends TestCase
{
    use CreatesTwoFactorUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    /**
     * T-1: Wrong-credential attempt on /admin/login returns a Livewire validation error
     * and leaves no filament.auth.panel key in the session.
     */
    public function test_wrong_credentials_on_admin_login_shows_validation_error_and_clears_panel_session(): void
    {
        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors(['data.email']);

        $this->assertGuest();
        $this->assertNull(session('filament.auth.panel'));
    }

    /**
     * T-2: /web/login with MFA and no prior /admin/login attempt — the
     * PanelAwareTwoFactorChallengeViewResponse must return the Blade view,
     * not redirect to /admin/two-factor-challenge.
     */
    public function test_web_login_with_mfa_without_admin_session_returns_blade_view(): void
    {
        $user = $this->createUserWithTotp();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // No filament.auth.panel should be set by a plain web login
        $this->assertNull(session('filament.auth.panel'));

        $this->get(route('two-factor.login'))
            ->assertOk()
            ->assertViewIs('auth.two-factor-challenge');
    }

    /**
     * T-3: /admin/two-factor-challenge form submission with valid TOTP redirects to
     * /admin without a ComponentNotFoundException or HTTP 500.
     */
    public function test_valid_totp_on_admin_two_factor_challenge_redirects_to_admin(): void
    {
        $this->mockTotpProvider(true);

        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('login.id', $user->getKey());
        session()->put('login.remember', false);
        session()->put('filament.auth.panel', 'admin');

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', '123456')
            ->call('submit')
            ->assertRedirect(Filament::getUrl());

        $this->assertAuthenticatedAs($user);
    }

    /**
     * T-4: /admin/two-factor-challenge form submission with invalid code returns a
     * Livewire validation error inline without authenticating the user.
     */
    public function test_invalid_totp_on_admin_two_factor_challenge_shows_validation_error(): void
    {
        $this->mockTotpProvider(false);

        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('login.id', $user->getKey());
        session()->put('filament.auth.panel', 'admin');

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', '000000')
            ->call('submit')
            ->assertHasErrors(['data.code']);

        $this->assertGuest();
    }

    /**
     * T-5: /admin/two-factor-setup form submission with valid TOTP during enrollment
     * sets two_factor_confirmed_at and redirects the user to /admin.
     */
    public function test_valid_totp_on_admin_two_factor_setup_confirms_enrollment_and_redirects_to_admin(): void
    {
        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('generateSecretKey')->andReturn('JBSWY3DPEHPK3PXP');
        $mock->shouldReceive('qrCodeUrl')->andReturn('otpauth://totp/test');
        $mock->shouldReceive('verify')->andReturn(true);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::actingAs($user)
            ->test(TwoFactorSetup::class)
            ->set('data.code', '123456')
            ->call('confirm')
            ->assertSet('step', 'recovery-codes')
            ->call('complete')
            ->assertRedirect(Filament::getUrl());

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
    }
}
