<?php

declare(strict_types=1);

namespace App\Constants\Auth;

/**
 * Permission action constants.
 *
 * Defines all permission action types that can be applied to entities.
 * Not all entities support all actions - see PermissionMatrix for mappings.
 *
 * CRITICAL RULE: All action names must be defined here as constants.
 * NO HARDCODED STRINGS ARE ALLOWED for action names throughout the application.
 */
class PermissionAction
{
    // Standard CRUD actions
    public const VIEW = 'view';

    public const CREATE = 'create';

    public const EDIT = 'edit';

    public const DELETE = 'delete';

    // Extended actions
    public const RESTORE = 'restore';

    public const FORCE_DELETE = 'force_delete';

    public const EXPORT = 'export';

    // Publishing actions
    public const PUBLISH = 'publish';

    public const UNPUBLISH = 'unpublish';

    // Special actions
    public const RESOLVE = 'resolve';

    public const ACTIVATE = 'activate';

    public const CONFIGURE = 'configure';

    public const GENERATE_ACTIVATION = 'generate_activation';

    /**
     * Get all action constants as an array.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::VIEW,
            self::CREATE,
            self::EDIT,
            self::DELETE,
            self::RESTORE,
            self::FORCE_DELETE,
            self::EXPORT,
            self::PUBLISH,
            self::UNPUBLISH,
            self::RESOLVE,
            self::ACTIVATE,
            self::CONFIGURE,
            self::GENERATE_ACTIVATION,
        ];
    }

    /**
     * Get standard CRUD actions.
     *
     * @return array<string>
     */
    public static function crud(): array
    {
        return [
            self::VIEW,
            self::CREATE,
            self::EDIT,
            self::DELETE,
        ];
    }

    /**
     * Get the translation key for an action.
     *
     * @param  string  $action  Action constant
     * @return string Translation key
     */
    public static function getTranslationKey(string $action): string
    {
        return "permissions.actions.{$action}";
    }

    /**
     * Get the translated label for an action.
     *
     * @param  string  $action  Action constant
     * @return string Translated label
     */
    public static function getLabel(string $action): string
    {
        return __("permissions.actions.{$action}");
    }
}
