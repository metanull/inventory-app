<?php

namespace App\Services;

use App\Models\EmailTwoFactorCode;
use App\Models\User;
use App\Notifications\EmailTwoFactorCode as EmailTwoFactorCodeNotification;
use Illuminate\Support\Facades\Log;

class EmailTwoFactorService
{
    /**
     * The default expiry time for email 2FA codes in minutes.
     */
    public const DEFAULT_EXPIRY_MINUTES = 5;

    /**
     * The maximum number of codes that can be generated per user per hour.
     */
    public const MAX_CODES_PER_HOUR = 3;

    /**
     * Generate and send an email 2FA code to the user.
     */
    public function generateAndSendCode(User $user): EmailTwoFactorCode
    {
        // Check rate limiting
        $this->checkRateLimit($user);

        // Clean up old codes for this user
        $this->cleanupUserCodes($user);

        // Generate new code
        $code = EmailTwoFactorCode::generateCode();
        $expiryMinutes = config('auth.email_2fa.expiry_minutes', self::DEFAULT_EXPIRY_MINUTES);

        // Create the code record
        $emailCode = EmailTwoFactorCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        // Send the notification
        $user->notify(new EmailTwoFactorCodeNotification($code, $expiryMinutes));

        Log::info('Email 2FA code generated', [
            'user_id' => $user->id,
            'expires_at' => $emailCode->expires_at,
        ]);

        return $emailCode;
    }

    /**
     * Verify an email 2FA code for a user.
     */
    public function verifyCode(User $user, string $code): bool
    {
        $emailCode = $user->emailTwoFactorCodes()
            ->where('code', $code)
            ->valid()
            ->first();

        if (! $emailCode) {
            Log::warning('Email 2FA code verification failed', [
                'user_id' => $user->id,
                'code' => $code,
                'reason' => 'Code not found or invalid',
            ]);

            return false;
        }

        // Mark as used
        $emailCode->markAsUsed();

        Log::info('Email 2FA code verified successfully', [
            'user_id' => $user->id,
            'code_id' => $emailCode->id,
        ]);

        return true;
    }

    /**
     * Check if the user has exceeded the rate limit for code generation.
     */
    protected function checkRateLimit(User $user): void
    {
        $recentCodes = $user->emailTwoFactorCodes()
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $maxCodes = config('auth.email_2fa.rate_limit', self::MAX_CODES_PER_HOUR);

        if ($recentCodes >= $maxCodes) {
            throw new \Exception('Too many 2FA codes requested. Please try again later.');
        }
    }

    /**
     * Clean up expired and used codes for a user.
     */
    protected function cleanupUserCodes(User $user): void
    {
        $user->emailTwoFactorCodes()
            ->where(function ($query) {
                $query->where('expires_at', '<', now())
                    ->orWhereNotNull('used_at');
            })
            ->delete();
    }

    /**
     * Clean up all expired codes system-wide.
     */
    public static function cleanupExpiredCodes(): int
    {
        return EmailTwoFactorCode::cleanupExpired();
    }

    /**
     * Check if the user has email 2FA enabled.
     */
    public function isEmailTwoFactorEnabled(User $user): bool
    {
        return $user->email_2fa_enabled;
    }

    /**
     * Enable email 2FA for the user.
     */
    public function enableEmailTwoFactor(User $user): void
    {
        $user->update([
            'email_2fa_enabled' => true,
        ]);

        Log::info('Email 2FA enabled for user', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Disable email 2FA for the user.
     */
    public function disableEmailTwoFactor(User $user): void
    {
        $user->update([
            'email_2fa_enabled' => false,
        ]);

        // Clean up any pending codes
        $this->cleanupUserCodes($user);

        Log::info('Email 2FA disabled for user', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Get user's 2FA preference.
     */
    public function getTwoFactorPreference(User $user): string
    {
        return $user->preferred_2fa_method ?? 'totp';
    }

    /**
     * Set user's 2FA preference.
     */
    public function setTwoFactorPreference(User $user, string $method): void
    {
        if (! in_array($method, ['totp', 'email', 'both'])) {
            throw new \InvalidArgumentException('Invalid 2FA method');
        }

        $user->update([
            'preferred_2fa_method' => $method,
        ]);

        Log::info('2FA preference updated', [
            'user_id' => $user->id,
            'method' => $method,
        ]);
    }
}
