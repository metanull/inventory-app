<?php

use App\Models\User;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

test('mobile api can authenticate user without 2fa', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/mobile/acquire-token', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Test Device',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'token',
        'user' => ['id', 'name', 'email', 'two_factor_enabled'],
    ]);
    $response->assertJson([
        'user' => [
            'two_factor_enabled' => false,
        ],
    ]);
});

test('mobile api requires 2fa when user has totp enabled', function () {
    $user = User::factory()->create();

    // Enable TOTP for user
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $response = $this->postJson('/api/mobile/acquire-token', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Test Device',
    ]);

    $response->assertStatus(202);
    $response->assertJson([
        'requires_two_factor' => true,
        'available_methods' => ['totp'],
        'primary_method' => 'totp',
    ]);
});

test('mobile api can verify totp 2fa code', function () {
    $user = User::factory()->create();

    // Enable TOTP
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_confirmed_at' => now(),
    ])->save();

    // Mock the TOTP provider to return true
    $this->mock(TwoFactorAuthenticationProvider::class, function ($mock) {
        $mock->shouldReceive('verify')->once()->andReturn(true);
    });

    $response = $this->postJson('/api/mobile/verify-two-factor', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Test Device',
        'code' => '123456',
        'method' => 'totp',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'token',
        'user' => ['id', 'name', 'email', 'two_factor_enabled', 'two_factor_method'],
    ]);
    $response->assertJson([
        'user' => [
            'two_factor_enabled' => true,
            'two_factor_method' => 'totp',
        ],
    ]);
});

test('mobile api rejects invalid totp 2fa code', function () {
    $user = User::factory()->create();

    // Enable TOTP
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_confirmed_at' => now(),
    ])->save();

    // Mock the TOTP provider to return false
    $this->mock(TwoFactorAuthenticationProvider::class, function ($mock) {
        $mock->shouldReceive('verify')->once()->andReturn(false);
    });

    $response = $this->postJson('/api/mobile/verify-two-factor', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Test Device',
        'code' => '000000',
        'method' => 'totp',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['code']);
});

test('mobile api fails with invalid credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/mobile/acquire-token', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'Test Device',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('mobile api can wipe tokens after authentication', function () {
    $user = User::factory()->create();

    // Create some existing tokens
    $user->createToken('Old Device 1');
    $user->createToken('Old Device 2');

    expect($user->tokens()->count())->toBe(2);

    $response = $this->postJson('/api/mobile/acquire-token', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'New Device',
        'wipe_tokens' => true,
    ]);

    $response->assertStatus(201);

    // Should have only the new token
    expect($user->fresh()->tokens()->count())->toBe(1);
    expect($user->fresh()->tokens()->first()->name)->toBe('New Device');
});
