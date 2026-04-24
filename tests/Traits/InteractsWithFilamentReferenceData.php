<?php

namespace Tests\Traits;

use App\Enums\Permission;
use App\Models\User;
use Filament\Facades\Filament;

trait InteractsWithFilamentReferenceData
{
    protected function createAuthorizedUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::MANAGE_REFERENCE_DATA->value,
        ]);

        return $user;
    }

    protected function setCurrentPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }
}
