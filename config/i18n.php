<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | This is the default locale that will be used by the application. This
    | value is used when no locale is explicitly set or when the requested
    | locale is not available.
    |
    */

    'default_locale' => env('APP_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to generate
    | localized first and last names, addresses, and more.
    |
    */

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | This array contains all locales that your application supports. Each
    | locale entry includes metadata such as icon, direction (LTR/RTL),
    | date/time formats, and currency information.
    |
    | When adding a new locale:
    | 1. Add the locale code as a key
    | 2. Configure all required metadata
    | 3. Run `php artisan lang:sync` to sync translations
    |
    */

    'supported_locales' => [
        'en_US' => [
            'native_name' => 'English (US)',
            'english_name' => 'English (United States)',
            'direction' => 'ltr',
            'icon' => [
                'pack' => 'heroicons',
                'name' => 'globe-alt',
            ],
            'date_format' => 'm/d/Y',
            'datetime_format' => 'm/d/Y H:i:s',
            'time_format' => 'H:i:s',
            'currency' => [
                'code' => 'USD',
                'symbol' => '$',
                'precision' => 2,
                'symbol_position' => 'before',
                'decimal_separator' => '.',
                'thousands_separator' => ',',
            ],
        ],
        'fr_FR' => [
            'native_name' => 'Français',
            'english_name' => 'French (France)',
            'direction' => 'ltr',
            'icon' => [
                'pack' => 'heroicons',
                'name' => 'globe-alt',
            ],
            'date_format' => 'd/m/Y',
            'datetime_format' => 'd/m/Y H:i:s',
            'time_format' => 'H:i:s',
            'currency' => [
                'code' => 'EUR',
                'symbol' => '€',
                'precision' => 2,
                'symbol_position' => 'after',
                'decimal_separator' => ',',
                'thousands_separator' => ' ',
            ],
        ],
        // Future RTL support example (commented out until needed):
        // 'ar' => [
        //     'native_name' => 'العربية',
        //     'english_name' => 'Arabic',
        //     'direction' => 'rtl',
        //     'icon' => [
        //         'pack' => 'heroicons',
        //         'name' => 'globe-alt',
        //     ],
        //     'date_format' => 'Y/m/d',
        //     'datetime_format' => 'Y/m/d H:i:s',
        //     'time_format' => 'H:i:s',
        //     'currency' => [
        //         'code' => 'SAR',
        //         'symbol' => 'ر.س',
        //         'precision' => 2,
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Namespaces
    |--------------------------------------------------------------------------
    |
    | These are the semantic namespaces used for organizing translations.
    | - 'ui': User interface elements (buttons, labels, navigation, etc.)
    | - 'messages': System messages, notifications, alerts, etc.
    |
    | Use semantic keys by default: `__('ui.auth.login.title')`
    | JSON string keys should only be used for very small UI labels.
    |
    */

    'namespaces' => [
        'ui',
        'messages',
    ],

    /*
    |--------------------------------------------------------------------------
    | Extracted Translation File
    |--------------------------------------------------------------------------
    |
    | Newly discovered translations that haven't been organized yet will be
    | placed in this file. You can later move them to the appropriate
    | namespace file (ui.php or messages.php).
    |
    */

    'extracted_file' => 'extracted',

    /*
    |--------------------------------------------------------------------------
    | Protected Translation Files
    |--------------------------------------------------------------------------
    |
    | These translation files are critical Laravel system files and should
    | never be pruned or modified by the lang:sync command. They are managed
    | by Laravel core and should only be updated manually when needed.
    |
    */

    'protected_translation_files' => [
        'validation',
        'auth',
        'pagination',
        'passwords',
    ],

];
