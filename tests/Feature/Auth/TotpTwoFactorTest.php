<?php

use App\Models\User;
use Laravel\Fortify\Features;
use PragmaRX\Google2FA\Google2FA;

test('totp two factor authentication setup generates qr code', function () {
    $this->actingAs($user = User::factory()->create());

    $this->withSession(['auth.password_confirmed_at' => time()]);

    // Enable 2FA
    $response = $this->post('/web/user/two-factor-authentication');

    $user = $user->fresh();
    expect($user->two_factor_secret)->not->toBeNull();

    // Test QR code generation
    $qrResponse = $this->get('/web/user/two-factor-qr-code');
    $qrResponse->assertOk();
    expect($qrResponse->headers->get('content-type'))->toContain('application/json');

    $qrData = json_decode($qrResponse->getContent(), true);
    expect($qrData)->toHaveKey('svg');
    expect($qrData['svg'])->toContain('<svg');
})->skip(function () {
    return ! Features::canManageTwoFactorAuthentication();
}, 'Two factor authentication is not enabled.');

test('totp two factor authentication generates recovery codes', function () {
    $this->actingAs($user = User::factory()->create());

    $this->withSession(['auth.password_confirmed_at' => time()]);

    // Enable 2FA
    $response = $this->post('/web/user/two-factor-authentication');

    $user = $user->fresh();
    expect($user->recoveryCodes())->toHaveCount(8);

    // Get recovery codes
    $codesResponse = $this->get('/web/user/two-factor-recovery-codes');
    $codesResponse->assertOk();

    $codes = json_decode($codesResponse->getContent(), true);
    expect($codes)->toHaveCount(8);
})->skip(function () {
    return ! Features::canManageTwoFactorAuthentication();
}, 'Two factor authentication is not enabled.');

test('totp two factor authentication can verify valid codes', function () {
    $this->actingAs($user = User::factory()->create());

    $this->withSession(['auth.password_confirmed_at' => time()]);

    // Enable 2FA
    $this->post('/web/user/two-factor-authentication');

    $user = $user->fresh();

    // Generate a valid TOTP code
    $google2fa = new Google2FA;
    $secret = decrypt($user->two_factor_secret);
    $validCode = $google2fa->getCurrentOtp($secret);

    // Confirm 2FA with valid code
    $response = $this->post('/web/user/confirmed-two-factor-authentication', [
        'code' => $validCode,
    ]);

    $response->assertRedirect();

    $user = $user->fresh();
    expect($user->two_factor_confirmed_at)->not->toBeNull();
})->skip(function () {
    return ! Features::canManageTwoFactorAuthentication();
}, 'Two factor authentication is not enabled.');

test('totp two factor authentication requires password confirmation', function () {
    $this->actingAs($user = User::factory()->create());

    // Try to enable 2FA without password confirmation
    $response = $this->post('/web/user/two-factor-authentication');

    $response->assertRedirect('/web/user/confirm-password');
})->skip(function () {
    return ! Features::canManageTwoFactorAuthentication();
}, 'Two factor authentication is not enabled.');

test('two factor secret key can be retrieved', function () {
    $this->actingAs($user = User::factory()->create());

    $this->withSession(['auth.password_confirmed_at' => time()]);

    // Enable 2FA
    $this->post('/web/user/two-factor-authentication');

    $user = $user->fresh();

    // Get secret key
    $secretResponse = $this->get('/web/user/two-factor-secret-key');
    $secretResponse->assertOk();

    $secretData = json_decode($secretResponse->getContent(), true);
    expect($secretData['secretKey'])->toBeString();
    expect(strlen($secretData['secretKey']))->toBeGreaterThan(0);
})->skip(function () {
    return ! Features::canManageTwoFactorAuthentication();
}, 'Two factor authentication is not enabled.');
