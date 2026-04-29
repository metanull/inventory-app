<?php

namespace App\Services\Filament\Auth;

use App\Models\User;
use App\Notifications\Filament\Auth\EmailTwoFactorCodeNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Random\RandomException;

class EmailTwoFactorCodeService
{
    private const CACHE_TTL = 600; // 10 minutes

    private const CODE_MIN = 100000;

    private const CODE_MAX = 999999;

    private const SEND_MAX_ATTEMPTS = 5;

    private const VERIFY_MAX_ATTEMPTS = 5;

    public function send(User $user): void
    {
        if (! $user->hasVerifiedEmail()) {
            throw new \RuntimeException('User does not have a verified email address.');
        }

        if (session('filament.admin.2fa.user_id') !== $user->getKey()) {
            throw new \RuntimeException('No pending admin MFA session for this user.');
        }

        $sendLimiterKey = $this->sendLimiterKey($user->getKey(), request()->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($sendLimiterKey, self::SEND_MAX_ATTEMPTS)) {
            throw new \RuntimeException('Too many email code requests. Please try again later.');
        }

        try {
            $code = (string) random_int(self::CODE_MIN, self::CODE_MAX);
        } catch (RandomException $e) {
            throw new \RuntimeException('Failed to generate verification code.', 0, $e);
        }

        $codeHash = Hash::make($code);
        $challengeId = Str::uuid()->toString();
        $cacheKey = $this->cacheKey($challengeId);

        $previousChallengeId = session('filament.admin.2fa.email_challenge_id');
        if ($previousChallengeId) {
            Cache::forget($this->cacheKey($previousChallengeId));
        }

        Cache::put($cacheKey, [
            'user_id' => $user->getKey(),
            'code_hash' => $codeHash,
            'expires_at' => now()->addSeconds(self::CACHE_TTL)->timestamp,
        ], self::CACHE_TTL);

        session()->put('filament.admin.2fa.email_challenge_id', $challengeId);

        RateLimiter::hit($sendLimiterKey, self::CACHE_TTL);

        try {
            $user->notify(new EmailTwoFactorCodeNotification($code));
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            session()->forget('filament.admin.2fa.email_challenge_id');
            throw $e;
        }
    }

    public function verify(User $user, string $code): bool
    {
        if (! $user->hasVerifiedEmail()) {
            return false;
        }

        $challengeId = session('filament.admin.2fa.email_challenge_id');

        if (! $challengeId) {
            return false;
        }

        $cacheKey = $this->cacheKey($challengeId);
        $payload = Cache::get($cacheKey);

        if (! $payload) {
            session()->forget('filament.admin.2fa.email_challenge_id');

            return false;
        }

        $pendingUserId = session('filament.admin.2fa.user_id');

        if ($payload['user_id'] !== $user->getKey() || $user->getKey() !== $pendingUserId) {
            session()->forget('filament.admin.2fa.email_challenge_id');

            return false;
        }

        $verifyLimiterKey = $this->verifyLimiterKey($challengeId);

        if (RateLimiter::tooManyAttempts($verifyLimiterKey, self::VERIFY_MAX_ATTEMPTS)) {
            session()->forget('filament.admin.2fa.email_challenge_id');
            Cache::forget($cacheKey);

            return false;
        }

        if (Hash::check($code, $payload['code_hash'])) {
            Cache::forget($cacheKey);
            session()->forget('filament.admin.2fa.email_challenge_id');
            RateLimiter::clear($verifyLimiterKey);

            return true;
        }

        RateLimiter::hit($verifyLimiterKey);

        return false;
    }

    public function sendLimiterKey(mixed $userId, string $ip): string
    {
        return "filament-admin-email-2fa-send:{$userId}:{$ip}";
    }

    /** @internal used for testing cache key assertions */
    public function verifyLimiterKey(string $challengeId): string
    {
        return "filament-admin-email-2fa-verify:{$challengeId}";
    }

    /** @internal used for testing cache key assertions */
    public function cacheKey(string $challengeId): string
    {
        return "filament:admin:2fa:email:{$challengeId}";
    }
}
