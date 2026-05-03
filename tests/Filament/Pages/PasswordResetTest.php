<?php

namespace Tests\Filament\Pages;

use App\Enums\Permission;
use App\Filament\Auth\PasswordResetMfaChallenge;
use App\Filament\Auth\RequestPasswordReset;
use App\Filament\Auth\ResetPassword;
use App\Models\User;
use App\Notifications\AdminPasswordResetNotification;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\CreatesTwoFactorUsers;

class PasswordResetTest extends TestCase
{
    use CreatesTwoFactorUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    // ─── RequestPasswordReset page ────────────────────────────────────────────

    public function test_request_password_reset_page_renders(): void
    {
        Livewire::test(RequestPasswordReset::class)
            ->assertStatus(200);
    }

    public function test_request_password_reset_redirects_authenticated_user(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::actingAs($user)
            ->test(RequestPasswordReset::class)
            ->assertRedirect(Filament::getUrl());
    }

    public function test_reset_link_is_dispatched_for_existing_email(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'user@example.com']);

        Livewire::test(RequestPasswordReset::class)
            ->set('data.email', 'user@example.com')
            ->call('submit')
            ->assertHasNoErrors();

        Notification::assertSentTo($user, AdminPasswordResetNotification::class);
    }

    public function test_admin_reset_link_targets_filament_route_not_web_route(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $notification = new AdminPasswordResetNotification('admin-reset-token');

        $mailMessage = $notification->toMail($user);
        $expectedUrl = route('filament.admin.auth.password.reset', [
            'token' => 'admin-reset-token',
            'email' => 'user@example.com',
        ]);

        $this->assertSame($expectedUrl, $mailMessage->actionUrl);
        $this->assertStringNotContainsString('/web/', $mailMessage->actionUrl);
    }

    public function test_reset_link_submission_shows_success_notification_even_for_unknown_email(): void
    {
        Livewire::test(RequestPasswordReset::class)
            ->set('data.email', 'nobody@example.com')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertNotified();
    }

    // ─── ResetPassword page ───────────────────────────────────────────────────

    public function test_reset_password_page_renders_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        Livewire::test(ResetPassword::class, ['token' => $token])
            ->assertStatus(200);
    }

    public function test_reset_password_rejects_invalid_token(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        Livewire::test(ResetPassword::class, ['token' => 'invalid-token'])
            ->set('data.email', 'user@example.com')
            ->set('data.password', 'NewPassword1!')
            ->set('data.password_confirmation', 'NewPassword1!')
            ->call('submit')
            ->assertHasErrors('data.email');
    }

    public function test_reset_password_rejects_unknown_email(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        Livewire::test(ResetPassword::class, ['token' => $token])
            ->set('data.email', 'nobody@example.com')
            ->set('data.password', 'NewPassword1!')
            ->set('data.password_confirmation', 'NewPassword1!')
            ->call('submit')
            ->assertHasErrors('data.email');
    }

    public function test_reset_password_without_mfa_updates_password_and_redirects_to_login(): void
    {
        $user = $this->createUserWithoutTwoFactor(['email' => 'user@example.com']);
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        Livewire::test(ResetPassword::class, ['token' => $token])
            ->set('data.email', 'user@example.com')
            ->set('data.password', 'NewPassword1!')
            ->set('data.password_confirmation', 'NewPassword1!')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(Filament::getLoginUrl());

        $this->assertTrue(Hash::check('NewPassword1!', $user->fresh()->password));
        $this->assertGuest(config('fortify.guard'));
    }

    public function test_reset_password_token_is_invalidated_after_successful_reset_without_mfa(): void
    {
        $user = $this->createUserWithoutTwoFactor(['email' => 'user@example.com']);
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        Livewire::test(ResetPassword::class, ['token' => $token])
            ->set('data.email', 'user@example.com')
            ->set('data.password', 'NewPassword1!')
            ->set('data.password_confirmation', 'NewPassword1!')
            ->call('submit');

        $tokenRepository = Password::broker(config('fortify.passwords'))->getRepository();
        $this->assertFalse($tokenRepository->exists($user->fresh(), $token));
    }

    public function test_reset_password_with_mfa_stores_pending_reset_and_redirects_to_mfa_page(): void
    {
        $user = $this->createUserWithTotp(['email' => 'user@example.com']);
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        Livewire::test(ResetPassword::class, ['token' => $token])
            ->set('data.email', 'user@example.com')
            ->set('data.password', 'NewPassword1!')
            ->set('data.password_confirmation', 'NewPassword1!')
            ->call('submit')
            ->assertRedirect(Filament::getCurrentPanel()->route('auth.password.reset.mfa'));

        $this->assertNotNull(session('filament.admin.password_reset.user_id'));
        $this->assertNotNull(session('filament.admin.password_reset.password_hash'));
        $this->assertNotNull(session('filament.admin.password_reset.token'));
    }

    public function test_reset_password_with_mfa_does_not_immediately_change_password(): void
    {
        $user = $this->createUserWithTotp(['email' => 'user@example.com']);
        $originalPasswordHash = $user->password;
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        Livewire::test(ResetPassword::class, ['token' => $token])
            ->set('data.email', 'user@example.com')
            ->set('data.password', 'NewPassword1!')
            ->set('data.password_confirmation', 'NewPassword1!')
            ->call('submit');

        $this->assertEquals($originalPasswordHash, $user->fresh()->password);
    }

    // ─── PasswordResetMfaChallenge page ───────────────────────────────────────

    public function test_mfa_challenge_page_redirects_without_pending_session(): void
    {
        Livewire::test(PasswordResetMfaChallenge::class)
            ->assertRedirect(Filament::getLoginUrl());
    }

    public function test_mfa_challenge_page_renders_with_pending_session(): void
    {
        $user = $this->createUserWithTotp(['email' => 'user@example.com']);

        session()->put('filament.admin.password_reset.user_id', $user->id);
        session()->put('filament.admin.password_reset.password_hash', Hash::make('NewPassword1!'));
        session()->put('filament.admin.password_reset.token', 'fake-token');

        Livewire::test(PasswordResetMfaChallenge::class)
            ->assertStatus(200);
    }

    public function test_valid_totp_code_completes_password_reset_and_redirects_to_login(): void
    {
        $this->mockTotpProvider(true);

        $user = $this->createUserWithTotp(['email' => 'user@example.com']);
        $newPasswordHash = Hash::make('NewPassword1!');
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        session()->put('filament.admin.password_reset.user_id', $user->id);
        session()->put('filament.admin.password_reset.password_hash', $newPasswordHash);
        session()->put('filament.admin.password_reset.token', $token);

        Livewire::test(PasswordResetMfaChallenge::class)
            ->set('data.code', '123456')
            ->call('submit')
            ->assertRedirect(Filament::getLoginUrl());

        $this->assertEquals($newPasswordHash, $user->fresh()->password);
        $this->assertGuest(config('fortify.guard'));
    }

    public function test_valid_totp_code_invalidates_reset_token(): void
    {
        $this->mockTotpProvider(true);

        $user = $this->createUserWithTotp(['email' => 'user@example.com']);
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        session()->put('filament.admin.password_reset.user_id', $user->id);
        session()->put('filament.admin.password_reset.password_hash', Hash::make('NewPassword1!'));
        session()->put('filament.admin.password_reset.token', $token);

        Livewire::test(PasswordResetMfaChallenge::class)
            ->set('data.code', '123456')
            ->call('submit');

        $tokenRepository = Password::broker(config('fortify.passwords'))->getRepository();
        $this->assertFalse($tokenRepository->exists($user->fresh(), $token));
    }

    public function test_valid_totp_code_clears_session_keys(): void
    {
        $this->mockTotpProvider(true);

        $user = $this->createUserWithTotp(['email' => 'user@example.com']);
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        session()->put('filament.admin.password_reset.user_id', $user->id);
        session()->put('filament.admin.password_reset.password_hash', Hash::make('NewPassword1!'));
        session()->put('filament.admin.password_reset.token', $token);

        Livewire::test(PasswordResetMfaChallenge::class)
            ->set('data.code', '123456')
            ->call('submit');

        $this->assertNull(session('filament.admin.password_reset.user_id'));
        $this->assertNull(session('filament.admin.password_reset.password_hash'));
        $this->assertNull(session('filament.admin.password_reset.token'));
    }

    public function test_invalid_totp_code_does_not_reset_password(): void
    {
        $this->mockTotpProvider(false);

        $user = $this->createUserWithTotp(['email' => 'user@example.com']);
        $originalPasswordHash = $user->password;

        session()->put('filament.admin.password_reset.user_id', $user->id);
        session()->put('filament.admin.password_reset.password_hash', Hash::make('NewPassword1!'));
        session()->put('filament.admin.password_reset.token', 'fake-token');

        Livewire::test(PasswordResetMfaChallenge::class)
            ->set('data.code', '000000')
            ->call('submit')
            ->assertHasErrors(['data.code']);

        $this->assertEquals($originalPasswordHash, $user->fresh()->password);
    }

    public function test_valid_recovery_code_completes_password_reset(): void
    {
        $user = $this->createUserWithRecoveryCodes(['email' => 'user@example.com']);
        $newPasswordHash = Hash::make('NewPassword1!');
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        session()->put('filament.admin.password_reset.user_id', $user->id);
        session()->put('filament.admin.password_reset.password_hash', $newPasswordHash);
        session()->put('filament.admin.password_reset.token', $token);

        Livewire::test(PasswordResetMfaChallenge::class)
            ->set('data.recovery_code', 'RECOVERY-CODE-1')
            ->call('submit')
            ->assertRedirect(Filament::getLoginUrl());

        $this->assertEquals($newPasswordHash, $user->fresh()->password);
    }

    public function test_invalid_recovery_code_does_not_reset_password(): void
    {
        $user = $this->createUserWithRecoveryCodes(['email' => 'user@example.com']);
        $originalPasswordHash = $user->password;

        session()->put('filament.admin.password_reset.user_id', $user->id);
        session()->put('filament.admin.password_reset.password_hash', Hash::make('NewPassword1!'));
        session()->put('filament.admin.password_reset.token', 'fake-token');

        Livewire::test(PasswordResetMfaChallenge::class)
            ->set('data.recovery_code', 'WRONG-RECOVERY-CODE')
            ->call('submit')
            ->assertHasErrors(['data.code']);

        $this->assertEquals($originalPasswordHash, $user->fresh()->password);
    }

    public function test_providing_multiple_verification_methods_fails(): void
    {
        $user = $this->createUserWithTotp(['email' => 'user@example.com']);

        session()->put('filament.admin.password_reset.user_id', $user->id);
        session()->put('filament.admin.password_reset.password_hash', Hash::make('NewPassword1!'));
        session()->put('filament.admin.password_reset.token', 'fake-token');

        Livewire::test(PasswordResetMfaChallenge::class)
            ->set('data.code', '123456')
            ->set('data.recovery_code', 'RECOVERY-CODE-1')
            ->call('submit')
            ->assertHasErrors(['data.code', 'data.recovery_code', 'data.email_code']);
    }

    // ─── Route isolation ──────────────────────────────────────────────────────

    public function test_admin_reset_flow_does_not_touch_web_routes(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::broker(config('fortify.passwords'))->createToken($user);

        $adminResponse = $this->get(route('filament.admin.auth.password.reset', ['token' => $token]));
        $adminResponse->assertOk();

        $this->assertFalse($this->app['router']->current()?->getPrefix() === '/web');
    }
}
