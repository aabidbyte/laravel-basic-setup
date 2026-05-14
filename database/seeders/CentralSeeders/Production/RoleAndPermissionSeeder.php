<?php

namespace Database\Seeders\CentralSeeders\Production;

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create Permissions
            foreach (Permissions::byEntity() as $entity => $permissions) {
                foreach ($permissions as $permissionName) {
                    Permission::firstOrCreate(
                        ['name' => $permissionName],
                        [
                            'entity' => $entity,
                            'action' => \str_contains($permissionName, ' ') ? \explode(' ', $permissionName)[0] : $permissionName,
                        ],
                    );
                }
            }

            // Create Roles and assign existing permissions
            $allPermissions = Permissions::all();
            $this->createSuperAdminRole($allPermissions);
            $this->createAdminRole();
            $this->createMemberRole();
        });
    }

    /**
     * Create Super Admin role with all permissions.
     */
    private function createSuperAdminRole(array $allPermissions): void
    {
        $role = Role::firstOrCreate(
            ['name' => Roles::SUPER_ADMIN],
            ['display_name' => Str::headline(Roles::SUPER_ADMIN)],
        );
        $role->syncPermissions($allPermissions);
    }

    /**
     * Create Admin role with subset of permissions.
     */
    private function createAdminRole(): void
    {
        $role = Role::firstOrCreate(
            ['name' => Roles::ADMIN],
            ['display_name' => Str::headline(Roles::ADMIN)],
        );

        // Define admin permissions (everything except telescope/horizon/security settings)
        $adminPermissions = array_filter(Permissions::all(), function ($permission) {
            return ! \str_contains($permission, 'telescope') &&
                   ! \str_contains($permission, 'horizon') &&
                   ! \str_contains($permission, 'error_logs');
        });

        $role->syncPermissions($adminPermissions);
    }

    /**
     * Create Member role with minimal permissions.
     */
    private function createMemberRole(): void
    {
        Role::firstOrCreate(
            ['name' => Roles::MEMBER],
            ['display_name' => Str::headline(Roles::MEMBER)],
        );
        // Members typically have no administrative permissions by default
    }
}
