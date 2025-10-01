<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\EmailTwoFactorService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Tests\TestCase;
use Tests\Traits\AuthenticationTestHelpers;

class PasswordResetTest extends TestCase
{
    use AuthenticationTestHelpers, RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock email sending for all tests
        Mail::fake();
        Notification::fake();
    }

    public function test_user_can_request_password_reset_without_two_factor(): void
    {
        $user = $this->createUserWithoutTwoFactor();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect();
        $response->assertSessionHas('status', __('We have emailed your password reset link.'));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_user_can_request_password_reset_with_totp_enabled(): void
    {
        $user = $this->createUserWithTotp();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect();
        $response->assertSessionHas('status', __('We have emailed your password reset link.'));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_user_can_request_password_reset_with_email_two_factor_enabled(): void
    {
        $user = $this->createUserWithEmailTwoFactor();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect();
        $response->assertSessionHas('status', __('We have emailed your password reset link.'));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_request_fails_with_invalid_email(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_can_reset_password_without_two_factor(): void
    {
        Event::fake();
        $user = $this->createUserWithoutTwoFactor();
        $token = Password::createToken($user);

        $newPassword = 'new-secure-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('status', __('Your password has been reset.'));

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
        Event::assertDispatched(PasswordReset::class);
    }

    public function test_user_can_reset_password_with_totp_enabled(): void
    {
        Event::fake();
        $this->mockTotpProvider(true);

        $user = $this->createUserWithTotp();
        $token = Password::createToken($user);

        $newPassword = 'new-secure-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            'two_factor_code' => $this->getValidTotpCode(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('status', __('Your password has been reset.'));

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
        Event::assertDispatched(PasswordReset::class);
    }

    public function test_user_can_reset_password_with_email_two_factor_enabled(): void
    {
        Event::fake();
        $this->mockEmailTwoFactorService(true);

        $user = $this->createUserWithEmailTwoFactor();
        $token = Password::createToken($user);

        $newPassword = 'new-secure-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            'two_factor_code' => $this->getValidEmailTwoFactorCode(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('status', __('Your password has been reset.'));

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
        Event::assertDispatched(PasswordReset::class);
    }

    public function test_password_reset_fails_with_invalid_totp_code(): void
    {
        $this->mockTotpProvider(false);

        $user = $this->createUserWithTotp();
        $token = Password::createToken($user);

        $newPassword = 'new-secure-password';
        $originalPassword = $user->password;

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            'two_factor_code' => $this->getInvalidTotpCode(),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['two_factor_code']);

        // Password should not have changed
        $this->assertEquals($originalPassword, $user->fresh()->password);
    }

    public function test_password_reset_fails_with_invalid_email_two_factor_code(): void
    {
        $this->mockEmailTwoFactorService(false);

        $user = $this->createUserWithEmailTwoFactor();
        $token = Password::createToken($user);

        $newPassword = 'new-secure-password';
        $originalPassword = $user->password;

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            'two_factor_code' => $this->getInvalidEmailTwoFactorCode(),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['two_factor_code']);

        // Password should not have changed
        $this->assertEquals($originalPassword, $user->fresh()->password);
    }

    public function test_password_reset_requires_two_factor_code_when_totp_enabled(): void
    {
        $user = $this->createUserWithTotp();
        $token = Password::createToken($user);

        $newPassword = 'new-secure-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            // Missing two_factor_code
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['two_factor_code']);
    }

    public function test_password_reset_requires_two_factor_code_when_email_two_factor_enabled(): void
    {
        $user = $this->createUserWithEmailTwoFactor();
        $token = Password::createToken($user);

        $newPassword = 'new-secure-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            // Missing two_factor_code
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['two_factor_code']);
    }

    public function test_password_reset_with_both_two_factor_methods_tries_totp_first(): void
    {
        Event::fake();

        // Mock TOTP to succeed
        $this->mock(TwoFactorAuthenticationProvider::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn(true);
        });

        // Email 2FA should not be called since TOTP succeeds
        $this->mock(EmailTwoFactorService::class, function ($mock) {
            $mock->shouldNotReceive('verifyCode');
        });

        $user = $this->createUserWithBothTwoFactor();
        $token = Password::createToken($user);

        $newPassword = 'new-secure-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            'two_factor_code' => $this->getValidTotpCode(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }

    public function test_password_reset_with_both_two_factor_methods_falls_back_to_email(): void
    {
        Event::fake();

        // Mock TOTP to fail
        $this->mock(TwoFactorAuthenticationProvider::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn(false);
        });

        // Mock email 2FA to succeed
        $this->mock(EmailTwoFactorService::class, function ($mock) {
            $mock->shouldReceive('verifyCode')->once()->andReturn(true);
        });

        $user = $this->createUserWithBothTwoFactor();
        $token = Password::createToken($user);

        $newPassword = 'new-secure-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            'two_factor_code' => $this->getValidEmailTwoFactorCode(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        $user = $this->createUserWithoutTwoFactor();

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_password_reset_fails_with_mismatched_passwords(): void
    {
        $user = $this->createUserWithoutTwoFactor();
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
    }

    public function test_password_reset_handles_invalid_base32_totp_secret_gracefully(): void
    {
        // Create user with invalid Base32 TOTP secret
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'two_factor_secret' => encrypt('INVALID0TOTP1SECRET'), // Contains invalid Base32 chars
            'two_factor_confirmed_at' => now(),
            'email_2fa_enabled' => false,
        ]);

        $token = Password::createToken($user);
        $newPassword = 'new-secure-password';

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            'two_factor_code' => '123456',
        ]);

        // Should fail with validation error, not crash
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['two_factor_code']);
    }
}
