<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Models\Pivots\PermissionRole;
use App\Models\Pivots\RoleUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role model for RBAC.
 *
 * Roles are assigned to users and contain permissions.
 */
class Role extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get the permissions that belong to the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->using(PermissionRole::class);
    }

    /**
     * Get the users that have this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(RoleUser::class);
    }

    /**
     * Check if the role has the given permission.
     */
    public function hasPermissionTo(string $permission): bool
    {
        return $this->permissions->contains('name', $permission);
    }

    /**
     * Give the role the specified permission(s).
     *
     * @param  string|Permission  ...$permissions  Permission(s) to give
     * @return $this
     */
    public function givePermissionTo(string|Permission ...$permissions): static
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission->id;
            }

            return Permission::where('name', $permission)->first()?->id;
        })->filter()->toArray();

        $this->permissions()->syncWithoutDetaching($permissionIds);
        $this->unsetRelation('permissions');

        return $this;
    }

    /**
     * Sync the role's permissions to only those specified.
     *
     * @param  array<int|string|Permission>  $permissions  Permissions to sync
     * @return $this
     */
    public function syncPermissions(array $permissions): static
    {
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
     * Revoke the specified permission from the role.
     *
     * @param  string|Permission  $permission  Permission to revoke
     * @return $this
     */
    public function revokePermissionTo(string|Permission $permission): static
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
     * Get a human-readable label for this role.
     */
    public function label(): string
    {
        return $this->display_name ?? $this->name;
    }
}
