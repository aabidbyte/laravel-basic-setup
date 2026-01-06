<?php

declare(strict_types=1);

namespace App\Policies;

use App\Constants\Auth\Permissions;
use App\Models\User;

class UserPolicy
{
    /**
     * Perform pre-authorization checks (Super Admin bypass).
     *
     * Returning true grants all abilities. Returning null falls through to the specific method.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Super Admin (ID 1) can do anything
        if ($user->id === 1) {
            return true;
        }

        return null;
    }

    /**
     * Determine if the user can view any users (list).
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::VIEW_USERS);
    }

    /**
     * Determine if the user can view a specific user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->can(Permissions::VIEW_USERS);
    }

    /**
     * Determine if the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->can(Permissions::CREATE_USERS);
    }

    /**
     * Determine if the user can update a user.
     */
    public function update(User $user, User $model): bool
    {
        // Cannot edit yourself via admin panel
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can(Permissions::EDIT_USERS);
    }

    /**
     * Determine if the user can delete a user.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot delete the super admin (ID 1) - extra protection
        // This is redundant due to Super Admin bypass but good for safety
        if ($model->id === 1) {
            return false;
        }

        return $user->can(Permissions::DELETE_USERS);
    }
}
