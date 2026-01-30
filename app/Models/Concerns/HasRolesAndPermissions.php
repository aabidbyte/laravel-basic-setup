<?php

namespace App\Models\Concerns;

use App\Models\Permission;
use App\Models\Pivots\PermissionUser;
use App\Models\Pivots\RoleUser;
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
        return $this->belongsToMany(Role::class)
            ->using(RoleUser::class);
    }

    /**
     * Get the direct permissions assigned to the user (not through roles).
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->using(PermissionUser::class);
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

        return \count(array_intersect($roles, $userRoleNames)) === \count($roles);
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
            if (\is_numeric($role)) {
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
     * Assign the given permission(s) directly to the user.
     *
     * @param  string|Permission  ...$permissions  Permission(s) to assign
     * @return $this
     */
    public function assignPermission(string|Permission ...$permissions): static
    {
        $permissionModels = collect($permissions)->map(function ($permission) {
            return $permission instanceof Permission
                ? $permission
                : Permission::where('name', $permission)->first();
        })->filter();

        $this->permissions()->syncWithoutDetaching($permissionModels->pluck('id'));
        $this->unsetRelation('permissions');

        return $this;
    }

    /**
     * Sync the given permission(s) directly to the user, removing any not in the list.
     *
     * @param  array<int|string|Permission>|Collection  $permissions  Permission IDs, names, or models
     * @return $this
     */
    public function syncPermissions(array|Collection $permissions): static
    {
        $permissions = $permissions instanceof Collection ? $permissions->all() : $permissions;

        $permissionIds = collect($permissions)->map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission->id;
            }
            if (\is_numeric($permission)) {
                return (int) $permission;
            }

            return Permission::where('name', $permission)->first()?->id;
        })->filter()->toArray();

        $this->permissions()->sync($permissionIds);
        $this->unsetRelation('permissions');

        return $this;
    }

    /**
     * Remove the given permission from the user.
     *
     * @param  string|Permission  $permission  Permission to remove
     * @return $this
     */
    public function removePermission(string|Permission $permission): static
    {
        $permissionModel = $permission instanceof Permission
            ? $permission
            : Permission::where('name', $permission)->first();

        if ($permissionModel) {
            $this->permissions()->detach($permissionModel->id);
            $this->unsetRelation('permissions');
        }

        return $this;
    }

    /**
     * Get only the direct permissions assigned to the user (not from roles).
     */
    public function getDirectPermissions(): Collection
    {
        return $this->permissions;
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
     * Get all permissions for the user (both from roles and direct permissions).
     */
    public function getAllPermissions(): Collection
    {
        // Get permissions from roles
        $rolePermissions = $this->roles->flatMap->permissions;

        // Get direct permissions
        $directPermissions = $this->permissions;

        // Merge and remove duplicates
        return $rolePermissions->merge($directPermissions)->unique('id');
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
