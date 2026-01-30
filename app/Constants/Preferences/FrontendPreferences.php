<?php

declare(strict_types=1);

namespace App\Constants\Preferences;

class FrontendPreferences
{
    /**
     * Session key for storing preferences cache.
     */
    public const SESSION_KEY = 'frontend_preferences';

    /**
     * Preference keys.
     */
    public const KEY_LOCALE = 'locale';

    public const KEY_THEME = 'theme';

    public const KEY_TIMEZONE = 'timezone';

    public const KEY_DATATABLES = 'datatables';

    /**
     * Default preference values.
     */
    public const DEFAULT_LOCALE = 'en_US';

    public const DEFAULT_THEME = 'light';

    public const DEFAULT_TIMEZONE = 'UTC';

    /**
     * Allowed theme values.
     */
    public const THEMES = [
        'light',
        'dark',
    ];

    /**
     * Get all default preferences.
     *
     * @return array<string, mixed>
     */
    public static function getDefaults(): array
    {
        return [
            self::KEY_LOCALE => self::DEFAULT_LOCALE,
            self::KEY_THEME => self::DEFAULT_THEME,
            self::KEY_TIMEZONE => self::DEFAULT_TIMEZONE,
            self::KEY_DATATABLES => [],
        ];
    }

    /**
     * Check if a theme value is valid.
     */
    public static function isValidTheme(string $theme): bool
    {
        return \in_array($theme, self::THEMES, true);
    }
}
