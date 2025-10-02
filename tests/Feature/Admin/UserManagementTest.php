<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /** @test */
    public function manager_can_access_user_management_index()
    {
        $manager = User::factory()->create();
        $managerRole = Role::findByName('Manager of Users');
        $manager->assignRole($managerRole);

        $response = $this->actingAs($manager)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('User Management');
    }

    /** @test */
    public function regular_user_cannot_access_user_management()
    {
        $user = User::factory()->create();
        $regularRole = Role::findByName('Regular User');
        $user->assignRole($regularRole);

        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function user_without_roles_cannot_access_user_management()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function manager_can_view_user_details()
    {
        $manager = User::factory()->create();
        $managerRole = Role::findByName('Manager of Users');
        $manager->assignRole($managerRole);

        $targetUser = User::factory()->create(['name' => 'John Doe']);
        $regularRole = Role::findByName('Regular User');
        $targetUser->assignRole($regularRole);

        $response = $this->actingAs($manager)
            ->get(route('admin.users.show', $targetUser));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('Regular User');
        $response->assertSee('view data');
    }

    /** @test */
    public function manager_can_create_new_user()
    {
        $manager = User::factory()->create();
        $managerRole = Role::findByName('Manager of Users');
        $manager->assignRole($managerRole);

        $regularRole = Role::findByName('Regular User');

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$regularRole->id],
        ];

        $response = $this->actingAs($manager)
            ->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($newUser->hasRole('Regular User'));
    }

    /** @test */
    public function manager_can_update_user()
    {
        $manager = User::factory()->create();
        $managerRole = Role::findByName('Manager of Users');
        $manager->assignRole($managerRole);

        $targetUser = User::factory()->create(['name' => 'Old Name']);
        $regularRole = Role::findByName('Regular User');

        $updateData = [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
            'roles' => [$managerRole->id], // Change role
        ];

        $response = $this->actingAs($manager)
            ->put(route('admin.users.update', $targetUser), $updateData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertEquals('Updated Name', $targetUser->name);
        $this->assertTrue($targetUser->hasRole('Manager of Users'));
        $this->assertFalse($targetUser->hasRole('Regular User'));
    }

    /** @test */
    public function manager_can_delete_other_users()
    {
        $manager = User::factory()->create();
        $managerRole = Role::findByName('Manager of Users');
        $manager->assignRole($managerRole);

        $targetUser = User::factory()->create();
        $targetUserId = $targetUser->id;

        $response = $this->actingAs($manager)
            ->delete(route('admin.users.destroy', $targetUser));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $targetUserId]);
    }

    /** @test */
    public function manager_cannot_delete_themselves()
    {
        $manager = User::factory()->create();
        $managerRole = Role::findByName('Manager of Users');
        $manager->assignRole($managerRole);

        $response = $this->actingAs($manager)
            ->delete(route('admin.users.destroy', $manager));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $manager->id]);
    }

    /** @test */
    public function search_functionality_works()
    {
        $manager = User::factory()->create();
        $managerRole = Role::findByName('Manager of Users');
        $manager->assignRole($managerRole);

        $user1 = User::factory()->create(['name' => 'Alice Smith']);
        $user2 = User::factory()->create(['name' => 'Bob Jones']);

        $response = $this->actingAs($manager)
            ->get(route('admin.users.index', ['search' => 'Alice']));

        $response->assertStatus(200);
        $response->assertSee('Alice Smith');
        $response->assertDontSee('Bob Jones');
    }

    /** @test */
    public function role_filter_works()
    {
        $manager = User::factory()->create();
        $managerRole = Role::findByName('Manager of Users');
        $regularRole = Role::findByName('Regular User');
        $manager->assignRole($managerRole);

        $regularUser = User::factory()->create();
        $regularUser->assignRole($regularRole);

        $response = $this->actingAs($manager)
            ->get(route('admin.users.index', ['role' => 'Regular User']));

        $response->assertStatus(200);
        $response->assertSee($regularUser->name);
        $response->assertDontSee($manager->name);
    }
}
