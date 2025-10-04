<?php

use App\Enums\Permission as PermissionEnum;
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
            ['name' => PermissionEnum::VIEW_DATA->value, 'description' => 'View all data models (items, partners, etc.)'],
            ['name' => PermissionEnum::CREATE_DATA->value, 'description' => 'Create new data records'],
            ['name' => PermissionEnum::UPDATE_DATA->value, 'description' => 'Update existing data records'],
            ['name' => PermissionEnum::DELETE_DATA->value, 'description' => 'Delete data records'],
            ['name' => PermissionEnum::MANAGE_USERS->value, 'description' => 'Create, update, and delete users'],
            ['name' => PermissionEnum::ASSIGN_ROLES->value, 'description' => 'Assign and remove roles from users'],
            ['name' => PermissionEnum::VIEW_USER_MANAGEMENT->value, 'description' => 'Access user management interfaces'],
            ['name' => PermissionEnum::MANAGE_ROLES->value, 'description' => 'Create and modify roles'],
            ['name' => PermissionEnum::VIEW_ROLE_MANAGEMENT->value, 'description' => 'Access role management interfaces'],
            ['name' => PermissionEnum::MANAGE_SETTINGS->value, 'description' => 'View and modify system settings'],
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
        $dataPermissions = [
            PermissionEnum::VIEW_DATA->value,
            PermissionEnum::CREATE_DATA->value,
            PermissionEnum::UPDATE_DATA->value,
            PermissionEnum::DELETE_DATA->value,
        ];
        foreach ($dataPermissions as $permission) {
            $perm = Permission::findByName($permission);
            if ($perm && $managerRole->hasPermissionTo($perm)) {
                $managerRole->revokePermissionTo($perm);
            }
        }

        // Ensure manager has user management permissions
        $managerPermissions = [
            PermissionEnum::MANAGE_USERS->value,
            PermissionEnum::ASSIGN_ROLES->value,
            PermissionEnum::VIEW_USER_MANAGEMENT->value,
            PermissionEnum::MANAGE_ROLES->value,
            PermissionEnum::VIEW_ROLE_MANAGEMENT->value,
            PermissionEnum::MANAGE_SETTINGS->value,
        ];
        foreach ($managerPermissions as $permission) {
            $perm = Permission::findByName($permission);
            if ($perm && ! $managerRole->hasPermissionTo($perm)) {
                $managerRole->givePermissionTo($perm);
            }
        }
    }
};
