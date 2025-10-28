<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Profile pages only require authentication, no specific permissions
        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($this->user);
    }

    public function test_profile_page_can_be_accessed_under_web_prefix(): void
    {
        $response = $this->get(route('web.profile.show'));
        $response->assertOk();

        // Check that the page contains profile content
        $response->assertSee('Profile');
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
    }

    public function test_old_profile_route_still_works_for_backward_compatibility(): void
    {
        $response = $this->get(route('web.profile.show'));
        $response->assertOk();
    }

    public function test_profile_page_shows_two_factor_authentication_section(): void
    {
        $response = $this->get(route('web.profile.show'));
        $response->assertOk();

        // Check that 2FA section is present
        $response->assertSee('Two Factor Authentication');
    }
}
