<?php

declare(strict_types=1);

namespace App\Constants\Auth;

class TenantPermissionMatrix
{
    /**
     * @return array<string, array<string>>
     */
    public static function byEntity(): array
    {
        return [
            PermissionEntity::USERS => [
                PermissionAction::VIEW,
                PermissionAction::CREATE,
                PermissionAction::EDIT,
                PermissionAction::DELETE,
            ],
            PermissionEntity::ROLES => [
                PermissionAction::VIEW,
                PermissionAction::CREATE,
                PermissionAction::EDIT,
                PermissionAction::DELETE,
            ],
            PermissionEntity::TEAMS => [
                PermissionAction::VIEW,
                PermissionAction::CREATE,
                PermissionAction::EDIT,
                PermissionAction::DELETE,
            ],
            PermissionEntity::MAIL_SETTINGS => [
                PermissionAction::VIEW,
                PermissionAction::CONFIGURE,
            ],
            PermissionEntity::EMAIL_TEMPLATES => [
                PermissionAction::VIEW,
                PermissionAction::CREATE,
                PermissionAction::EDIT,
                PermissionAction::EDIT_BUILDER,
                PermissionAction::DELETE,
                PermissionAction::PUBLISH,
            ],
            PermissionEntity::EMAIL_LAYOUTS => [
                PermissionAction::VIEW,
                PermissionAction::CREATE,
                PermissionAction::EDIT,
                PermissionAction::DELETE,
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public static function all(): array
    {
        $permissions = [];

        foreach (self::byEntity() as $entity => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "{$action} {$entity}";
            }
        }

        return $permissions;
    }
}
