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
     * and leaves no filament.auth.panel, no login.id, and no filament.admin.2fa.* keys in session.
     */
    public function test_wrong_credentials_on_admin_login_shows_validation_error_and_no_session_leak(): void
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
        $this->assertNull(session('login.id'));
        $this->assertNull(session('filament.admin.2fa.user_id'));
        $this->assertNull(session('filament.admin.2fa.remember'));
    }

    /**
     * T-2: Correct credentials on /admin/login for a user with confirmed 2FA:
     * Livewire redirects to filament.admin.auth.two-factor-challenge; user is still guest;
     * session has filament.admin.2fa.user_id only (no login.id, no filament.auth.panel).
     */
    public function test_correct_credentials_with_confirmed_2fa_redirects_to_filament_challenge(): void
    {
        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertRedirect(route('filament.admin.auth.two-factor-challenge'));

        $this->assertGuest();
        $this->assertSame($user->getKey(), session('filament.admin.2fa.user_id'));
        $this->assertNull(session('login.id'));
        $this->assertNull(session('filament.auth.panel'));
    }

    /**
     * T-3: Correct credentials on /admin/login for a user with access-admin-panel but
     * without confirmed 2FA: user is logged in and redirected to two-factor-setup.
     */
    public function test_correct_credentials_without_confirmed_2fa_redirects_to_setup(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertRedirect(route('filament.admin.auth.two-factor-setup'));

        $this->assertAuthenticatedAs($user);
    }

    /**
     * T-4: /admin/two-factor-challenge submit with valid TOTP:
     * Filament::auth()->user() is the expected user; redirect to Filament::getUrl(); session keys cleared.
     */
    public function test_valid_totp_on_admin_two_factor_challenge_redirects_to_admin(): void
    {
        $this->mockTotpProvider(true);

        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('filament.admin.2fa.user_id', $user->getKey());
        session()->put('filament.admin.2fa.remember', false);

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', '123456')
            ->call('submit')
            ->assertRedirect(Filament::getUrl());

        $this->assertAuthenticatedAs($user);
        $this->assertNull(session('filament.admin.2fa.user_id'));
        $this->assertNull(session('filament.admin.2fa.remember'));
    }

    /**
     * T-5: /admin/two-factor-challenge submit with invalid TOTP:
     * Validation error on data.code; user remains guest; rate-limit hit incremented.
     */
    public function test_invalid_totp_on_admin_two_factor_challenge_shows_validation_error(): void
    {
        $this->mockTotpProvider(false);

        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('filament.admin.2fa.user_id', $user->getKey());

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', '000000')
            ->call('submit')
            ->assertHasErrors(['data.code']);

        $this->assertGuest();
    }

    /**
     * T-6: /admin/two-factor-challenge submit with valid recovery code:
     * Login succeeds; recovery code is consumed.
     */
    public function test_valid_recovery_code_on_admin_two_factor_challenge_logs_in_and_consumes_code(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('filament.admin.2fa.user_id', $user->getKey());
        session()->put('filament.admin.2fa.remember', false);

        $recoveryCode = $this->getUnusedRecoveryCode();

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.recovery_code', $recoveryCode)
            ->call('submit')
            ->assertRedirect(Filament::getUrl());

        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotContains($recoveryCode, $user->recoveryCodes());
    }

    /**
     * T-7: /admin/two-factor-setup confirm with valid code:
     * two_factor_confirmed_at is set; redirect to Filament::getUrl().
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

    /**
     * T-8: Isolation A — static check.
     * The app/Filament/** and app/Http/Middleware/Filament/** trees must contain no forbidden strings.
     */
    public function test_isolation_a_filament_trees_contain_no_forbidden_strings(): void
    {
        $trees = [
            base_path('app/Filament'),
            base_path('app/Http/Middleware/Filament'),
        ];

        $forbidden = [
            "'two-factor.login'",
            '"two-factor.login"',
            'filament.auth.panel',
            'Routing\\Pipeline',
            'RedirectsIfTwoFactorAuthenticatable',
            'AttemptToAuthenticate',
            'PrepareAuthenticatedSession',
            'TwoFactorChallengeViewResponse',
            'TwoFactorLoginResponse',
        ];

        foreach ($trees as $tree) {
            if (! is_dir($tree)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tree));

            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $content = file_get_contents($file->getPathname());

                foreach ($forbidden as $needle) {
                    $this->assertStringNotContainsString(
                        $needle,
                        $content,
                        "Forbidden string '{$needle}' found in {$file->getPathname()}"
                    );
                }
            }
        }
    }

    /**
     * T-9: Isolation B — a successful /web/login for a user with confirmed 2FA,
     * with no prior /admin interaction in the session, redirects to route('two-factor.login')
     * and serves view('auth.two-factor-challenge') — never filament.admin.auth.two-factor-challenge.
     */
    public function test_web_login_with_mfa_without_admin_session_redirects_to_web_challenge(): void
    {
        $user = $this->createUserWithTotp();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertNull(session('filament.auth.panel'));
        $this->assertNull(session('filament.admin.2fa.user_id'));

        $this->get(route('two-factor.login'))
            ->assertOk()
            ->assertViewIs('auth.two-factor-challenge');
    }

    /**
     * T-10: Isolation C — a wrong /admin/login attempt followed by a /web/login for a 2FA user
     * in the same browser session still serves the /web Blade challenge view.
     * No filament.admin.2fa.* leak; the /web flow is independent of any /admin state.
     */
    public function test_isolation_c_wrong_admin_login_then_web_login_serves_web_challenge(): void
    {
        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        // First: wrong /admin/login attempt
        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors(['data.email']);

        $this->assertGuest();
        $this->assertNull(session('filament.admin.2fa.user_id'));

        // Then: correct /web/login attempt for the same 2FA user
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Web flow is independent — no filament.admin.2fa.* keys, no filament.auth.panel
        $this->assertNull(session('filament.admin.2fa.user_id'));
        $this->assertNull(session('filament.auth.panel'));

        // Web challenge is the Blade view, not the Filament challenge
        $this->get(route('two-factor.login'))
            ->assertOk()
            ->assertViewIs('auth.two-factor-challenge');
    }
}
