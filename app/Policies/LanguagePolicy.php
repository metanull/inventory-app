<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Language;
use App\Models\User;

class LanguagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::MANAGE_REFERENCE_DATA->value);
    }

    public function view(User $user, Language $language): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Language $language): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Language $language): bool
    {
        return $this->viewAny($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->viewAny($user);
    }
}
