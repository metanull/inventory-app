<?php

namespace Tests\Filament\Pages;

use App\Enums\Permission;
use App\Filament\Auth\TwoFactorChallenge;
use App\Notifications\Filament\Auth\EmailTwoFactorCodeNotification;
use App\Services\Filament\Auth\EmailTwoFactorCodeService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\CreatesTwoFactorUsers;

class TwoFactorChallengeEmailCodeTest extends TestCase
{
    use CreatesTwoFactorUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_pending_user_can_request_email_code_and_notification_is_sent(): void
    {
        Notification::fake();

        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('filament.admin.2fa.user_id', $user->getKey());

        Livewire::test(TwoFactorChallenge::class)
            ->call('sendEmailCode')
            ->assertHasNoErrors();

        Notification::assertSentTo($user, EmailTwoFactorCodeNotification::class);
    }

    public function test_requesting_email_code_stores_only_hashed_cache_state(): void
    {
        Notification::fake();

        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('filament.admin.2fa.user_id', $user->getKey());

        Livewire::test(TwoFactorChallenge::class)
            ->call('sendEmailCode');

        $challengeId = session('filament.admin.2fa.email_challenge_id');
        $this->assertNotNull($challengeId);

        $service = app(EmailTwoFactorCodeService::class);
        $cacheKey = $service->cacheKey($challengeId);
        $payload = Cache::get($cacheKey);

        $this->assertNotNull($payload);
        $this->assertArrayHasKey('user_id', $payload);
        $this->assertArrayHasKey('code_hash', $payload);
        $this->assertArrayHasKey('expires_at', $payload);
        $this->assertSame($user->getKey(), $payload['user_id']);

        // The stored value must be a bcrypt hash, not a plaintext 6-digit code
        $this->assertGreaterThan(6, strlen($payload['code_hash']));
        $this->assertStringStartsWith('$2y$', $payload['code_hash']);

        // The plaintext code must NOT be stored in the session
        $sessionData = json_encode(session()->all());
        $this->assertStringNotContainsString('code_hash', $sessionData);
    }

    public function test_valid_email_code_logs_in_clears_session_keys_and_cache(): void
    {
        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $challengeId = (string) Str::uuid();
        $plainCode = '123456';

        $service = app(EmailTwoFactorCodeService::class);
        $cacheKey = $service->cacheKey($challengeId);

        Cache::put($cacheKey, [
            'user_id' => $user->getKey(),
            'code_hash' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], 600);

        session()->put('filament.admin.2fa.user_id', $user->getKey());
        session()->put('filament.admin.2fa.remember', false);
        session()->put('filament.admin.2fa.email_challenge_id', $challengeId);

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.email_code', $plainCode)
            ->call('submit')
            ->assertRedirect(Filament::getUrl());

        $this->assertAuthenticatedAs($user);
        $this->assertNull(session('filament.admin.2fa.user_id'));
        $this->assertNull(session('filament.admin.2fa.remember'));
        $this->assertNull(session('filament.admin.2fa.email_challenge_id'));
        $this->assertNull(Cache::get($cacheKey));
    }

    public function test_invalid_email_code_shows_validation_error_and_keeps_session(): void
    {
        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $challengeId = (string) Str::uuid();

        $service = app(EmailTwoFactorCodeService::class);
        $cacheKey = $service->cacheKey($challengeId);

        Cache::put($cacheKey, [
            'user_id' => $user->getKey(),
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], 600);

        session()->put('filament.admin.2fa.user_id', $user->getKey());
        session()->put('filament.admin.2fa.email_challenge_id', $challengeId);

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.email_code', '999999')
            ->call('submit')
            ->assertHasErrors(['data.email_code']);

        $this->assertGuest();
        $this->assertSame($user->getKey(), session('filament.admin.2fa.user_id'));
    }

    public function test_expired_email_code_fails_and_clears_challenge_id(): void
    {
        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $challengeId = (string) Str::uuid();

        session()->put('filament.admin.2fa.user_id', $user->getKey());
        session()->put('filament.admin.2fa.email_challenge_id', $challengeId);

        // Cache is empty — simulates expiry

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.email_code', '123456')
            ->call('submit')
            ->assertHasErrors(['data.email_code']);

        $this->assertGuest();
        $this->assertNull(session('filament.admin.2fa.email_challenge_id'));
        $this->assertNotNull(session('filament.admin.2fa.user_id'));
    }

