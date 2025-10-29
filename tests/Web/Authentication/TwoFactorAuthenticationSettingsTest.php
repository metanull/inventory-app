<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Http\Livewire\TwoFactorAuthenticationForm;
use Livewire\Livewire;
use Tests\TestCase;

class TwoFactorAuthenticationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // 2FA management only requires authentication, no specific permissions
        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($this->user);
    }

    public function test_two_factor_authentication_can_be_enabled(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        $this->withSession(['auth.password_confirmed_at' => time()]);

        Livewire::test(TwoFactorAuthenticationForm::class)
            ->call('enableTwoFactorAuthentication');

        $updatedUser = $this->user->fresh();

        $this->assertNotNull($updatedUser->two_factor_secret);
        $this->assertCount(8, $updatedUser->recoveryCodes());
    }

    public function test_recovery_codes_can_be_regenerated(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        $this->withSession(['auth.password_confirmed_at' => time()]);

        $component = Livewire::test(TwoFactorAuthenticationForm::class)
            ->call('enableTwoFactorAuthentication')
            ->call('regenerateRecoveryCodes');

        $updatedUser = $this->user->fresh();

        $component->call('regenerateRecoveryCodes');

        $this->assertCount(8, $updatedUser->recoveryCodes());
        $this->assertCount(8, array_diff($updatedUser->recoveryCodes(), $this->user->fresh()->recoveryCodes()));
    }

    public function test_two_factor_authentication_can_be_disabled(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        $this->withSession(['auth.password_confirmed_at' => time()]);

        $component = Livewire::test(TwoFactorAuthenticationForm::class)
            ->call('enableTwoFactorAuthentication');

        $this->assertNotNull($this->user->fresh()->two_factor_secret);

        $component->call('disableTwoFactorAuthentication');

        $this->assertNull($this->user->fresh()->two_factor_secret);
    }
}
