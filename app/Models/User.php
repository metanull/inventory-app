<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract, MustVerifyEmail
{
    use Authenticatable;
    use CanResetPassword;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use MustVerifyEmailTrait;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_2fa_enabled',
        'preferred_2fa_method',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'email_2fa_enabled' => 'boolean',
    ];

    /**
     * Get the email 2FA codes for the user.
     */
    public function emailTwoFactorCodes()
    {
        return $this->hasMany(EmailTwoFactorCode::class);
    }

    /**
     * Override Fortify's method to include email 2FA.
     * This ensures Fortify redirects users with email 2FA to the challenge page.
     */
    public function hasEnabledTwoFactorAuthentication(): bool
    {
        // Check for TOTP 2FA (Fortify's default behavior)
        $hasTotpEnabled = ! is_null($this->two_factor_secret) &&
                          ! is_null($this->two_factor_confirmed_at);

        // Check for email 2FA (custom implementation)
        return $hasTotpEnabled || $this->email_2fa_enabled;
    }

    /**
     * Check if the user has any form of 2FA enabled.
     * Alias for hasEnabledTwoFactorAuthentication() method.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->hasEnabledTwoFactorAuthentication();
    }

    /**
     * Check if TOTP 2FA is enabled.
     */
    public function hasTotpEnabled(): bool
    {
        return $this->hasEnabledTwoFactorAuthentication();
    }

    /**
     * Check if email 2FA is enabled.
     */
    public function hasEmailTwoFactorEnabled(): bool
    {
        return (bool) $this->email_2fa_enabled;
    }

    /**
     * Check if user has any form of two-factor authentication enabled.
     * Alias for hasTwoFactorEnabled() method.
     */
    public function hasAnyTwoFactorEnabled(): bool
    {
        return $this->hasTwoFactorEnabled();
    }

    /**
     * Get the preferred 2FA method.
     */
    public function getPreferred2faMethod(): string
    {
        return $this->preferred_2fa_method ?? 'totp';
    }

    /**
     * Check if the user can use TOTP for 2FA based on their preference.
     */
    public function canUseTotpFor2fa(): bool
    {
        $preference = $this->getPreferred2faMethod();

        return $this->hasTotpEnabled() && in_array($preference, ['totp', 'both']);
    }

    /**
     * Check if the user can use email for 2FA based on their preference.
     */
    public function canUseEmailFor2fa(): bool
    {
        $preference = $this->getPreferred2faMethod();

        return $this->hasEmailTwoFactorEnabled() && in_array($preference, ['email', 'both']);
    }

    /**
     * Get available 2FA methods based on what's enabled and user preference.
     */
    public function getAvailable2faMethods(): array
    {
        $methods = [];

        if ($this->canUseTotpFor2fa()) {
            $methods[] = 'totp';
        }

        if ($this->canUseEmailFor2fa()) {
            $methods[] = 'email';
        }

        return $methods;
    }

    /**
     * Check if the user needs to complete 2FA verification.
     */
    public function needs2faVerification(): bool
    {
        return $this->hasTwoFactorEnabled() && ! empty($this->getAvailable2faMethods());
    }

    /**
     * Enable email 2FA for the user.
     */
    public function enableEmailTwoFactor(): void
    {
        $this->update(['email_2fa_enabled' => true]);
    }

    /**
     * Disable email 2FA for the user.
     */
    public function disableEmailTwoFactor(): void
    {
        $this->update(['email_2fa_enabled' => false]);

        // Clean up any pending email codes
        $this->emailTwoFactorCodes()
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->delete();
    }

    /**
     * Set the user's preferred 2FA method.
     */
    public function setPreferred2faMethod(string $method): void
    {
        if (! in_array($method, ['totp', 'email', 'both'])) {
            throw new \InvalidArgumentException('Invalid 2FA method. Must be one of: totp, email, both');
        }

        $this->update(['preferred_2fa_method' => $method]);
    }

    /**
     * Get the primary 2FA method to use during authentication.
     * Returns the first available method based on preference.
     */
    public function getPrimary2faMethod(): ?string
    {
        $preference = $this->getPreferred2faMethod();
        $available = $this->getAvailable2faMethods();

        if (empty($available)) {
            return null;
        }

        // If preference is 'both', prefer TOTP first
        if ($preference === 'both') {
            return in_array('totp', $available) ? 'totp' : $available[0];
        }

        // Return the preferred method if available, otherwise the first available
        return in_array($preference, $available) ? $preference : $available[0];
    }

    /**
     * Generate and send an email 2FA code.
     */
    public function generateEmailTwoFactorCode(): ?EmailTwoFactorCode
    {
        if (! $this->canUseEmailFor2fa()) {
            return null;
        }

        $service = app(\App\Services\EmailTwoFactorService::class);

        return $service->generateAndSendCode($this);
    }

    /**
     * Verify an email 2FA code.
     */
    public function verifyEmailTwoFactorCode(string $code): bool
    {
        if (! $this->canUseEmailFor2fa()) {
            return false;
        }

        $service = app(\App\Services\EmailTwoFactorService::class);

        return $service->verifyCode($this, $code);
    }

    /**
     * Validate and consume a recovery code using Fortify's standard secure approach.
     * Provides case-insensitive comparison while maintaining Fortify's replacement security model.
     */
    public function validateAndConsumeRecoveryCode(string $code): bool
    {
        if (! $this->hasEnabledTwoFactorAuthentication()) {
            return false;
        }

        $recoveryCodes = $this->recoveryCodes();

        if (empty($recoveryCodes)) {
            return false;
        }

        // Case insensitive comparison with Fortify's standard replacement approach
        foreach ($recoveryCodes as $recoveryCode) {
            if (hash_equals(strtolower($recoveryCode), strtolower($code))) {
                // Use Fortify's standard replacement approach (more secure than tracking)
                $this->replaceRecoveryCode($recoveryCode);

                return true;
            }
        }

        return false;
    }

    /**
     * Get the user's two factor authentication recovery codes.
     * Override Fortify's method to handle null values gracefully.
     */
    public function recoveryCodes(): array
    {
        if (is_null($this->two_factor_recovery_codes)) {
            return [];
        }

        return json_decode(decrypt($this->two_factor_recovery_codes), true) ?? [];
    }
}
