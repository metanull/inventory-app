<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActualWebInterfaceBugReproductionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles and permissions exist
        $this->artisan('db:seed', ['--class' => 'ProductionDataSeeder']);
    }

    public function test_reproduces_original_bug_scenario_with_user_id_3(): void
    {
        // Create admin user (ID 1)
        $admin = User::factory()->create(['email_verified_at' => now(), 'id' => 1]);
        $admin->assignRole('Manager of Users');

        // Create another user (ID 2)
        User::factory()->create(['email_verified_at' => now(), 'id' => 2]);

        // Create the target user that had ID 3 in the original error
        $targetUser = User::factory()->create([
            'email_verified_at' => now(),
            'id' => 3,
            'name' => 'Test User',
            'email' => 'test-user-3@example.com',
        ]);

        // Get the Regular User role (which likely has ID 3, causing the original confusion)
        $regularUserRole = Role::where('name', 'Regular User')->first();

        // This should work now - assigning role ID 3 to user ID 3
        // Previously this failed because role ID was being passed as role name
        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'roles' => [$regularUserRole->id], // This was the problematic line
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Verify the role was correctly assigned
        $this->assertTrue($targetUser->fresh()->hasRole('Regular User'));

        // Also verify that the role assignment is persistent
        $rolesAssigned = $targetUser->fresh()->roles->pluck('name')->toArray();
        $this->assertContains('Regular User', $rolesAssigned);
    }

    public function test_web_form_handles_edge_cases_correctly(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create();

        // Test with empty roles array (should remove all roles)
        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'roles' => [], // Explicitly empty array
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertCount(0, $targetUser->fresh()->roles);

        // Test with no roles key at all (should also remove all roles)
        $targetUser->assignRole('Regular User'); // First assign a role
        $this->assertTrue($targetUser->fresh()->hasRole('Regular User'));

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            // No 'roles' key at all
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertCount(0, $targetUser->fresh()->roles);
    }

    public function test_multiple_role_assignments_work_correctly(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create();
        // Enable MFA for the target user
        $targetUser->forceFill([
            'email_2fa_enabled' => true,
        ])->save();

        // Get multiple roles
        $regularUserRole = Role::where('name', 'Regular User')->first();
        $managerRole = Role::where('name', 'Manager of Users')->first();

        // Assign multiple roles via web form
        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'roles' => [$regularUserRole->id, $managerRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $freshUser = $targetUser->fresh();
        $this->assertTrue($freshUser->hasRole('Regular User'));
        $this->assertTrue($freshUser->hasRole('Manager of Users'));
        $this->assertCount(2, $freshUser->roles);
    }
}
