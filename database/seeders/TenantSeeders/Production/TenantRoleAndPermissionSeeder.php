<?php

namespace Database\Seeders\TenantSeeders\Production;

use App\Constants\Auth\Roles;
use App\Constants\Auth\TenantPermissionMatrix;
use App\Enums\Ui\ThemeColorTypes;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantRoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = $this->seedPermissions();

        $admin = $this->updateOrCreateRole(Roles::ADMIN, [
            'display_name' => Str::headline(Roles::ADMIN),
            'color' => ThemeColorTypes::PRIMARY->value,
        ]);

        $member = $this->updateOrCreateRole(Roles::MEMBER, [
            'display_name' => Str::headline(Roles::MEMBER),
            'color' => ThemeColorTypes::NEUTRAL->value,
        ]);

        $this->syncRolePermissions($admin, $permissions);
        $this->syncRolePermissions($member, new Collection());
    }

    /**
     * @return Collection<int, Permission>
     */
    private function seedPermissions(): Collection
    {
        foreach (TenantPermissionMatrix::byEntity() as $entity => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$action} {$entity}";
                $permission = Permission::firstOrNew(['name' => $permissionName]);

                if (! $permission->exists) {
                    $permission->uuid = (string) Str::uuid();
                }

                $permission->fill([
                    'display_name' => Str::headline($permissionName),
                    'entity' => $entity,
                    'action' => $action,
                ]);
                $permission->save();
            }
        }

        return Permission::whereIn('name', TenantPermissionMatrix::all())->get();
    }

    /**
     * @param  array{display_name: string, color: string}  $attributes
     */
    private function updateOrCreateRole(string $name, array $attributes): Role
    {
        $role = Role::firstOrNew(['name' => $name]);

        if (! $role->exists) {
            $role->uuid = (string) Str::uuid();
        }

        $role->fill($attributes);
        $role->save();

        return $role;
    }

    /**
     * @param  Collection<int, Permission>  $permissions
     */
    private function syncRolePermissions(Role $role, Collection $permissions): void
    {
        $role->permissions()->sync($permissions->mapWithKeys(fn (Permission $permission): array => [
            $permission->id => ['uuid' => (string) Str::uuid()],
        ])->all());
    }
}
