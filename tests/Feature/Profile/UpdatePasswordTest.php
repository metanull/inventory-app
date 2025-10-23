<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\Http\Livewire\UpdatePasswordForm;
use Livewire\Livewire;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_password_form_shows_2fa_field_when_user_has_totp_enabled(): void
    {
        // Enable TOTP for user
        $this->user->forceFill([
            'two_factor_secret' => encrypt('test_secret'),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($this->user)->get(route('web.profile.show'));

        $response->assertOk();
        $response->assertSee('Two-Factor Authentication Code');
        $response->assertSee('Enter your authenticator app code to confirm the password change');
    }

    public function test_password_form_does_not_show_2fa_field_when_2fa_disabled(): void
    {
        $response = $this->actingAs($this->user)->get(route('web.profile.show'));

        $response->assertOk();
        $response->assertDontSee('Two-Factor Authentication Code');
    }

    public function test_password_can_be_updated_via_livewire(): void
    {
        $this->actingAs($this->user);

        Livewire::test(UpdatePasswordForm::class)
            ->set('state', [
                'current_password' => 'password',
                'password' => 'new-password123',
                'password_confirmation' => 'new-password123',
            ])
            ->call('updatePassword');

        $this->assertTrue(Hash::check('new-password123', $this->user->fresh()->password));
    }
}
