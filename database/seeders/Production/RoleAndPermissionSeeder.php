<?php

namespace Database\Seeders\Production;

use App\Constants\Permissions;
use App\Constants\Roles;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates all roles and permissions for the system.
     * This is essential data required in all environments.
     */
    public function run(): void
    {
        $this->command->info('🔐 Creating roles and permissions...');

        clearPermissionCache();

        $this->createPermissions();
        $this->createRoles();
        $this->assignPermissionsToSuperAdmin();

        clearPermissionCache();

        $this->command->info('✅ Roles and permissions created successfully');
    }

    /**
     * Create all permissions using constants.
     */
    private function createPermissions(): void
    {
        $permissionNames = Permissions::all();

        foreach ($permissionNames as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }

        $this->command->info('✅ Created '.count($permissionNames).' permissions');
    }

    /**
     * Create all roles using constants.
     */
    private function createRoles(): void
    {
        $roleNames = Roles::all();

        foreach ($roleNames as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
        }

        $this->command->info('✅ Created '.count($roleNames).' roles');
    }

    /**
     * Assign all permissions to Super Admin role.
     */
    private function assignPermissionsToSuperAdmin(): void
    {
        $superAdminRole = Role::where('name', Roles::SUPER_ADMIN)->first();

        if (! $superAdminRole) {
            return;
        }

        $superAdminRole->givePermissionTo(Permission::all());
        $this->command->info('✅ Assigned all permissions to '.Roles::SUPER_ADMIN.' role');
    }
}
