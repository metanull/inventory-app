<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /** @test */
    public function profile_page_displays_user_roles_and_permissions()
    {
        $user = User::factory()->create();
        $role = Role::findByName('Regular User');
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->get(route('web.profile.show'));

        $response->assertStatus(200);
        $response->assertSee('User Roles & Permissions');
        $response->assertSee('Regular User');
        $response->assertSee('view data');
        $response->assertSee('create data');
    }

    /** @test */
    public function profile_shows_warning_for_users_without_roles()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('web.profile.show'));

        $response->assertStatus(200);
        $response->assertSee('No Roles Assigned');
        $response->assertSee('Please contact an administrator');
    }

    /** @test */
    public function user_role_information_livewire_component_works()
    {
        $user = User::factory()->create();
        $role = Role::findByName('Manager of Users');
        $user->assignRole($role);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Profile\UserRoleInformation::class)
            ->assertSee('Manager of Users')
            ->assertSee('manage users')
            ->assertSee('assign roles')
            ->assertSee('view data');
    }

    /** @test */
    public function user_role_information_shows_no_roles_message()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Profile\UserRoleInformation::class)
            ->assertSee('No Roles Assigned')
            ->assertSee('Please contact an administrator');
    }

    /** @test */
    public function email_verification_is_enabled()
    {
        $this->assertTrue(
            in_array('email-verification', config('fortify.features'))
        );
    }

    /** @test */
    public function profile_update_features_are_enabled()
    {
        $this->assertTrue(
            in_array('update-profile-information', config('fortify.features'))
        );

        $this->assertTrue(
            in_array('update-passwords', config('fortify.features'))
        );
    }

    /** @test */
    public function two_factor_authentication_is_enabled()
    {
        $this->assertTrue(
            in_array('two-factor-authentication', config('fortify.features'))
        );
    }
}