    public function test_reused_email_code_fails_after_successful_verification(): void
    {
        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $challengeId = (string) Str::uuid();
        $plainCode = '123456';

        $service = app(EmailTwoFactorCodeService::class);
        $cacheKey = $service->cacheKey($challengeId);

        Cache::put($cacheKey, [
            'user_id' => $user->getKey(),
            'code_hash' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], 600);

        session()->put('filament.admin.2fa.user_id', $user->getKey());
        session()->put('filament.admin.2fa.remember', false);
        session()->put('filament.admin.2fa.email_challenge_id', $challengeId);

        // First submission — succeeds and logs in
        Livewire::test(TwoFactorChallenge::class)
            ->set('data.email_code', $plainCode)
            ->call('submit');

        $this->assertAuthenticatedAs($user);
        $this->assertNull(Cache::get($cacheKey));

        // Reset auth state and session for second attempt
        Auth()->logout();
        session()->put('filament.admin.2fa.user_id', $user->getKey());
        session()->put('filament.admin.2fa.email_challenge_id', $challengeId);

        // Second submission — cache is gone, must fail
        Livewire::test(TwoFactorChallenge::class)
            ->set('data.email_code', $plainCode)
            ->call('submit')
            ->assertHasErrors(['data.email_code']);

        $this->assertGuest();
    }

    public function test_sending_second_email_code_invalidates_first(): void
    {
        Notification::fake();

        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('filament.admin.2fa.user_id', $user->getKey());

        // First send
        Livewire::test(TwoFactorChallenge::class)
            ->call('sendEmailCode');

        $firstChallengeId = session('filament.admin.2fa.email_challenge_id');
        $this->assertNotNull($firstChallengeId);

        $service = app(EmailTwoFactorCodeService::class);
        $firstCacheKey = $service->cacheKey($firstChallengeId);
        $this->assertNotNull(Cache::get($firstCacheKey));

        // Second send
        Livewire::test(TwoFactorChallenge::class)
            ->call('sendEmailCode');

        $secondChallengeId = session('filament.admin.2fa.email_challenge_id');
        $this->assertNotNull($secondChallengeId);
        $this->assertNotSame($firstChallengeId, $secondChallengeId);

        // First challenge cache entry must be cleared
        $this->assertNull(Cache::get($firstCacheKey));

        // Second challenge cache entry must exist
        $secondCacheKey = $service->cacheKey($secondChallengeId);
        $this->assertNotNull(Cache::get($secondCacheKey));
    }

    public function test_unverified_email_user_cannot_request_email_code(): void
    {
        Notification::fake();

        $user = $this->createUserWithTotp(['email_verified_at' => null]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('filament.admin.2fa.user_id', $user->getKey());

        Livewire::test(TwoFactorChallenge::class)
            ->call('sendEmailCode')
            ->assertHasErrors(['data.email_code']);

        Notification::assertNothingSent();
    }

    public function test_unverified_email_user_cannot_verify_email_code(): void
    {
        $user = $this->createUserWithTotp(['email_verified_at' => null]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $challengeId = (string) Str::uuid();

        $service = app(EmailTwoFactorCodeService::class);
        $cacheKey = $service->cacheKey($challengeId);

        Cache::put($cacheKey, [
            'user_id' => $user->getKey(),
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], 600);

        session()->put('filament.admin.2fa.user_id', $user->getKey());
        session()->put('filament.admin.2fa.email_challenge_id', $challengeId);

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.email_code', '123456')
            ->call('submit')
            ->assertHasErrors(['data.email_code']);

        $this->assertGuest();
    }

    public function test_submitting_multiple_credentials_fails_validation(): void
    {
        $this->mockTotpProvider(true);

        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('filament.admin.2fa.user_id', $user->getKey());

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', '123456')
            ->set('data.email_code', '654321')
            ->call('submit')
            ->assertHasErrors(['data.code']);

        $this->assertGuest();
    }

    public function test_submitting_no_credentials_fails_validation(): void
    {
        $user = $this->createUserWithTotp();
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        session()->put('filament.admin.2fa.user_id', $user->getKey());

        Livewire::test(TwoFactorChallenge::class)
            ->call('submit')
            ->assertHasErrors(['data.code']);

        $this->assertGuest();
    }
}
