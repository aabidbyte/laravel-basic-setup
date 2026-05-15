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
    case PLANS = 'plans';
    case FEATURES = 'features';
    case SUBSCRIPTIONS = 'subscriptions';
    case TENANTS = 'tenants';
    case EMAIL_TEMPLATES = 'email-templates';

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
            self::PLANS => __('plans.plural'),
            self::FEATURES => __('features.plural'),
            self::SUBSCRIPTIONS => __('subscriptions.index_title'),
            self::TENANTS => __('tenancy.tenants'),
            self::EMAIL_TEMPLATES => __('types.email_templates'),
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
            self::PLANS => __('plans.singular'),
            self::FEATURES => __('features.singular'),
            self::SUBSCRIPTIONS => __('subscriptions.subscription'),
            self::TENANTS => __('tenancy.tenant'),
            self::EMAIL_TEMPLATES => __('types.email_template'),
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
            self::PLANS => 'ticket',
            self::FEATURES => 'sparkles',
            self::SUBSCRIPTIONS => 'credit-card',
            self::TENANTS => 'building-office',
            self::EMAIL_TEMPLATES => 'envelope',
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
