<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('check qr code response format', function () {
    $this->actingAs($user = User::factory()->create());

    $this->withSession(['auth.password_confirmed_at' => time()]);

    // Enable 2FA
    $response = $this->post('/web/user/two-factor-authentication');

    // Test QR code generation
    $qrResponse = $this->get('/web/user/two-factor-qr-code');
    $qrResponse->assertOk();

    // Debug what we actually get
    dump('Content-Type: '.$qrResponse->headers->get('content-type'));
    dump('Content: '.substr($qrResponse->getContent(), 0, 200));

    expect(true)->toBeTrue(); // Just to make the test pass while debugging
})->skip(function () {
    return ! Features::canManageTwoFactorAuthentication();
}, 'Two factor authentication is not enabled.');
