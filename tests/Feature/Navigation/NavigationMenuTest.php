<?php

namespace Tests\Feature\Navigation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles and permissions exist
        $this->artisan('db:seed', ['--class' => 'ProductionDataSeeder']);
    }

    public function test_unauthenticated_users_do_not_see_administration_menu(): void
    {
        $response = $this->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertDontSee('Administration');
        $response->assertDontSee('User Management');
    }

    public function test_authenticated_users_without_admin_permissions_do_not_see_administration_menu(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Regular User');

        $response = $this->actingAs($user)->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertDontSee('Administration');
    }

    public function test_admin_users_see_administration_menu(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $response = $this->actingAs($admin)->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertSee('Administration');
        $response->assertSee('User Management');
        $response->assertSee('System Management');
    }

    public function test_administration_dropdown_contains_user_management_link(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $response = $this->actingAs($admin)->get(route('web.welcome'));

        $response->assertStatus(200);

        // Check that the User Management link is properly formed
        $response->assertSee(route('admin.users.index'), false);
    }

    public function test_api_documentation_link_hidden_for_unauthenticated_users(): void
    {
        $response = $this->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertDontSee('API Documentation');
    }

    public function test_api_documentation_link_visible_for_authenticated_users(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Regular User');

        $response = $this->actingAs($user)->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertSee('API Documentation');
    }

    public function test_responsive_navigation_includes_user_management_for_admins(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $response = $this->actingAs($admin)->get(route('web.welcome'));

        $response->assertStatus(200);

        // The responsive navigation should also have the User Management link
        $content = $response->getContent();
        $userManagementLinkCount = substr_count($content, route('admin.users.index'));

        // Should appear at least twice: once in desktop dropdown, once in responsive menu
        $this->assertGreaterThanOrEqual(2, $userManagementLinkCount);
    }
}
