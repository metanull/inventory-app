<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::MANAGE_USERS->value);
    }

    public function view(User $user, User $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, User $record): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, User $record): bool
    {
        return $user->hasPermissionTo(Permission::ASSIGN_ROLES->value) && ! $user->is($record);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ASSIGN_ROLES->value);
    }

    public function approve(User $user, User $record): bool
    {
        return $this->viewAny($user);
    }

    public function suspend(User $user, User $record): bool
    {
        return $this->viewAny($user) && ! $user->is($record);
    }
}
