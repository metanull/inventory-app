<?php

namespace Tests\Unit\Actions\Fortify;

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use App\Services\EmailTwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Tests\TestCase;

class UpdateUserPasswordTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected UpdateUserPassword $action;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new UpdateUserPassword;
        $this->user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        // Authenticate the user for current_password validation to work
        $this->actingAs($this->user, 'web');
    }

    public function test_password_can_be_updated_without_2fa(): void
    {
        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ];

        $this->action->update($this->user, $input);

        $this->assertTrue(Hash::check('new-password123', $this->user->fresh()->password));
    }

    public function test_password_update_fails_with_wrong_current_password(): void
    {
        $input = [
            'current_password' => 'wrong-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ];

        $this->expectException(ValidationException::class);
        $this->action->update($this->user, $input);
    }

    public function test_password_update_requires_2fa_code_when_totp_enabled(): void
    {
        // Enable TOTP for user
        $this->user->forceFill([
            'two_factor_secret' => 'test-secret',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Two-factor authentication code is required when changing password.');

        $this->action->update($this->user, $input);
    }

    public function test_password_update_requires_2fa_code_when_email_2fa_enabled(): void
    {
        // Enable email 2FA for user
        $this->user->forceFill(['email_2fa_enabled' => true])->save();

        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Two-factor authentication code is required when changing password.');

        $this->action->update($this->user, $input);
    }

    public function test_password_update_succeeds_with_valid_totp_code(): void
    {
        // Enable TOTP for user
        $this->user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Mock the TOTP provider to return true
        $totpProvider = $this->mock(TwoFactorAuthenticationProvider::class);
        $totpProvider->shouldReceive('verify')
            ->once()
            ->with('test-secret', '123456')
            ->andReturn(true);

        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
            'two_factor_code' => '123456',
        ];

        $this->action->update($this->user, $input);

        $this->assertTrue(Hash::check('new-password123', $this->user->fresh()->password));
    }

    public function test_password_update_fails_with_invalid_totp_code(): void
    {
        // Enable TOTP for user
        $this->user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Mock the TOTP provider to return false
        $totpProvider = $this->mock(TwoFactorAuthenticationProvider::class);
        $totpProvider->shouldReceive('verify')
            ->once()
            ->with('test-secret', '000000')
            ->andReturn(false);

        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
            'two_factor_code' => '000000',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided two-factor authentication code is invalid.');

        $this->action->update($this->user, $input);

        // Password should not have changed
        $this->assertTrue(Hash::check('current-password', $this->user->fresh()->password));
    }

    public function test_password_update_succeeds_with_valid_email_2fa_code(): void
    {
        // Enable email 2FA for user
        $this->user->forceFill(['email_2fa_enabled' => true])->save();

        // Mock the email 2FA service to return true
        $emailService = $this->mock(EmailTwoFactorService::class);
        $emailService->shouldReceive('verifyCode')
            ->once()
            ->with($this->user, '123456')
            ->andReturn(true);

        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
            'two_factor_code' => '123456',
        ];

        $this->action->update($this->user, $input);

        $this->assertTrue(Hash::check('new-password123', $this->user->fresh()->password));
    }

    public function test_password_update_fails_with_invalid_email_2fa_code(): void
    {
        // Enable email 2FA for user
        $this->user->forceFill(['email_2fa_enabled' => true])->save();

        // Mock the email 2FA service to return false
        $emailService = $this->mock(EmailTwoFactorService::class);
        $emailService->shouldReceive('verifyCode')
            ->once()
            ->with($this->user, '000000')
            ->andReturn(false);

        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
            'two_factor_code' => '000000',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided two-factor authentication code is invalid.');

        $this->action->update($this->user, $input);

        // Password should not have changed
        $this->assertTrue(Hash::check('current-password', $this->user->fresh()->password));
    }

    public function test_password_update_tries_totp_first_then_email_2fa(): void
    {
        // Enable both TOTP and email 2FA for user
        $this->user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
            'email_2fa_enabled' => true,
        ])->save();

        // Mock TOTP provider to return false (invalid TOTP)
        $totpProvider = $this->mock(TwoFactorAuthenticationProvider::class);
        $totpProvider->shouldReceive('verify')
            ->once()
            ->with('test-secret', '123456')
            ->andReturn(false);

        // Mock email 2FA service to return true (valid email code)
        $emailService = $this->mock(EmailTwoFactorService::class);
        $emailService->shouldReceive('verifyCode')
            ->once()
            ->with($this->user, '123456')
            ->andReturn(true);

        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
            'two_factor_code' => '123456',
        ];

        $this->action->update($this->user, $input);

        $this->assertTrue(Hash::check('new-password123', $this->user->fresh()->password));
    }

    public function test_password_update_fails_when_both_2fa_methods_invalid(): void
    {
        // Enable both TOTP and email 2FA for user
        $this->user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
            'email_2fa_enabled' => true,
        ])->save();

        // Mock both providers to return false
        $totpProvider = $this->mock(TwoFactorAuthenticationProvider::class);
        $totpProvider->shouldReceive('verify')
            ->once()
            ->with('test-secret', '000000')
            ->andReturn(false);

        $emailService = $this->mock(EmailTwoFactorService::class);
        $emailService->shouldReceive('verifyCode')
            ->once()
            ->with($this->user, '000000')
            ->andReturn(false);

        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
            'two_factor_code' => '000000',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided two-factor authentication code is invalid.');

        $this->action->update($this->user, $input);

        // Password should not have changed
        $this->assertTrue(Hash::check('current-password', $this->user->fresh()->password));
    }

    public function test_password_update_succeeds_with_valid_totp_when_both_methods_enabled(): void
    {
        // Enable both TOTP and email 2FA for user
        $this->user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
            'email_2fa_enabled' => true,
        ])->save();

        // Mock TOTP provider to return true (don't need to call email service)
        $totpProvider = $this->mock(TwoFactorAuthenticationProvider::class);
        $totpProvider->shouldReceive('verify')
            ->once()
            ->with('test-secret', '123456')
            ->andReturn(true);

        // Email service should not be called since TOTP succeeds
        $emailService = $this->mock(EmailTwoFactorService::class);
        $emailService->shouldNotReceive('verifyCode');

        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
            'two_factor_code' => '123456',
        ];

        $this->action->update($this->user, $input);

        $this->assertTrue(Hash::check('new-password123', $this->user->fresh()->password));
    }

    public function test_validation_error_bag_is_set_correctly(): void
    {
        // Enable email 2FA for user
        $this->user->forceFill(['email_2fa_enabled' => true])->save();

        // Mock the email 2FA service to return false
        $emailService = $this->mock(EmailTwoFactorService::class);
        $emailService->shouldReceive('verifyCode')
            ->once()
            ->andReturn(false);

        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
            'two_factor_code' => '000000',
        ];

        try {
            $this->action->update($this->user, $input);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertEquals('updatePassword', $e->errorBag);
            $this->assertArrayHasKey('two_factor_code', $e->errors());
        }
    }

    public function test_password_update_handles_invalid_base32_totp_secret_gracefully(): void
    {
        // Set up user with an invalid Base32 TOTP secret (contains 0 and 1 which are invalid)
        $this->user->forceFill([
            'two_factor_secret' => encrypt('2OTD3XWE6GGU6QVP'), // Contains invalid Base32 characters 0 and 1
            'two_factor_confirmed_at' => now(),
        ])->save();

        $input = [
            'current_password' => 'current-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
            'two_factor_code' => '123456',
        ];

        // Should fail with invalid 2FA code message, not crash with Base32 exception
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided two-factor authentication code is invalid.');

        $this->action->update($this->user, $input);

        // Password should not have changed
        $this->assertTrue(Hash::check('current-password', $this->user->fresh()->password));
    }
}
