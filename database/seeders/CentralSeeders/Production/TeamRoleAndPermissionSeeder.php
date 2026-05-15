<?php

namespace Database\Seeders\CentralSeeders\Production;

use App\Constants\Teams\TeamPermissions;
use App\Constants\Teams\TeamRoles;
use App\Enums\Ui\ThemeColorTypes;
use App\Models\TeamPermission;
use App\Models\TeamRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeamRoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = $this->seedPermissions();

        $admin = $this->updateOrCreateRole(TeamRoles::ADMIN, [
            'display_name' => Str::headline(TeamRoles::ADMIN),
            'color' => ThemeColorTypes::PRIMARY->value,
            'is_admin' => true,
            'is_default' => false,
            'sort_order' => 10,
        ]);

        $member = $this->updateOrCreateRole(TeamRoles::MEMBER, [
            'display_name' => Str::headline(TeamRoles::MEMBER),
            'color' => ThemeColorTypes::NEUTRAL->value,
            'is_admin' => false,
            'is_default' => true,
            'sort_order' => 20,
        ]);

        $this->syncRolePermissions($admin, $permissions->whereIn('name', TeamPermissions::admin()));
        $this->syncRolePermissions($member, $permissions->whereIn('name', TeamPermissions::member()));
    }

    /**
     * @return Collection<int, TeamPermission>
     */
    private function seedPermissions(): Collection
    {
        foreach (TeamPermissions::all() as $permissionName) {
            $permission = TeamPermission::firstOrNew(['name' => $permissionName]);

            if (! $permission->exists) {
                $permission->uuid = (string) Str::uuid();
            }

            $parts = \explode(' ', $permissionName, 2);
            $permission->fill([
                'display_name' => Str::headline($permissionName),
                'action' => $parts[0],
                'entity' => $parts[1] ?? $permissionName,
            ]);
            $permission->save();
        }

        return TeamPermission::whereIn('name', TeamPermissions::all())->get();
    }

    /**
     * @param  array{
     *     display_name: string,
     *     color: string,
     *     is_admin: bool,
     *     is_default: bool,
     *     sort_order: int,
     * }  $attributes
     */
    private function updateOrCreateRole(string $name, array $attributes): TeamRole
    {
        $role = TeamRole::firstOrNew([
            'tenant_id' => null,
            'name' => $name,
        ]);

        if (! $role->exists) {
            $role->uuid = (string) Str::uuid();
        }

        $role->fill($attributes);
        $role->save();

        return $role;
    }

    /**
     * @param  Collection<int, TeamPermission>  $permissions
     */
    private function syncRolePermissions(TeamRole $role, Collection $permissions): void
    {
        $role->permissions()->sync($permissions->mapWithKeys(fn (TeamPermission $permission): array => [
            $permission->id => ['uuid' => (string) Str::uuid()],
        ])->all());
    }
}
