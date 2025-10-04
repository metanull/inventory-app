<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NonVerifiedUserAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_non_verified_user_sees_account_under_review_message(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $nonVerifiedRole = Role::findByName('Non-verified users');
        $user->assignRole($nonVerifiedRole);

        $response = $this->actingAs($user)->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertSee('Account Under Review');
        $response->assertSee('Your account has been successfully created, but it requires verification');
        $response->assertSee('Please wait for an administrator');
    }

    public function test_non_verified_user_does_not_see_inventory_links(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $nonVerifiedRole = Role::findByName('Non-verified users');
        $user->assignRole($nonVerifiedRole);

        $response = $this->actingAs($user)->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertDontSee('Primary Domains');
        $response->assertDontSee('Items');
        $response->assertDontSee('Partners');
        $response->assertDontSee('Projects');
        $response->assertDontSee('Collections');
    }

    public function test_non_verified_user_cannot_access_items_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $nonVerifiedRole = Role::findByName('Non-verified users');
        $user->assignRole($nonVerifiedRole);

        $response = $this->actingAs($user)->get(route('items.index'));

        // Should be denied access due to lack of permissions
        $response->assertStatus(403);
    }

    public function test_regular_user_sees_inventory_content(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $regularRole = Role::findByName('Regular User');
        $user->assignRole($regularRole);

        $response = $this->actingAs($user)->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertSee('Primary Domains');
        $response->assertSee('Items');
        $response->assertSee('Partners');
        $response->assertSee('Projects');
        $response->assertSee('Collections');
        $response->assertDontSee('Account Under Review');
    }

    public function test_regular_user_can_access_items_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $regularRole = Role::findByName('Regular User');
        $user->assignRole($regularRole);

        $response = $this->actingAs($user)->get(route('items.index'));

        $response->assertStatus(200);
    }

    public function test_manager_user_sees_administration_but_not_inventory(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $managerRole = Role::findByName('Manager of Users');
        $user->assignRole($managerRole);

        $response = $this->actingAs($user)->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertSee('Administration');
        $response->assertSee('User Management');
        $response->assertDontSee('Primary Domains');
        $response->assertDontSee('Account Under Review');
    }

    public function test_manager_user_cannot_access_items_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $managerRole = Role::findByName('Manager of Users');
        $user->assignRole($managerRole);

        $response = $this->actingAs($user)->get(route('items.index'));

        // Should be denied access since managers don't have data permissions
        $response->assertStatus(403);
    }

    public function test_manager_user_can_access_user_management(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $managerRole = Role::findByName('Manager of Users');
        $user->assignRole($managerRole);

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertStatus(200);
    }
}
