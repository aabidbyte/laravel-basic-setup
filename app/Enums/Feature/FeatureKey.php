<?php

declare(strict_types=1);

namespace App\Enums\Feature;

enum FeatureKey: string
{
    case MAX_USERS = 'max_users';
    case STORAGE = 'storage';
    case API_ACCESS = 'api_access';
    case PRIORITY_SUPPORT = 'priority_support';
    case ERROR_LOGS = 'error_logs';
    case DEV_FEATURE = 'dev_feature';

    public function label(): string
    {
        return __("features.keys.{$this->value}");
    }

    public function valueType(): FeatureValueType
    {
        return match ($this) {
            self::MAX_USERS => FeatureValueType::INTEGER,
            self::API_ACCESS, self::PRIORITY_SUPPORT, self::ERROR_LOGS, self::DEV_FEATURE => FeatureValueType::BOOLEAN,
            self::STORAGE => FeatureValueType::STRING,
        };
    }

    public function defaultValue(): mixed
    {
        return match ($this) {
            self::API_ACCESS, self::PRIORITY_SUPPORT, self::ERROR_LOGS, self::DEV_FEATURE => false,
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    public function nameTranslations(): array
    {
        return match ($this) {
            self::MAX_USERS => ['en_US' => 'Maximum users', 'fr_FR' => 'Utilisateurs maximum'],
            self::STORAGE => ['en_US' => 'Storage', 'fr_FR' => 'Stockage'],
            self::API_ACCESS => ['en_US' => 'API access', 'fr_FR' => 'Accès API'],
            self::PRIORITY_SUPPORT => ['en_US' => 'Priority support', 'fr_FR' => 'Support prioritaire'],
            self::ERROR_LOGS => ['en_US' => 'Error logs', 'fr_FR' => 'Journaux d\'erreur'],
            self::DEV_FEATURE => ['en_US' => 'Development feature', 'fr_FR' => 'Fonctionnalité de développement'],
        };
    }
}
