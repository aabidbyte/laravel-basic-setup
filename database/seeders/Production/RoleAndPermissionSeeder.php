<?php

namespace Database\Seeders\Production;

use App\Constants\Auth\PermissionAction;
use App\Constants\Auth\PermissionEntity;
use App\Constants\Auth\Roles;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Auth\PermissionMatrix;
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
     * Create all permissions using the PermissionMatrix.
     */
    private function createPermissions(): void
    {
        $matrix = new PermissionMatrix;
        $count = 0;

        foreach ($matrix->getMatrix() as $entity => $actions) {
            foreach ($actions as $action) {
                $permissionName = $matrix->getPermissionName($entity, $action);
                $displayName = $this->generateDisplayName($entity, $action);
                $description = $this->generateDescription($entity, $action);

                Permission::firstOrCreate(
                    ['name' => $permissionName],
                    [
                        'display_name' => $displayName,
                        'description' => $description,
                    ],
                );
                $count++;
            }
        }

        $this->command->info("✅ Created {$count} permissions");
    }

    /**
     * Generate a human-readable display name for a permission.
     *
     * @param  string  $entity  Entity constant
     * @param  string  $action  Action constant
     */
    private function generateDisplayName(string $entity, string $action): string
    {
        $entityLabel = PermissionEntity::getLabel($entity);
        $actionLabel = PermissionAction::getLabel($action);

        return "{$actionLabel} {$entityLabel}";
    }

    /**
     * Generate a description for a permission.
     *
     * @param  string  $entity  Entity constant
     * @param  string  $action  Action constant
     */
    private function generateDescription(string $entity, string $action): string
    {
        $entityLabel = PermissionEntity::getLabel($entity);
        $actionLabel = strtolower(PermissionAction::getLabel($action));

        return match ($action) {
            PermissionAction::VIEW => "Can view {$entityLabel} listings and details",
            PermissionAction::CREATE => "Can create new {$entityLabel}",
            PermissionAction::EDIT => "Can edit existing {$entityLabel}",
            PermissionAction::DELETE => "Can delete {$entityLabel}",
            PermissionAction::RESTORE => "Can restore deleted {$entityLabel}",
            PermissionAction::FORCE_DELETE => "Can permanently delete {$entityLabel}",
            PermissionAction::EXPORT => "Can export {$entityLabel} data",
            PermissionAction::PUBLISH => "Can publish {$entityLabel}",
            PermissionAction::UNPUBLISH => "Can unpublish {$entityLabel}",
            PermissionAction::RESOLVE => "Can resolve {$entityLabel}",
            PermissionAction::ACTIVATE => "Can activate/deactivate {$entityLabel}",
            PermissionAction::CONFIGURE => "Can configure {$entityLabel}",
            PermissionAction::GENERATE_ACTIVATION => "Can generate activation links for {$entityLabel}",
            default => "Can {$actionLabel} {$entityLabel}",
        };
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
                ['display_name' => $displayName],
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
