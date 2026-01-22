<?php

declare(strict_types=1);

namespace App\Constants\Auth;

/**
 * Permission entity constants.
 *
 * Defines all permissionable entities in the system.
 * Each entity can have different permission actions applied.
 *
 * CRITICAL RULE: All entity names must be defined here as constants.
 * NO HARDCODED STRINGS ARE ALLOWED for entity names throughout the application.
 */
class PermissionEntity
{
    // User management
    public const USERS = 'users';

    public const ROLES = 'roles';

    public const TEAMS = 'teams';

    // System administration
    public const ERROR_LOGS = 'error_logs';

    public const TELESCOPE = 'telescope';

    public const HORIZON = 'horizon';

    // Settings
    public const MAIL_SETTINGS = 'mail_settings';

    // Email templates
    public const EMAIL_TEMPLATES = 'email_templates';

    public const EMAIL_LAYOUTS = 'email_layouts';

    /**
     * Get all entity constants as an array.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::USERS,
            self::ROLES,
            self::TEAMS,
            self::ERROR_LOGS,
            self::TELESCOPE,
            self::HORIZON,
            self::MAIL_SETTINGS,
            self::EMAIL_TEMPLATES,
            self::EMAIL_LAYOUTS,
        ];
    }

    /**
     * Get the translation key for an entity.
     *
     * @param  string  $entity  Entity constant
     * @return string Translation key
     */
    public static function getTranslationKey(string $entity): string
    {
        return "permissions.entities.{$entity}";
    }

    /**
     * Get the translated label for an entity.
     *
     * @param  string  $entity  Entity constant
     * @return string Translated label
     */
    public static function getLabel(string $entity): string
    {
        return __("permissions.entities.{$entity}");
    }
}
