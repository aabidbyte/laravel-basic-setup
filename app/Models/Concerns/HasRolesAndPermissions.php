<?php

namespace App\Models\Concerns;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Trait for managing roles and permissions on User model.
 *
 * Replaces Spatie's HasRoles trait with a simpler, Laravel-native implementation.
 */
trait HasRolesAndPermissions
{
    /**
     * Get the roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Check if the user has the given role(s).
     *
     * @param  string|array<string>  $roles  Role name(s) to check
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;

        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    /**
     * Check if the user has any of the given roles.
     *
     * @param  array<string>  $roles  Role names to check
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Check if the user has all of the given roles.
     *
     * @param  array<string>  $roles  Role names to check
     */
    public function hasAllRoles(array $roles): bool
    {
        $userRoleNames = $this->roles->pluck('name')->toArray();

        return count(array_intersect($roles, $userRoleNames)) === count($roles);
    }

    /**
     * Assign the given role(s) to the user.
     *
     * @param  string|Role  ...$roles  Role(s) to assign
     * @return $this
     */
    public function assignRole(string|Role ...$roles): static
    {
        $roleModels = collect($roles)->map(function ($role) {
            return $role instanceof Role
                ? $role
                : Role::where('name', $role)->first();
        })->filter();

        $this->roles()->syncWithoutDetaching($roleModels->pluck('id'));
        $this->unsetRelation('roles');

        return $this;
    }

    /**
     * Sync the given role(s) to the user, removing any not in the list.
     *
     * @param  array<int|string|Role>|Collection  $roles  Role IDs, names, or models
     * @return $this
     */
    public function syncRoles(array|Collection $roles): static
    {
        $roles = $roles instanceof Collection ? $roles->all() : $roles;

        $roleIds = collect($roles)->map(function ($role) {
            if ($role instanceof Role) {
                return $role->id;
            }
            if (is_numeric($role)) {
                return (int) $role;
            }

            return Role::where('name', $role)->first()?->id;
        })->filter()->toArray();

        $this->roles()->sync($roleIds);
        $this->unsetRelation('roles');

        return $this;
    }

    /**
     * Remove the given role from the user.
     *
     * @param  string|Role  $role  Role to remove
     * @return $this
     */
    public function removeRole(string|Role $role): static
    {
        $roleModel = $role instanceof Role
            ? $role
            : Role::where('name', $role)->first();

        if ($roleModel) {
            $this->roles()->detach($roleModel->id);
            $this->unsetRelation('roles');
        }

        return $this;
    }

    /**
     * Check if the user has the given permission.
     *
     * @param  string  $permission  Permission name to check
     */
    public function hasPermissionTo(string $permission): bool
    {
        return $this->getAllPermissions()->contains('name', $permission);
    }

    /**
     * Get all permissions for the user through their roles.
     */
    public function getAllPermissions(): Collection
    {
        return $this->roles->flatMap->permissions->unique('id');
    }

    /**
     * Get all permission names for the user.
     *
     * @return array<string>
     */
    public function getPermissionNames(): array
    {
        return $this->getAllPermissions()->pluck('name')->toArray();
    }
}
