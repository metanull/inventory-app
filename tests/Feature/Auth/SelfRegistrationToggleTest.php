<?php

namespace Tests\Feature\Auth;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SelfRegistrationToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_self_registration_is_disabled_by_default(): void
    {
        $this->assertFalse(Setting::get('self_registration_enabled', false));
    }

    public function test_registration_form_is_blocked_when_disabled(): void
    {
        // Ensure self-registration is disabled
        Setting::set('self_registration_enabled', false, 'boolean');

        $response = $this->get(route('register'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Self-registration is currently disabled. Please contact an administrator.');
    }

    public function test_registration_form_is_accessible_when_enabled(): void
    {
        // Enable self-registration
        Setting::set('self_registration_enabled', true, 'boolean');

        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Register');
    }

    public function test_registration_fails_when_disabled_via_api(): void
    {
        // Ensure self-registration is disabled
        Setting::set('self_registration_enabled', false, 'boolean');

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors(['registration']);
    }

    public function test_registration_succeeds_when_enabled(): void
    {
        // Enable self-registration
        Setting::set('self_registration_enabled', true, 'boolean');

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);

        // Verify user gets "Non-verified users" role
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('Non-verified users'));
    }

    public function test_manager_can_toggle_self_registration_setting(): void
    {
        // Create a manager user
        /** @var User $manager */
        $manager = User::factory()->create();
        $managerRole = Role::findByName('Manager of Users');
        $manager->assignRole($managerRole);

        // Test enabling self-registration
        $response = $this->actingAs($manager)->put(route('settings.update'), [
            'self_registration_enabled' => true,
        ]);

        $response->assertRedirect();
        $this->assertTrue(Setting::get('self_registration_enabled', false));

        // Test disabling self-registration
        $response = $this->actingAs($manager)->put(route('settings.update'), [
            'self_registration_enabled' => false,
        ]);

        $response->assertRedirect();
        $this->assertFalse(Setting::get('self_registration_enabled', false));
    }

    public function test_regular_user_cannot_access_settings(): void
    {
        // Create a regular user
        /** @var User $user */
        $user = User::factory()->create();
        $regularRole = Role::findByName('Regular User');
        $user->assignRole($regularRole);

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertStatus(403);
    }

    public function test_non_verified_user_cannot_access_settings(): void
    {
        // Create a non-verified user
        /** @var User $user */
        $user = User::factory()->create();
        $nonVerifiedRole = Role::findByName('Non-verified users');
        $user->assignRole($nonVerifiedRole);

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertStatus(403);
    }

    public function test_registration_links_hidden_when_disabled(): void
    {
        // Ensure self-registration is disabled
        Setting::set('self_registration_enabled', false, 'boolean');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Register');
        $response->assertDontSee('Create Account');
    }

    public function test_registration_links_visible_when_enabled(): void
    {
        // Enable self-registration
        Setting::set('self_registration_enabled', true, 'boolean');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Register');
    }
}
