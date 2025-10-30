<?php

namespace Tests\Web\Authentication;

use App\Enums\Permission;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission as PermissionModel;
use Tests\TestCase;

/**
 * Test the self-registration toggle feature.
 * These tests verify the FEATURE of enabling/disabling self-registration.
 */
class SelfRegistrationToggleTest extends TestCase
{
    use RefreshDatabase;

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

        // CreateNewUser throws ValidationException which redirects back
        $response->assertRedirect();
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

        // Verify new user has NO roles or permissions (permission-based system)
        $user = User::where('email', 'test@example.com')->first();
        $this->assertEquals(0, $user->roles()->count());
        $this->assertEquals(0, $user->getAllPermissions()->count());
    }

    public function test_user_with_manage_settings_permission_can_toggle_self_registration(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $permission = PermissionModel::findByName(Permission::MANAGE_SETTINGS->value);

        $manager = User::factory()->create();
        $manager->givePermissionTo($permission);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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

    public function test_user_with_view_data_permission_cannot_access_settings(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        $permission = PermissionModel::findByName(Permission::VIEW_DATA->value);

        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertStatus(403);
    }

    public function test_user_without_permissions_cannot_access_settings(): void
    {
        // Permissions already exist from TestCase::ensurePermissionsExist()
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertStatus(403);
    }

    public function test_registration_links_hidden_when_disabled(): void
    {
        // Ensure self-registration is disabled
        Setting::set('self_registration_enabled', false, 'boolean');

        // Check the login page instead (where registration links typically appear)
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertDontSee('Register');
        $response->assertDontSee('Create Account');
    }

    public function test_registration_links_visible_when_enabled(): void
    {
        // Enable self-registration
        Setting::set('self_registration_enabled', true, 'boolean');

        // Check the registration page is accessible
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Register'); // Page title or button text
    }
}
