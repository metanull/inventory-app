<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class UserRoleInformation extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        if ($user === null) {
            return view('livewire.profile.user-role-information', ['roles' => collect(), 'permissions' => collect()]);
        }

        $roles = $user->roles()->get();
        $permissions = $user->getAllPermissions();

        return view('livewire.profile.user-role-information', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
}
