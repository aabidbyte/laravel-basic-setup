<?php

namespace App\Policies;

use App\Constants\Auth\Permissions;
use App\Models\Feature;
use App\Models\User;

class FeaturePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::VIEW_FEATURES());
    }

    public function view(User $user, Feature $feature): bool
    {
        return $user->hasPermissionTo(Permissions::VIEW_FEATURES());
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::CREATE_FEATURES());
    }

    public function update(User $user, Feature $feature): bool
    {
        return $user->hasPermissionTo(Permissions::EDIT_FEATURES());
    }

    public function delete(User $user, Feature $feature): bool
    {
        return $user->hasPermissionTo(Permissions::DELETE_FEATURES());
    }

    public function restore(User $user, Feature $feature): bool
    {
        return $user->hasPermissionTo(Permissions::RESTORE_FEATURES());
    }

    public function forceDelete(User $user, Feature $feature): bool
    {
        return $user->hasPermissionTo(Permissions::FORCE_DELETE_FEATURES());
    }
}
