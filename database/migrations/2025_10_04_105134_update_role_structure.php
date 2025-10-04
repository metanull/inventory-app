<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure the new role structure exists
        $this->createRolesAndPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the new role
        Role::where('name', 'Non-verified users')->delete();

        // Restore manager role permissions
        $managerRole = Role::findByName('Manager of Users');
        if ($managerRole) {
            $dataPermissions = ['view data', 'create data', 'update data', 'delete data'];
            foreach ($dataPermissions as $permission) {
                $perm = Permission::findByName($permission);
                if ($perm) {
                    $managerRole->givePermissionTo($perm);
                }
            }
        }
    }

    private function createRolesAndPermissions(): void
    {
        // Create permissions if they don't exist
        $permissions = [
            ['name' => 'view data', 'description' => 'View all data models (items, partners, etc.)'],
            ['name' => 'create data', 'description' => 'Create new data records'],
            ['name' => 'update data', 'description' => 'Update existing data records'],
            ['name' => 'delete data', 'description' => 'Delete data records'],
            ['name' => 'manage users', 'description' => 'Create, update, and delete users'],
            ['name' => 'assign roles', 'description' => 'Assign and remove roles from users'],
            ['name' => 'view user management', 'description' => 'Access user management interfaces'],
            ['name' => 'manage roles', 'description' => 'Create and modify roles'],
            ['name' => 'view role management', 'description' => 'Access role management interfaces'],
            ['name' => 'manage settings', 'description' => 'View and modify system settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }

        // Create "Non-verified users" role if it doesn't exist
        $nonVerifiedRole = Role::firstOrCreate(
            ['name' => 'Non-verified users'],
            ['description' => 'Users who have registered but not yet been granted permissions']
        );

        // Ensure "Regular User" role exists and has correct permissions
        $regularRole = Role::firstOrCreate(
            ['name' => 'Regular User'],
            ['description' => 'Standard users with data CRUD permissions']
        );

        $regularPermissions = ['view data', 'create data', 'update data', 'delete data'];
        foreach ($regularPermissions as $permission) {
            $perm = Permission::findByName($permission);
            if ($perm && ! $regularRole->hasPermissionTo($perm)) {
                $regularRole->givePermissionTo($perm);
            }
        }

        // Ensure "Manager of Users" role exists and has ONLY user management permissions
        $managerRole = Role::firstOrCreate(
            ['name' => 'Manager of Users'],
            ['description' => 'User and role management access only (no data operations)']
        );

        // Remove data permissions from manager role if they exist
        $dataPermissions = ['view data', 'create data', 'update data', 'delete data'];
        foreach ($dataPermissions as $permission) {
            $perm = Permission::findByName($permission);
            if ($perm && $managerRole->hasPermissionTo($perm)) {
                $managerRole->revokePermissionTo($perm);
            }
        }

        // Ensure manager has user management permissions
        $managerPermissions = ['manage users', 'assign roles', 'view user management', 'manage roles', 'view role management', 'manage settings'];
        foreach ($managerPermissions as $permission) {
            $perm = Permission::findByName($permission);
            if ($perm && ! $managerRole->hasPermissionTo($perm)) {
                $managerRole->givePermissionTo($perm);
            }
        }
    }
};
