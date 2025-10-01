<?php

use App\Models\EmailTwoFactorCode;
use App\Models\User;
use App\Notifications\EmailTwoFactorCode as EmailTwoFactorCodeNotification;
use App\Services\EmailTwoFactorService;
use Illuminate\Support\Facades\Notification;

test('email two factor code can be generated and sent', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_2fa_enabled' => true,
    ]);

    $service = new EmailTwoFactorService;
    $emailCode = $service->generateAndSendCode($user);

    expect($emailCode)->toBeInstanceOf(EmailTwoFactorCode::class);
    expect($emailCode->user_id)->toBe($user->id);
    expect($emailCode->code)->toHaveLength(6);
    expect($emailCode->isValid())->toBeTrue();

    // Check notification was sent
    Notification::assertSentTo($user, EmailTwoFactorCodeNotification::class);
});

test('email two factor code can be verified', function () {
    $user = User::factory()->create([
        'email_2fa_enabled' => true,
    ]);

    $code = EmailTwoFactorCode::factory()->create([
        'user_id' => $user->id,
        'code' => '123456',
    ]);

    $service = new EmailTwoFactorService;
    $result = $service->verifyCode($user, '123456');

    expect($result)->toBeTrue();
    expect($code->fresh()->isUsed())->toBeTrue();
});

test('email two factor code verification fails with invalid code', function () {
    $user = User::factory()->create([
        'email_2fa_enabled' => true,
    ]);

    $service = new EmailTwoFactorService;
    $result = $service->verifyCode($user, '000000');

    expect($result)->toBeFalse();
});

test('user can enable and disable email two factor', function () {
    $user = User::factory()->create();

    expect($user->hasEmailTwoFactorEnabled())->toBeFalse();

    $user->enableEmailTwoFactor();
    expect($user->fresh()->hasEmailTwoFactorEnabled())->toBeTrue();

    $user->disableEmailTwoFactor();
    expect($user->fresh()->hasEmailTwoFactorEnabled())->toBeFalse();
});

test('user can set preferred 2fa method', function () {
    $user = User::factory()->create();

    $user->setPreferred2faMethod('email');
    expect($user->fresh()->getPreferred2faMethod())->toBe('email');

    $user->setPreferred2faMethod('totp');
    expect($user->fresh()->getPreferred2faMethod())->toBe('totp');

    $user->setPreferred2faMethod('both');
    expect($user->fresh()->getPreferred2faMethod())->toBe('both');
});

test('user 2fa availability methods work correctly', function () {
    $user = User::factory()->create([
        'email_2fa_enabled' => true,
        'preferred_2fa_method' => 'email',
    ]);

    // Enable TOTP
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_confirmed_at' => now(),
    ])->save();

    expect($user->hasTotpEnabled())->toBeTrue();
    expect($user->hasEmailTwoFactorEnabled())->toBeTrue();
    expect($user->hasTwoFactorEnabled())->toBeTrue();

    // Test method availability based on preference
    expect($user->canUseEmailFor2fa())->toBeTrue();
    expect($user->canUseTotpFor2fa())->toBeFalse(); // preference is email only

    $user->setPreferred2faMethod('both');
    expect($user->canUseEmailFor2fa())->toBeTrue();
    expect($user->canUseTotpFor2fa())->toBeTrue();

    expect($user->getAvailable2faMethods())->toContain('totp');
    expect($user->getAvailable2faMethods())->toContain('email');
});

test('user can generate and verify email code through model methods', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_2fa_enabled' => true,
        'preferred_2fa_method' => 'email',
    ]);

    $emailCode = $user->generateEmailTwoFactorCode();

    expect($emailCode)->toBeInstanceOf(EmailTwoFactorCode::class);
    expect($emailCode->user_id)->toBe($user->id);

    $result = $user->verifyEmailTwoFactorCode($emailCode->code);
    expect($result)->toBeTrue();

    Notification::assertSentTo($user, EmailTwoFactorCodeNotification::class);
});

test('email code factory states work correctly', function () {
    $validCode = EmailTwoFactorCode::factory()->valid()->create();
    expect($validCode->isValid())->toBeTrue();

    $expiredCode = EmailTwoFactorCode::factory()->expired()->create();
    expect($expiredCode->isExpired())->toBeTrue();
    expect($expiredCode->isValid())->toBeFalse();

    $usedCode = EmailTwoFactorCode::factory()->used()->create();
    expect($usedCode->isUsed())->toBeTrue();
    expect($usedCode->isValid())->toBeFalse();
});

test('service rate limiting works', function () {
    $user = User::factory()->create([
        'email_2fa_enabled' => true,
    ]);

    // Create 3 recent codes (at the rate limit)
    EmailTwoFactorCode::factory()->count(3)->create([
        'user_id' => $user->id,
        'created_at' => now()->subMinutes(30), // Within the hour
    ]);

    $service = new EmailTwoFactorService;

    expect(fn () => $service->generateAndSendCode($user))
        ->toThrow(Exception::class, 'Too many 2FA codes requested');
});

test('cleanup expired codes works', function () {
    // Create some expired codes
    EmailTwoFactorCode::factory()->count(3)->expired()->create();

    // Create some used codes
    EmailTwoFactorCode::factory()->count(2)->used()->create();

    // Create valid codes
    EmailTwoFactorCode::factory()->count(2)->valid()->create();

    expect(EmailTwoFactorCode::count())->toBe(7);

    $cleaned = EmailTwoFactorService::cleanupExpiredCodes();
    expect($cleaned)->toBe(5); // 3 expired + 2 used
    expect(EmailTwoFactorCode::count())->toBe(2); // Only valid ones remain
});
