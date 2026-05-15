<?php

declare(strict_types=1);

namespace App\Constants\Auth;

use App\Services\Auth\PermissionMatrix;
use Exception;

/**
 * Permission constants for the RBAC system.
 *
 * This class provides dynamic access to all permissions in the system via magic methods.
 * Permissions follow the pattern: "{action} {entity}" (e.g., "view users", "edit roles").
 *
 * The permissions are dynamically generated from the PermissionMatrix service which defines
 * which entities support which actions. This is the single source of truth.
 *
 * USAGE:
 *   Permissions::VIEW_USERS()          // returns 'view users'
 *   Permissions::EDIT_ROLES()          // returns 'edit roles'
 *   Permissions::DELETE_EMAIL_TEMPLATES()  // returns 'delete email_templates'
 *
 * CRITICAL RULE: Use these permission methods throughout the application.
 * NO HARDCODED STRINGS ARE ALLOWED for permission names.
 *
 * @method static string VIEW_USERS()
 * @method static string CREATE_USERS()
 * @method static string EDIT_USERS()
 * @method static string DELETE_USERS()
 * @method static string ACTIVATE_USERS()
 * @method static string EXPORT_USERS()
 * @method static string GENERATE_ACTIVATION_USERS()
 * @method static string IMPERSONATE_USERS()
 * @method static string RESTORE_USERS()
 * @method static string FORCE_DELETE_USERS()
 * @method static string VIEW_ROLES()
 * @method static string CREATE_ROLES()
 * @method static string EDIT_ROLES()
 * @method static string DELETE_ROLES()
 * @method static string RESTORE_ROLES()
 * @method static string FORCE_DELETE_ROLES()
 * @method static string VIEW_TEAMS()
 * @method static string CREATE_TEAMS()
 * @method static string EDIT_TEAMS()
 * @method static string DELETE_TEAMS()
 * @method static string RESTORE_TEAMS()
 * @method static string FORCE_DELETE_TEAMS()
 * @method static string VIEW_ERROR_LOGS()
 * @method static string RESOLVE_ERROR_LOGS()
 * @method static string DELETE_ERROR_LOGS()
 * @method static string EXPORT_ERROR_LOGS()
 * @method static string RESTORE_ERROR_LOGS()
 * @method static string FORCE_DELETE_ERROR_LOGS()
 * @method static string VIEW_TELESCOPE()
 * @method static string VIEW_HORIZON()
 * @method static string VIEW_MAIL_SETTINGS()
 * @method static string CONFIGURE_MAIL_SETTINGS()
 * @method static string VIEW_EMAIL_TEMPLATES()
 * @method static string CREATE_EMAIL_TEMPLATES()
 * @method static string EDIT_EMAIL_TEMPLATES()
 * @method static string EDIT_BUILDER_EMAIL_TEMPLATES()
 * @method static string DELETE_EMAIL_TEMPLATES()
 * @method static string RESTORE_EMAIL_TEMPLATES()
 * @method static string FORCE_DELETE_EMAIL_TEMPLATES()
 * @method static string PUBLISH_EMAIL_TEMPLATES()
 * @method static string VIEW_EMAIL_LAYOUTS()
 * @method static string CREATE_EMAIL_LAYOUTS()
 * @method static string EDIT_EMAIL_LAYOUTS()
 * @method static string DELETE_EMAIL_LAYOUTS()
 * @method static string VIEW_TENANTS()
 * @method static string CREATE_TENANTS()
 * @method static string EDIT_TENANTS()
 * @method static string DELETE_TENANTS()
 * @method static string RESTORE_TENANTS()
 * @method static string FORCE_DELETE_TENANTS()
 * @method static string IMPERSONATE_TENANTS()
 * @method static string VIEW_PLANS()
 * @method static string CREATE_PLANS()
 * @method static string EDIT_PLANS()
 * @method static string DELETE_PLANS()
 * @method static string RESTORE_PLANS()
 * @method static string FORCE_DELETE_PLANS()
 * @method static string VIEW_FEATURES()
 * @method static string CREATE_FEATURES()
 * @method static string EDIT_FEATURES()
 * @method static string DELETE_FEATURES()
 * @method static string RESTORE_FEATURES()
 * @method static string FORCE_DELETE_FEATURES()
 * @method static string VIEW_SUBSCRIPTIONS()
 * @method static string CREATE_SUBSCRIPTIONS()
 * @method static string EDIT_SUBSCRIPTIONS()
 * @method static string DELETE_SUBSCRIPTIONS()
 * @method static string RESTORE_SUBSCRIPTIONS()
 * @method static string FORCE_DELETE_SUBSCRIPTIONS()
 * @method static string VIEW_DOMAINS()
 * @method static string CREATE_DOMAINS()
 * @method static string EDIT_DOMAINS()
 * @method static string DELETE_DOMAINS()
 * @method static string VIEW_TRASH()
 * @method static string DELETE_TRASH()
 * @method static string RESTORE_TRASH()
 * @method static string FORCE_DELETE_TRASH()
 */
