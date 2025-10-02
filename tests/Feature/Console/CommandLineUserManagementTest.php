<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommandLineUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_user_create_command_creates_user_with_random_password(): void
    {
        $this->artisan('user:create', [
            'username' => 'Test User',
            'email' => 'test@example.com',
        ])
            ->expectsOutput('User created successfully.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(strlen($user->password) > 0); // Password should be hashed
    }

    public function test_user_assign_role_command_assigns_role_to_existing_user(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->artisan('user:assign-role', [
            'email' => 'user@example.com',
            'role' => 'Regular User',
        ])
            ->expectsOutput("Successfully assigned role 'Regular User' to user '{$user->name}' (user@example.com).")
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->hasRole('Regular User'));
    }

    public function test_user_assign_role_command_fails_for_nonexistent_user(): void
    {
        $this->artisan('user:assign-role', [
            'email' => 'nonexistent@example.com',
            'role' => 'Regular User',
        ])
            ->expectsOutput("User with email 'nonexistent@example.com' not found.")
            ->assertExitCode(1);
    }

    public function test_user_assign_role_command_fails_for_nonexistent_role(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->artisan('user:assign-role', [
            'email' => 'user@example.com',
            'role' => 'Nonexistent Role',
        ])
            ->expectsOutput("Role 'Nonexistent Role' not found.")
            ->expectsOutput('Available roles:')
            ->assertExitCode(1);
    }

    public function test_user_assign_role_command_handles_already_assigned_role(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $user->assignRole('Regular User');

        $this->artisan('user:assign-role', [
            'email' => 'user@example.com',
            'role' => 'Regular User',
        ])
            ->expectsOutput("User '{$user->name}' already has role 'Regular User'.")
            ->assertExitCode(0);
    }

    public function test_user_remove_role_command_removes_role_from_user(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $user->assignRole('Regular User');

        $this->artisan('user:remove-role', [
            'email' => 'user@example.com',
            'role' => 'Regular User',
        ])
            ->expectsOutput("Successfully removed role 'Regular User' from user '{$user->name}' (user@example.com).")
            ->assertExitCode(0);

        $this->assertFalse($user->fresh()->hasRole('Regular User'));
    }

    public function test_user_remove_role_command_handles_unassigned_role(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->artisan('user:remove-role', [
            'email' => 'user@example.com',
            'role' => 'Regular User',
        ])
            ->expectsOutput("User '{$user->name}' does not have role 'Regular User'.")
            ->assertExitCode(0);
    }

    public function test_user_list_command_displays_all_users(): void
    {
        $user1 = User::factory()->create(['name' => 'User One', 'email' => 'user1@example.com']);
        $user2 = User::factory()->create(['name' => 'User Two', 'email' => 'user2@example.com']);

        $user1->assignRole('Regular User');
        $user2->assignRole('Manager of Users');

        $this->artisan('user:list')
            ->expectsTable(
                ['ID', 'Name', 'Email', 'Roles', 'Created'],
                [
                    [$user1->id, 'User One', 'user1@example.com', 'Regular User', $user1->created_at->format('Y-m-d H:i')],
                    [$user2->id, 'User Two', 'user2@example.com', 'Manager of Users', $user2->created_at->format('Y-m-d H:i')],
                ]
            )
            ->expectsOutput('Found 2 user(s) total.')
            ->assertExitCode(0);
    }

    public function test_user_list_command_filters_by_role(): void
    {
        $user1 = User::factory()->create(['name' => 'User One', 'email' => 'user1@example.com']);
        $user2 = User::factory()->create(['name' => 'User Two', 'email' => 'user2@example.com']);

        $user1->assignRole('Regular User');
        $user2->assignRole('Manager of Users');

        $this->artisan('user:list', ['--role' => 'Regular User'])
            ->expectsTable(
                ['ID', 'Name', 'Email', 'Roles', 'Created'],
                [
                    [$user1->id, 'User One', 'user1@example.com', 'Regular User', $user1->created_at->format('Y-m-d H:i')],
                ]
            )
            ->expectsOutput("Found 1 user(s) with role 'Regular User'.")
            ->assertExitCode(0);
    }

    public function test_user_list_command_handles_no_users(): void
    {
        $this->artisan('user:list')
            ->expectsOutput('No users found.')
            ->assertExitCode(0);
    }

    public function test_user_show_command_displays_user_information(): void
    {
        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        $user->assignRole('Manager of Users');

        $this->artisan('user:show', ['email' => 'test@example.com'])
            ->expectsOutput('User Information:')
            ->expectsOutput("ID: {$user->id}")
            ->expectsOutput('Name: Test User')
            ->expectsOutput('Email: test@example.com')
            ->expectsOutput('Assigned Roles:')
            ->expectsOutput('  • Manager of Users')
            ->expectsOutput('Permissions (via roles):')
            ->assertExitCode(0);
    }

    public function test_user_show_command_handles_user_without_roles(): void
    {
        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        $this->artisan('user:show', ['email' => 'test@example.com'])
            ->expectsOutput('User Information:')
            ->expectsOutput('⚠️  This user has no roles assigned!')
            ->expectsOutput("Use 'php artisan user:assign-role test@example.com \"Role Name\"' to assign a role.")
            ->assertExitCode(0);
    }

    public function test_user_show_command_fails_for_nonexistent_user(): void
    {
        $this->artisan('user:show', ['email' => 'nonexistent@example.com'])
            ->expectsOutput("User with email 'nonexistent@example.com' not found.")
            ->assertExitCode(1);
    }

    public function test_permission_show_command_displays_permission_matrix(): void
    {
        $this->artisan('permission:show')
            ->expectsOutput('Guard: web')
            ->assertExitCode(0);
    }

    public function test_permissions_rebuild_command_rebuilds_permissions(): void
    {
        // Delete existing permissions to test rebuild
        Permission::query()->delete();
        Role::query()->delete();

        $this->artisan('permissions:rebuild', ['--force' => true])
            ->expectsOutput('Rebuilding permissions structure...')
            ->expectsOutput('Clearing existing roles and permissions...')
            ->expectsOutput('Creating new roles and permissions...')
            ->assertExitCode(0);

        // Verify roles and permissions were recreated
        $this->assertDatabaseHas('roles', ['name' => 'Manager of Users']);
        $this->assertDatabaseHas('roles', ['name' => 'Regular User']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage users']);
        $this->assertDatabaseHas('permissions', ['name' => 'view data']);
    }

    public function test_permission_create_role_command_creates_new_role(): void
    {
        $this->artisan('permission:create-role', ['name' => 'Test Role'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('roles', ['name' => 'Test Role']);
    }

    public function test_permission_create_permission_command_creates_new_permission(): void
    {
        $this->artisan('permission:create-permission', ['name' => 'test permission'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('permissions', ['name' => 'test permission']);
    }

    public function test_complete_user_workflow_creates_admin_user(): void
    {
        // This test simulates the complete workflow described in documentation

        // Step 1: Create user
        $this->artisan('user:create', [
            'username' => 'System Administrator',
            'email' => 'admin@company.com',
        ])
            ->expectsOutput('User created successfully.')
            ->assertExitCode(0);

        // Step 2: Assign manager role
        $this->artisan('user:assign-role', [
            'email' => 'admin@company.com',
            'role' => 'Manager of Users',
        ])
            ->expectsOutput("Successfully assigned role 'Manager of Users' to user 'System Administrator' (admin@company.com).")
            ->assertExitCode(0);

        // Step 3: Verify the user was created and has correct permissions
        $user = User::where('email', 'admin@company.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('System Administrator', $user->name);
        $this->assertTrue($user->hasRole('Manager of Users'));
        $this->assertTrue($user->can('manage users'));
        $this->assertTrue($user->can('view user management'));
        $this->assertTrue($user->can('assign roles'));

        // Step 4: Verify via show command
        $this->artisan('user:show', ['email' => 'admin@company.com'])
            ->expectsOutput('User Information:')
            ->expectsOutput('Name: System Administrator')
            ->expectsOutput('  • Manager of Users')
            ->expectsOutput('  • manage users')
            ->assertExitCode(0);
    }

    public function test_role_management_preserves_critical_permissions(): void
    {
        // Ensure that the Manager of Users role has all critical permissions
        $managerRole = Role::findByName('Manager of Users');
        $this->assertNotNull($managerRole);

        // Check critical permissions
        $criticalPermissions = [
            'manage users',
            'view user management',
            'assign roles',
            'manage roles',
        ];

        foreach ($criticalPermissions as $permission) {
            $this->assertTrue(
                $managerRole->hasPermissionTo($permission),
                "Manager of Users role is missing critical permission: {$permission}"
            );
        }
    }

    public function test_regular_user_has_limited_permissions(): void
    {
        $regularRole = Role::findByName('Regular User');
        $this->assertNotNull($regularRole);

        // Regular users should NOT have management permissions
        $restrictedPermissions = [
            'manage users',
            'view user management',
            'assign roles',
            'manage roles',
        ];

        foreach ($restrictedPermissions as $permission) {
            $this->assertFalse(
                $regularRole->hasPermissionTo($permission),
                "Regular User role should not have management permission: {$permission}"
            );
        }

        // But should have basic permissions
        $this->assertTrue($regularRole->hasPermissionTo('view data'));
        $this->assertTrue($regularRole->hasPermissionTo('create data'));
    }
}
