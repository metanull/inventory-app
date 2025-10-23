<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, MustVerifyEmail
{
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles;
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
    ];

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

    /**
     * Check if the user has any sensitive permissions that require MFA.
     */
    public function hasSensitivePermissions(): bool
    {
        $sensitivePermissions = \App\Enums\Permission::sensitivePermissions();

        foreach ($sensitivePermissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }
}
