<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Profile management only requires authentication, no specific permissions
        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($this->user);
    }

    public function test_current_profile_information_is_available(): void
    {
        $component = Livewire::test(UpdateProfileInformationForm::class);

        $this->assertEquals($this->user->name, $component->state['name']);
        $this->assertEquals($this->user->email, $component->state['email']);
    }

    public function test_profile_information_can_be_updated(): void
    {
        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', ['name' => 'Test Name', 'email' => 'test@example.com'])
            ->call('updateProfileInformation');

        $updatedUser = $this->user->fresh();
        $this->assertEquals('Test Name', $updatedUser->name);
        $this->assertEquals('test@example.com', $updatedUser->email);
    }
}
