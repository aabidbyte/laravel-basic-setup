<?php

namespace App\Policies;

use App\Constants\Auth\Permissions;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::VIEW_SUBSCRIPTIONS());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->hasPermissionTo(Permissions::VIEW_SUBSCRIPTIONS());
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::CREATE_SUBSCRIPTIONS());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Subscription $subscription): bool
    {
        return $user->hasPermissionTo(Permissions::EDIT_SUBSCRIPTIONS());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->hasPermissionTo(Permissions::DELETE_SUBSCRIPTIONS());
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Subscription $subscription): bool
    {
        return $user->hasPermissionTo(Permissions::RESTORE_SUBSCRIPTIONS());
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return $user->hasPermissionTo(Permissions::FORCE_DELETE_SUBSCRIPTIONS());
    }
}
