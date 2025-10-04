<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Data operation permissions
            'view data' => 'Read access to all data models',
            'create data' => 'Create new records in data models',
            'update data' => 'Modify existing records',
            'delete data' => 'Remove records from data models',

            // User management permissions
            'manage users' => 'Create, read, update, delete users',
            'assign roles' => 'Grant and revoke user roles',
            'view user management' => 'Access user management interfaces',

            // Role management permissions
            'manage roles' => 'Create, read, update roles and permissions',
            'view role management' => 'Access role management interfaces',
        ];

        foreach ($permissions as $name => $description) {
            Permission::create([
                'name' => $name,
                'description' => $description,
            ]);
        }

        // Create "Non-verified users" role with no permissions
        $nonVerifiedRole = Role::create([
            'name' => 'Non-verified users',
            'description' => 'Self-registered users awaiting verification by administrators',
        ]);

        // Non-verified users get no permissions - they cannot do anything until verified

        // Create "Regular User" role with data operation permissions
        $regularUserRole = Role::create([
            'name' => 'Regular User',
            'description' => 'Standard user with data operation access',
        ]);

        $regularUserRole->givePermissionTo([
            'view data',
            'create data',
            'update data',
            'delete data',
        ]);

        // Create "Manager of Users" role with only user/role management permissions
        $managerRole = Role::create([
            'name' => 'Manager of Users',
            'description' => 'User and role management access only (no data operations)',
        ]);

        $managerRole->givePermissionTo([
            // User management permissions
            'manage users',
            'assign roles',
            'view user management',

            // Role management permissions
            'manage roles',
            'view role management',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Created roles: Non-verified users, Regular User, Manager of Users');
        $this->command->info('Created '.count($permissions).' permissions');
    }
}
