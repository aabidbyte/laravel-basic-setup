<?php

namespace Database\Seeders\Production;

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
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

        $this->createPermissions();
        $this->createRoles();
        $this->assignPermissionsToSuperAdmin();

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
                ['name' => $permissionName],
            );
        }

        $this->command->info('✅ Created ' . count($permissionNames) . ' permissions');
    }

    /**
     * Create all roles using constants.
     */
    private function createRoles(): void
    {
        $roles = [
            Roles::SUPER_ADMIN => 'Super Admin',
            Roles::ADMIN => 'Admin',
            Roles::MEMBER => 'Member',
        ];

        foreach ($roles as $roleName => $displayName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['display_name' => $displayName]
            );
        }

        $this->command->info('✅ Created ' . count($roles) . ' roles');
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

        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions->toArray());
        $this->command->info('✅ Assigned all permissions to ' . Roles::SUPER_ADMIN . ' role');
    }
}
