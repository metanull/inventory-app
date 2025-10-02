<?php

use App\Livewire\Profile\EmailTwoFactorForm;
use App\Models\User;
use Livewire\Livewire;

test('email two factor form component can be mounted', function () {
    $user = User::factory()->create([
        'email_2fa_enabled' => true,
        'preferred_2fa_method' => 'email',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(EmailTwoFactorForm::class);

    expect($component->get('emailTwoFactorEnabled'))->toBeTrue();
    expect($component->get('preferred2faMethod'))->toBe('email');
});

test('email two factor form can enable email 2fa', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(EmailTwoFactorForm::class)
        ->call('enableEmailTwoFactor');

    expect($user->fresh()->hasEmailTwoFactorEnabled())->toBeTrue();
    $component->assertSet('emailTwoFactorEnabled', true);
    $component->assertSet('showingEmailTest', true);
});

test('email two factor form can disable email 2fa', function () {
    $user = User::factory()->create([
        'email_2fa_enabled' => true,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(EmailTwoFactorForm::class)
        ->call('disableEmailTwoFactor');

    expect($user->fresh()->hasEmailTwoFactorEnabled())->toBeFalse();
    $component->assertSet('emailTwoFactorEnabled', false);
    $component->assertSet('showingEmailTest', false);
});

test('email two factor form can update preferences', function () {
    $user = User::factory()->create([
        'email_2fa_enabled' => true,
    ]);

    // Enable TOTP for this user to test both options
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->actingAs($user);

    $component = Livewire::test(EmailTwoFactorForm::class)
        ->set('preferred2faMethod', 'both')
        ->call('updatePreference');

    expect($user->fresh()->getPreferred2faMethod())->toBe('both');
});

test('email two factor form shows correct available preferences', function () {
    $user = User::factory()->create([
        'email_2fa_enabled' => true,
    ]);

    // Enable TOTP for this user
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->actingAs($user);

    $component = Livewire::test(EmailTwoFactorForm::class);

    $preferences = $component->get('availablePreferences');

    expect($preferences)->toHaveKey('totp');
    expect($preferences)->toHaveKey('email');
    expect($preferences)->toHaveKey('both');
});

test('email two factor form can send test code', function () {
    \Illuminate\Support\Facades\Notification::fake();

    $user = User::factory()->create([
        'email_2fa_enabled' => true,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(EmailTwoFactorForm::class)
        ->call('sendTestEmailCode');

    \Illuminate\Support\Facades\Notification::assertSentTo(
        $user,
        \App\Notifications\EmailTwoFactorCode::class
    );
});

test('email two factor form can verify test code', function () {
    $user = User::factory()->create([
        'email_2fa_enabled' => true,
    ]);

    // Create a valid code
    $code = \App\Models\EmailTwoFactorCode::factory()->create([
        'user_id' => $user->id,
        'code' => '123456',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(EmailTwoFactorForm::class)
        ->set('testEmailCode', '123456')
        ->call('verifyTestEmailCode');

    expect($code->fresh()->isUsed())->toBeTrue();
    $component->assertSet('showingEmailTest', false);
});
