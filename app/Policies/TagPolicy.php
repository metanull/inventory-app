<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::MANAGE_REFERENCE_DATA->value);
    }

    public function view(User $user, Tag $tag): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Tag $tag): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $this->viewAny($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->viewAny($user);
    }
}