class Permissions
{
    /**
     * Cache for permission name lookups to avoid regenerating the matrix on every call.
     */
    private static ?array $cache = null;

    /**
     * Singleton instance of PermissionMatrix to avoid recreating it.
     */
    private static ?PermissionMatrix $matrix = null;

    /**
     * Magic method to handle permission constant calls.
     *
     * Converts calls like Permissions::VIEW_USERS() to the permission string 'view users'.
     *
     * @param  string  $name  The method name (e.g., 'VIEW_USERS')
     * @param  array  $arguments  Arguments (ignored, should be empty)
     * @return string The permission name (e.g., 'view users')
     *
     * @throws Exception If the permission constant name is not found
     */
    public static function __callStatic(string $name, array $arguments): string
    {
        return self::get($name);
    }

    /**
     * Get permission string from constant name.
     *
     * @param  string  $constantName  The constant name (e.g., 'VIEW_USERS')
     * @return string The permission name (e.g., 'view users')
     *
     * @throws Exception If the permission is not found
     */
    private static function get(string $constantName): string
    {
        if (self::$cache === null) {
            self::buildCache();
        }

        if (! isset(self::$cache[$constantName])) {
            throw new Exception("Unknown permission constant: {$constantName}. Check PermissionMatrix for valid permissions.");
        }

        return self::$cache[$constantName];
    }

    /**
     * Build the cache of permission constants from the PermissionMatrix.
     *
     * This creates a mapping from constant names (e.g., 'VIEW_USERS') to
     * permission strings (e.g., 'view users').
     */
    private static function buildCache(): void
    {
        $matrix = self::getMatrix();
        self::$cache = [];

        foreach ($matrix->getPermissionsByEntity() as $entity => $permissions) {
            foreach ($permissions as $permission) {
                $constantName = self::permissionToConstantName($permission);
                self::$cache[$constantName] = $permission;
            }
        }
    }

    /**
     * Convert a permission string to its constant name format.
     *
     * Examples:
     *   'view users' => 'VIEW_USERS'
     *   'edit roles' => 'EDIT_ROLES'
     *   'edit email_template_builder' => 'EDIT_BUILDER_EMAIL_TEMPLATES'
     *
     * @param  string  $permission  The permission string
     * @return string The constant name
     */
    private static function permissionToConstantName(string $permission): string
    {
        return strtoupper(\str_replace(' ', '_', $permission));
    }

    /**
     * Get the PermissionMatrix singleton instance.
     */
    private static function getMatrix(): PermissionMatrix
    {
        if (self::$matrix === null) {
            self::$matrix = new PermissionMatrix();
        }

        return self::$matrix;
    }

    /**
     * Get all permission names from the PermissionMatrix.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return self::getMatrix()->getAllPermissionNames();
    }

    /**
     * Get permissions grouped by entity.
     *
     * @return array<string, array<string>>
     */
    public static function byEntity(): array
    {
        return self::getMatrix()->getPermissionsByEntity();
    }

    /**
     * Get permissions for a specific entity.
     *
     * @param  string  $entity  Entity constant from PermissionEntity
     * @return array<string>
     */
    public static function forEntity(string $entity): array
    {
        $matrix = self::getMatrix();

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
        return self::getMatrix()->getPermissionName($entity, $action);
    }

    /**
     * Clear the permission cache.
     * Useful for testing or when the permission matrix is dynamically updated.
     */
    public static function clearCache(): void
    {
        self::$cache = null;
        self::$matrix = null;
    }
}
