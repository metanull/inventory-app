<?php

namespace Tests\Filament\Pages;

use App\Enums\Permission;
use App\Filament\Auth\TwoFactorChallenge;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\CreatesTwoFactorUsers;

class TwoFactorChallengePageTest extends TestCase
{
    use CreatesTwoFactorUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_page_renders_for_user_with_pending_login_session(): void
    {
        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('login.id', $user->getKey());
        session()->put('filament.auth.panel', 'admin');

        Livewire::test(TwoFactorChallenge::class)
            ->assertStatus(200);
    }

    public function test_page_redirects_to_login_if_no_pending_login_session(): void
    {
        Livewire::test(TwoFactorChallenge::class)
            ->assertRedirect(Filament::getLoginUrl());
    }

    public function test_valid_totp_code_completes_login_and_clears_session(): void
    {
        $this->mockTotpProvider(true);

        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('login.id', $user->getKey());
        session()->put('login.remember', false);
        session()->put('filament.auth.panel', 'admin');

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', '123456')
            ->call('submit');

        $this->assertAuthenticatedAs($user);
        $this->assertNull(session('login.id'));
        $this->assertNull(session('filament.auth.panel'));
    }

    public function test_invalid_totp_code_does_not_authenticate_and_keeps_session(): void
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
        $this->assertSame($user->getKey(), session('login.id'));
    }

    public function test_valid_recovery_code_completes_login_and_consumes_code(): void
    {
        $user = $this->createUserWithRecoveryCodes();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $recoveryCode = $this->getUnusedRecoveryCode();

        session()->put('login.id', $user->getKey());
        session()->put('login.remember', false);
        session()->put('filament.auth.panel', 'admin');

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.recovery_code', $recoveryCode)
            ->call('submit');

        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $remainingCodes = $user->recoveryCodes();
        $this->assertNotContains($recoveryCode, $remainingCodes);
    }

    public function test_throttling_redirects_to_login_after_too_many_attempts(): void
    {
        $this->mockTotpProvider(false);

        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('login.id', $user->getKey());
        session()->put('filament.auth.panel', 'admin');

        // Exhaust the rate limit (50 in testing)
        $limiterKey = 'two-factor:'.$user->getKey();
        $maxAttempts = app()->environment('testing') ? 50 : 5;

        RateLimiter::clear($limiterKey);

        for ($i = 0; $i < $maxAttempts; $i++) {
            RateLimiter::hit($limiterKey);
        }

        // The next attempt should be throttled
        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', '000000')
            ->call('submit')
            ->assertRedirect(Filament::getLoginUrl());

        // Session keys should be cleared
        $this->assertNull(session('login.id'));
        $this->assertNull(session('filament.auth.panel'));
    }
}
