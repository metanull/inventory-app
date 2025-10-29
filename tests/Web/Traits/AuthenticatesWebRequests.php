<?php

namespace Tests\Web\Traits;

use App\Enums\Permission;
use App\Models\User;

/**
 * Provides authenticated Web requests for tests
 */
trait AuthenticatesWebRequests
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate user with all data permissions for Web tests
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(Permission::VIEW_DATA);
        $this->user->givePermissionTo(Permission::CREATE_DATA);
        $this->user->givePermissionTo(Permission::UPDATE_DATA);
        $this->user->givePermissionTo(Permission::DELETE_DATA);

        $this->actingAs($this->user);
    }

    protected function actingAsUserWithPermissions(array $permissions): self
    {
        $this->user = User::factory()->create();

        foreach ($permissions as $permission) {
            $this->user->givePermissionTo($permission);
        }

        $this->actingAs($this->user);

        return $this;
    }
}
