<?php

declare(strict_types=1);

namespace App\Enums\Trash;

/**
 * Enum for all trashable entity types.
 */
enum TrashEntityType: string
{
    case USERS = 'users';
    case ROLES = 'roles';
    case TEAMS = 'teams';
    case ERROR_LOGS = 'error-logs';

    /**
     * Get the plural label for the entity.
     */
    public function label(): string
    {
        return match ($this) {
            self::USERS => __('types.users'),
            self::ROLES => __('types.roles'),
            self::TEAMS => __('types.teams'),
            self::ERROR_LOGS => __('types.error_logs'),
        };
    }

    /**
     * Get the singular label for the entity.
     */
    public function singularLabel(): string
    {
        return match ($this) {
            self::USERS => __('types.user'),
            self::ROLES => __('types.role'),
            self::TEAMS => __('types.team'),
            self::ERROR_LOGS => __('types.error_log'),
        };
    }

    /**
     * Get the icon for the entity.
     */
    public function icon(): string
    {
        return match ($this) {
            self::USERS => 'users',
            self::ROLES => 'shield-check',
            self::TEAMS => 'user-group',
            self::ERROR_LOGS => 'exclamation-triangle',
        };
    }

    /**
     * Get all entity types as an array of strings.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return \array_column(self::cases(), 'value');
    }
}
