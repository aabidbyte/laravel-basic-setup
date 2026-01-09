<?php

declare(strict_types=1);

namespace App\Constants\Auth;

use App\Services\Auth\PermissionMatrix;

/**
 * Permission constants for the RBAC system.
 *
 * This class provides constants for all permissions in the system.
 * Permissions follow the pattern: "{action} {entity}" (e.g., "view users", "edit roles").
 *
 * The permissions are derived from the PermissionMatrix service which defines
 * which entities support which actions.
 *
 * CRITICAL RULE: All permission names must be defined here as constants.
 * NO HARDCODED STRINGS ARE ALLOWED for permission names throughout the application.
 */
class Permissions
{
    // User permissions
    public const VIEW_USERS = 'view users';

    public const CREATE_USERS = 'create users';

    public const EDIT_USERS = 'edit users';

    public const DELETE_USERS = 'delete users';

    public const ACTIVATE_USERS = 'activate users';

    public const EXPORT_USERS = 'export users';

    public const GENERATE_ACTIVATION_USERS = 'generate_activation users';

    public const RESTORE_USERS = 'restore users';

    public const FORCE_DELETE_USERS = 'force_delete users';

    // Role permissions
    public const VIEW_ROLES = 'view roles';

    public const CREATE_ROLES = 'create roles';

    public const EDIT_ROLES = 'edit roles';

    public const DELETE_ROLES = 'delete roles';

    public const RESTORE_ROLES = 'restore roles';

    public const FORCE_DELETE_ROLES = 'force_delete roles';

    // Team permissions
    public const VIEW_TEAMS = 'view teams';

    public const CREATE_TEAMS = 'create teams';

    public const EDIT_TEAMS = 'edit teams';

    public const DELETE_TEAMS = 'delete teams';

    public const RESTORE_TEAMS = 'restore teams';

    public const FORCE_DELETE_TEAMS = 'force_delete teams';

    // Document permissions
    public const VIEW_DOCUMENTS = 'view documents';

    public const CREATE_DOCUMENTS = 'create documents';

    public const EDIT_DOCUMENTS = 'edit documents';

    public const DELETE_DOCUMENTS = 'delete documents';

    public const PUBLISH_DOCUMENTS = 'publish documents';

    public const UNPUBLISH_DOCUMENTS = 'unpublish documents';

    // Article permissions
    public const VIEW_ARTICLES = 'view articles';

    public const CREATE_ARTICLES = 'create articles';

    public const EDIT_ARTICLES = 'edit articles';

    public const DELETE_ARTICLES = 'delete articles';

    public const PUBLISH_ARTICLES = 'publish articles';

    public const UNPUBLISH_ARTICLES = 'unpublish articles';

    public const EXPORT_ARTICLES = 'export articles';

    // Post permissions
    public const VIEW_POSTS = 'view posts';

    public const CREATE_POSTS = 'create posts';

    public const EDIT_POSTS = 'edit posts';

    public const DELETE_POSTS = 'delete posts';

    public const RESTORE_POSTS = 'restore posts';

    public const EXPORT_POSTS = 'export posts';

    // Error log permissions
    public const VIEW_ERROR_LOGS = 'view error_logs';

    public const RESOLVE_ERROR_LOGS = 'resolve error_logs';

    public const DELETE_ERROR_LOGS = 'delete error_logs';

    public const EXPORT_ERROR_LOGS = 'export error_logs';

    public const RESTORE_ERROR_LOGS = 'restore error_logs';

    public const FORCE_DELETE_ERROR_LOGS = 'force_delete error_logs';

    // System access permissions
    public const ACCESS_TELESCOPE = 'access telescope';

    public const ACCESS_HORIZON = 'access horizon';

    // Settings permissions
    public const VIEW_MAIL_SETTINGS = 'view mail_settings';

    public const CONFIGURE_MAIL_SETTINGS = 'configure mail_settings';

    /**
     * Get all permission names from the PermissionMatrix.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return (new PermissionMatrix)->getAllPermissionNames();
    }

    /**
     * Get permissions grouped by entity.
     *
     * @return array<string, array<string>>
     */
    public static function byEntity(): array
    {
        return (new PermissionMatrix)->getPermissionsByEntity();
    }

    /**
     * Get permissions for a specific entity.
     *
     * @param  string  $entity  Entity constant from PermissionEntity
     * @return array<string>
     */
    public static function forEntity(string $entity): array
    {
        $matrix = new PermissionMatrix;

        return array_map(
            fn (string $action) => $matrix->getPermissionName($entity, $action),
            $matrix->getActionsForEntity($entity),
        );
    }

    /**
     * Generate a permission name for an entity-action pair.
     *
     * @param  string  $entity  Entity constant from PermissionEntity
     * @param  string  $action  Action constant from PermissionAction
     */
    public static function make(string $entity, string $action): string
    {
        return (new PermissionMatrix)->getPermissionName($entity, $action);
    }
}
