<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserRoleInformation extends Component
{
    public function render()
    {
        $user = Auth::user();
        $roles = $user->roles()->get();
        $permissions = $user->getAllPermissions();

        return view('livewire.profile.user-role-information', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
}
