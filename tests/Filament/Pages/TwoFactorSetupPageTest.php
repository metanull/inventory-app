<?php

namespace Tests\Filament\Pages;

use App\Enums\Permission;
use App\Filament\Auth\TwoFactorSetup;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class TwoFactorSetupPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function createUnenrolledUserWithPanelAccess(): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        return $user;
    }

    protected function mockTotpProvider(bool $shouldVerify): void
    {
        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('generateSecretKey')->andReturn('JBSWY3DPEHPK3PXP');
        $mock->shouldReceive('qrCodeUrl')->andReturn('otpauth://totp/test:user@example.com?secret=JBSWY3DPEHPK3PXP');
        $mock->shouldReceive('verify')->andReturn($shouldVerify);

        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);
    }

    public function test_unenrolled_user_sees_qr_code_and_setup_key(): void
    {
        $this->mockTotpProvider(true);

        $user = $this->createUnenrolledUserWithPanelAccess();

        Livewire::actingAs($user)
            ->test(TwoFactorSetup::class)
            ->assertSet('step', 'setup')
            ->assertSee('JBSWY3DPEHPK3PXP');
    }

    public function test_confirming_with_valid_code_sets_confirmed_at_and_shows_recovery_codes(): void
    {
        $this->mockTotpProvider(true);

        $user = $this->createUnenrolledUserWithPanelAccess();

        Livewire::actingAs($user)
            ->test(TwoFactorSetup::class)
            ->set('data.code', '123456')
            ->call('confirm')
            ->assertSet('step', 'recovery-codes')
            ->assertSee('Continue to Dashboard');

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
    }

    public function test_confirming_with_invalid_code_shows_error_and_leaves_unconfirmed(): void
    {
        $this->mockTotpProvider(false);

        $user = $this->createUnenrolledUserWithPanelAccess();

        Livewire::actingAs($user)
            ->test(TwoFactorSetup::class)
            ->set('data.code', '000000')
            ->call('confirm')
            ->assertHasErrors(['data.code'])
            ->assertSet('step', 'setup');

        $user->refresh();
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_already_enrolled_user_visiting_setup_page_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create([
            'two_factor_confirmed_at' => now(),
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::actingAs($user)
            ->test(TwoFactorSetup::class)
            ->assertRedirect(Filament::getUrl());
    }

    public function test_complete_action_redirects_to_dashboard(): void
    {
        $this->mockTotpProvider(true);

        $user = $this->createUnenrolledUserWithPanelAccess();

        $component = Livewire::actingAs($user)
            ->test(TwoFactorSetup::class)
            ->set('data.code', '123456')
            ->call('confirm');

        $component->assertSet('step', 'recovery-codes');

        $component->call('complete')
            ->assertRedirect(Filament::getUrl());
    }
}
