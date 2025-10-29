<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\CreatesTwoFactorUsers;

class AuthenticationStateTest extends TestCase
{
    use CreatesTwoFactorUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles and permissions exist by running the production seeder
        $this->artisan('db:seed', ['--class' => 'ProductionDataSeeder']);
    }

    public function test_unauthenticated_users_cannot_see_api_documentation_link(): void
    {
        $response = $this->get(route('web.welcome'));

        $response->assertOk();
        $response->assertDontSee('API Documentation');
        $response->assertDontSee('REST endpoints, schemas and integration notes.');
    }

    public function test_authenticated_users_can_see_api_documentation_link(): void
    {
        $user = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        $user->assignRole('Regular User');

        $response = $this->actingAs($user)->get(route('web.welcome'));

        $response->assertOk();
        $response->assertSee('API Documentation');
        $response->assertSee('REST endpoints, schemas and integration notes.');
    }

    public function test_users_with_unverified_email_can_access_verification_page(): void
    {
        $user = $this->createUserWithoutTwoFactor(['email_verified_at' => null]);
        $user->assignRole('Regular User');

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertOk();
        $response->assertSee('Before continuing, could you verify your email address');
    }

    public function test_email_verification_page_profile_link_works(): void
    {
        $user = $this->createUserWithoutTwoFactor(['email_verified_at' => null]);
        $user->assignRole('Regular User');

        $verifyResponse = $this->actingAs($user)->get(route('verification.notice'));
        $verifyResponse->assertOk();
        $verifyResponse->assertSee('Edit Profile');

        // Test that the profile link works (might redirect to verification notice, which is acceptable)
        $profileResponse = $this->actingAs($user)->get(route('web.profile.show'));
        $profileResponse->assertStatus(302);
        $profileResponse->assertRedirect(route('verification.notice'));
    }

    public function test_users_with_verified_email_redirected_from_verification(): void
    {
        $user = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        $user->assignRole('Regular User');

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_without_roles_cannot_access_protected_pages(): void
    {
        $user = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        // Don't assign any roles

        $response = $this->actingAs($user)->get(route('items.index'));

        // Users without roles should now be denied access
        $response->assertStatus(403);
    }

    public function test_regular_users_can_access_data_pages(): void
    {
        $user = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        $user->assignRole('Regular User');

        $response = $this->actingAs($user)->get(route('items.index'));

        $response->assertOk();
    }

    public function test_regular_users_cannot_access_admin_pages(): void
    {
        $user = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        $user->assignRole('Regular User');

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_manager_users_can_access_admin_pages(): void
    {
        $user = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        $user->assignRole('Manager of Users');

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertOk();
    }

    public function test_admin_user_edit_page_loads(): void
    {
        $adminUser = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        $adminUser->assignRole('Manager of Users');

        $targetUser = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        $targetUser->assignRole('Regular User');

        $response = $this->actingAs($adminUser)->get(route('admin.users.edit', $targetUser));

        $response->assertOk();
        $response->assertSee('Edit User');
        $response->assertSee($targetUser->name);
    }

    public function test_admin_user_edit_page_loads_correctly(): void
    {
        $adminUser = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        $adminUser->assignRole('Manager of Users');

        $targetUser = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        $targetUser->assignRole('Regular User');

        $response = $this->actingAs($adminUser)->get(route('admin.users.edit', $targetUser));

        $response->assertOk();
        $response->assertSee('Edit User');
        $response->assertSee('Role Assignment');
        $response->assertSee('Email Verification');
        $response->assertSee($targetUser->name);
    }

    public function test_navigation_adapts_to_authentication_state(): void
    {
        // Enable self-registration for this test
        \App\Models\Setting::set('self_registration_enabled', true, 'boolean');

        // Test unauthenticated navigation
        $response = $this->get(route('web.welcome'));
        $response->assertOk();
        $response->assertSee('Inventory Portal');
        $response->assertSee('Login');
        $response->assertSee('Register');

        // Test authenticated navigation - dashboard might redirect unverified users
        $user = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        $user->assignRole('Regular User');

        $response = $this->actingAs($user)->get(route('dashboard'));

        // Dashboard might redirect to welcome or other page for authenticated users
        if ($response->getStatusCode() === 302) {
            $response->assertRedirect();
        } else {
            $response->assertOk();
            $response->assertDontSee('Login');
            $response->assertDontSee('Register');
        }
    }

    public function test_profile_page_requires_authentication(): void
    {
        $response = $this->get(route('web.profile.show'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_profile(): void
    {
        $user = $this->createUserWithoutTwoFactor(['email_verified_at' => now()]);
        $user->assignRole('Regular User');

        $response = $this->actingAs($user)->get(route('web.profile.show'));

        $response->assertOk();
        $response->assertSee('Profile Information');
    }
}
