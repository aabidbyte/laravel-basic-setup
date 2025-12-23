<?php

declare(strict_types=1);

namespace App\Constants;

class DataTable
{
    /**
     * Session key for storing DataTable preferences.
     */
    public const SESSION_KEY = 'datatable_preferences';

    /**
     * Preference key prefix for user's frontend_preferences JSON column.
     */
    public const USER_PREF_KEY_PREFIX = 'datatable_preferences';

    /**
     * Get user preference key for a specific entity.
     */
    public static function getUserPreferenceKey(string $entityKey): string
    {
        return self::USER_PREF_KEY_PREFIX.'.'.$entityKey;
    }

    /**
     * Get session key for a specific entity (for backward compatibility).
     */
    public static function getSessionKey(string $entityKey): string
    {
        return self::SESSION_KEY.'.'.$entityKey;
    }
}
