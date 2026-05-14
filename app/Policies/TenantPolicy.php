<?php

declare(strict_types=1);

namespace App\Policies;

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::VIEW_TENANTS());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tenant $tenant): bool
    {
        if ($user->hasRole(Roles::SUPER_ADMIN)) {
            return true;
        }

        return $user->hasPermissionTo(Permissions::VIEW_TENANTS())
            && $user->tenants()->whereKey($tenant->getKey())->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::CREATE_TENANTS());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tenant $tenant): bool
    {
        if ($user->hasRole(Roles::SUPER_ADMIN)) {
            return true;
        }

        return $user->hasPermissionTo(Permissions::EDIT_TENANTS())
            && $user->tenants()->whereKey($tenant->getKey())->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tenant $tenant): bool
    {
        if ($user->hasRole(Roles::SUPER_ADMIN)) {
            return true;
        }

        return $user->hasPermissionTo(Permissions::DELETE_TENANTS())
            && $user->tenants()->whereKey($tenant->getKey())->exists();
    }
}
