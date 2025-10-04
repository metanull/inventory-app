<?php

namespace Tests\Traits;

use App\Models\User;
use Spatie\Permission\Models\Role;

trait RequiresDataPermissions
{
    protected function createAuthenticatedUserWithDataPermissions(): User
    {
        // Ensure roles exist by seeding them (only if they don't exist)
        if (! Role::where('name', 'Regular User')->exists()) {
            $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        }

        $user = User::factory()->create(['email_verified_at' => now()]);
        $regularRole = Role::findByName('Regular User');
        $user->assignRole($regularRole);

        return $user;
    }

    protected function actAsRegularUser(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);
    }
}
