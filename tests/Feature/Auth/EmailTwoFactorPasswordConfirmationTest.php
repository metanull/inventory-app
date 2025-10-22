<?php

namespace Tests\Feature\Auth;

use App\Livewire\Profile\EmailTwoFactorForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Test that Email 2FA component properly requires password confirmation.
 *
 * Note: The password confirmation is handled by the <x-confirms-password> component
 * in the blade view. These tests verify that the Livewire methods work correctly
 * when password confirmation is present.
 */
class EmailTwoFactorPasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_enabling_email_2fa_works_with_password_confirmation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // With password confirmation session
        $this->withSession(['auth.password_confirmed_at' => time()]);

        $component = Livewire::test(EmailTwoFactorForm::class)
            ->call('enableEmailTwoFactor');

        expect($user->fresh()->hasEmailTwoFactorEnabled())->toBeTrue();
        $component->assertSet('emailTwoFactorEnabled', true);
    }

    public function test_disabling_email_2fa_works_with_password_confirmation(): void
    {
        $user = User::factory()->create([
            'email_2fa_enabled' => true,
        ]);

        $this->actingAs($user);
        $this->withSession(['auth.password_confirmed_at' => time()]);

        $component = Livewire::test(EmailTwoFactorForm::class)
            ->call('disableEmailTwoFactor');

        expect($user->fresh()->hasEmailTwoFactorEnabled())->toBeFalse();
        $component->assertSet('emailTwoFactorEnabled', false);
    }

    public function test_sending_test_code_works_with_password_confirmation(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $user = User::factory()->create([
            'email_2fa_enabled' => true,
        ]);

        $this->actingAs($user);
        $this->withSession(['auth.password_confirmed_at' => time()]);

        $component = Livewire::test(EmailTwoFactorForm::class)
            ->call('sendTestEmailCode');

        $component->assertStatus(200);
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \App\Notifications\EmailTwoFactorCode::class
        );
    }

    public function test_updating_preference_works_with_password_confirmation(): void
    {
        $user = User::factory()->create([
            'email_2fa_enabled' => true,
        ]);

        // Enable TOTP for this user
        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($user);
        $this->withSession(['auth.password_confirmed_at' => time()]);

        $component = Livewire::test(EmailTwoFactorForm::class)
            ->set('preferred2faMethod', 'both')
            ->call('updatePreference');

        expect($user->fresh()->getPreferred2faMethod())->toBe('both');
    }

    public function test_email_2fa_component_is_properly_registered(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // The component should mount without errors
        $component = Livewire::test(EmailTwoFactorForm::class);

        $component->assertStatus(200);
        $component->assertSet('emailTwoFactorEnabled', false);
    }
}
