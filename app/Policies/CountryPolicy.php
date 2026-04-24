<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Country;
use App\Models\User;

class CountryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::MANAGE_REFERENCE_DATA->value);
    }

    public function view(User $user, Country $country): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Country $country): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Country $country): bool
    {
        return $this->viewAny($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->viewAny($user);
    }
}
