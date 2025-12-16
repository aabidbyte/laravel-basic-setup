# Internationalization System

This document describes the centralized internationalization (i18n) system used in this Laravel application.

## Overview

The application uses a centralized i18n configuration system that:

-   Centralizes all locale settings in `config/i18n.php`
-   Uses **semantic translation keys** by default (e.g., `ui.auth.login.title`)
-   Supports multiple locales with metadata (icons, date formats, currency, RTL support)
-   Provides a `lang:sync` command to automatically sync translations across locales
-   Protects critical Laravel translation files from being pruned

## Configuration

### `config/i18n.php`

All internationalization settings are centralized in `config/i18n.php`:

```php
return [
    'default_locale' => 'en_US',
    'fallback_locale' => 'en_US',
    'faker_locale' => 'en_US',

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
            ],
        ],
        // ... more locales
    ],

    'namespaces' => ['ui', 'messages'],
    'extracted_file' => 'extracted',
    'protected_translation_files' => ['validation', 'auth', 'pagination', 'passwords'],
];
```

### Locale Metadata

Each supported locale includes:

-   **native_name**: The locale name in its native language
-   **english_name**: The locale name in English
-   **direction**: Text direction (`ltr` or `rtl`) - used for RTL language support
-   **icon**: Icon configuration for UI display (uses `<x-ui.icon>` component)
-   **date_format**: PHP date format string for dates
-   **datetime_format**: PHP date format string for date-time values
-   **time_format**: PHP date format string for time values
-   **currency**: Currency configuration (code, symbol, precision, symbol_position, decimal_separator, thousands_separator)

### Integration with `config/app.php`

The `config/app.php` file sources locale settings from `config/i18n.php`:

```php
'locale' => config('i18n.default_locale', 'en_US'),
'fallback_locale' => config('i18n.fallback_locale', 'en_US'),
'faker_locale' => config('i18n.faker_locale', 'en_US'),
```

## Translation Key Conventions

### Semantic Keys (Default)

**Always use semantic keys by default.** Semantic keys are organized by namespace and module:

```php
// UI elements
__('ui.navigation.dashboard')
__('ui.auth.login.title')
__('ui.settings.profile.name_label')

// System messages
__('messages.notifications.success')
__('messages.errors.validation_failed')
```

**Key Structure:**

-   `ui.*` - User interface elements (buttons, labels, navigation, forms)
-   `messages.*` - System messages, notifications, alerts, errors

**Organization:**

-   Keys are organized by module/feature (e.g., `ui.auth.*`, `ui.settings.*`)
-   Use descriptive, hierarchical keys (e.g., `ui.settings.profile.email_label`)

### JSON String Keys (Optional)

JSON string keys should **only** be used for very small UI labels when semantic keys are impractical. This is not the default approach.

## Translation File Structure

### Directory Structure

```
lang/
├── en_US/              # Default locale (source of truth)
│   ├── ui.php          # UI translations
│   ├── messages.php    # System messages
│   ├── extracted.php   # Newly discovered translations (temporary)
│   ├── validation.php  # Laravel validation (protected)
│   ├── auth.php        # Laravel auth (protected)
│   ├── pagination.php  # Laravel pagination (protected)
│   └── passwords.php   # Laravel passwords (protected)
└── fr_FR/              # French locale
    ├── ui.php
    ├── messages.php
    └── ... (same structure)
```

### File Organization

-   **`ui.php`**: All user interface translations organized by module
-   **`messages.php`**: System messages, notifications, alerts
-   **`extracted.php`**: Newly discovered translations that need to be organized (temporary)
-   **Protected files**: `validation.php`, `auth.php`, `pagination.php`, `passwords.php` (never pruned)

## RTL Support

The system includes first-class RTL (Right-to-Left) support for languages like Arabic.

### HTML Direction Attribute

Layout components automatically set the `dir` attribute based on the current locale:

```blade
@php
    $locale = app()->getLocale();
    $supportedLocales = config('i18n.supported_locales', []);
    $direction = $supportedLocales[$locale]['direction'] ?? 'ltr';
@endphp
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $direction }}">
```

### Tailwind RTL Classes

The application uses Tailwind's `rtl:` variant for RTL-specific styling:

```blade
<div class="ms-2 me-5 rtl:space-x-reverse">
    <!-- Content -->
</div>
```

## The `lang:sync` Command

The `lang:sync` command automatically syncs translations across all locales.

### Basic Usage

```bash
# Dry-run (default - shows what would be done)
php artisan lang:sync

# Actually write changes
php artisan lang:sync --write
```

### Command Options

-   **`--write`**: Actually write changes to files (default is dry-run)
-   **`--prune`**: Remove unused keys from non-protected files (safe)
-   **`--prune-all`**: Remove unused keys from all files including `ui.php` and `messages.php`
-   **`--allow-json`**: Allow JSON string keys for literal strings (not recommended)

### How It Works

1. **Scans codebase**: Finds all translation keys used in PHP and Blade files
2. **Syncs locales**: Uses default locale (`en_US`) as source of truth
3. **Adds missing keys**: Adds any missing keys to other locales (with default locale values)
4. **Prunes unused keys** (optional): Removes keys not found in codebase (respects protected files)

### Safety Features

-   **Dry-run by default**: Shows what would be done without making changes
-   **Protected files**: Never prunes or modifies `validation.php`, `auth.php`, `pagination.php`, `passwords.php`
-   **Safe pruning**: By default, only prunes `extracted.php` unless `--prune-all` is used

### Example Output

```
Scanning codebase for translation usage...
Found 156 translation keys in codebase.
Syncing locales...
Updated: fr_FR/ui.php
Updated: fr_FR/messages.php

=== Sync Summary ===
+------------------+-------+
| Metric           | Count |
+------------------+-------+
| Keys found       | 156   |
| Keys added       | 12    |
| Keys pruned      | 0     |
| Files updated    | 2     |
+------------------+-------+
```

## Adding a New Locale

To add a new locale:

1. **Add to `config/i18n.php`**:

```php
'supported_locales' => [
    // ... existing locales
    'ar' => [
        'native_name' => 'العربية',
        'english_name' => 'Arabic',
        'direction' => 'rtl',  // RTL support
        'icon' => [
            'pack' => 'heroicons',
            'name' => 'globe-alt',
        ],
        'date_format' => 'Y/m/d',
        'datetime_format' => 'Y/m/d H:i:s',
        'time_format' => 'H:i:s',
        'currency' => [
            'code' => 'SAR',
            'symbol' => 'ر.س',
            'precision' => 2,
        ],
    ],
],
```

2. **Create locale directory**:

```bash
mkdir -p lang/ar
```

3. **Copy structure from default locale**:

```bash
cp lang/en_US/*.php lang/ar/
```

4. **Run sync command**:

```bash
php artisan lang:sync --write
```

5. **Translate the keys**: Edit `lang/ar/ui.php` and `lang/ar/messages.php` with translations

## Pluralization

Laravel's pluralization system is fully supported. Use `trans_choice()` for pluralized translations:

```php
trans_choice('messages.items.count', $count, ['count' => $count])
```

In translation files:

```php
// lang/en_US/messages.php
return [
    'items' => [
        'count' => '{0} No items|{1} One item|[2,*] :count items',
    ],
];
```

## Best Practices

1. **Always use semantic keys**: `__('ui.auth.login.title')` not `__('Log in')`
2. **Organize by namespace**: UI elements in `ui.*`, system messages in `messages.*`
3. **Use descriptive keys**: `ui.settings.profile.email_label` not `ui.email`
4. **Run `lang:sync` regularly**: Keep translations in sync across locales
5. **Review `extracted.php`**: Move newly discovered keys to appropriate namespace files
6. **Never modify protected files manually**: Let Laravel manage `validation.php`, `auth.php`, etc.
7. **Test with multiple locales**: Ensure RTL support works correctly for RTL languages

## Troubleshooting

### Missing translations

If you see a translation key instead of the translated text:

1. Check if the key exists in `lang/{locale}/ui.php` or `lang/{locale}/messages.php`
2. Run `php artisan lang:sync --write` to sync missing keys
3. Ensure the locale is in `config/i18n.php`'s `supported_locales`

### RTL not working

1. Verify `direction` is set to `rtl` in `config/i18n.php` for the locale
2. Check that layout files include the `dir` attribute
3. Ensure Tailwind `rtl:` classes are used where needed

### Sync command not finding keys

1. Ensure keys use semantic format: `__('ui.module.key')`
2. Check that files are in `app/` or `resources/views/`
3. Verify file extensions are `.php` or `.blade.php`

## Helper Functions

The application provides helper functions for formatting dates, times, and currency according to the current locale settings.

### Date/Time Helpers

Located in `app/helpers/dateTime.php`:

-   **`formatDate($date, ?string $locale = null): string`** - Formats a date using the locale's `date_format`
-   **`formatTime($time, ?string $locale = null): string`** - Formats a time using the locale's `time_format`
-   **`formatDateTime($datetime, ?string $locale = null): string`** - Formats a datetime using the locale's `datetime_format`

**Usage:**

```php
// In Blade templates
{{ formatDate(now()) }}           // "12/16/2025" (en_US) or "16/12/2025" (fr_FR)
{{ formatTime(now()) }}           // "14:30:00"
{{ formatDateTime(now()) }}       // "12/16/2025 14:30:00" (en_US)

// With locale override
{{ formatDate('2025-12-16', 'fr_FR') }}  // "16/12/2025"
```

**Input Types:**

-   Carbon instances: `formatDate(Carbon::now())`
-   DateTime objects: `formatDate(new DateTime())`
-   Date strings: `formatDate('2025-12-16')`
-   Null/empty: Returns empty string

### Currency Helper

Located in `app/helpers/currency.php`:

-   **`formatCurrency($amount, ?string $locale = null, ?string $currencyCode = null): string`** - Formats currency using locale's currency settings

**Usage:**

```php
// In Blade templates
{{ formatCurrency(100.50) }}                    // "$100.50" (en_US) or "100,50 €" (fr_FR)
{{ formatCurrency(1000.50, 'fr_FR') }}         // "1 000,50 €"
{{ formatCurrency(100.50, null, 'EUR') }}      // Uses EUR formatting
```

**Features:**

-   Locale-specific decimal separators (`.` for en_US, `,` for fr_FR)
-   Locale-specific thousands separators (`,` for en_US, ` ` for fr_FR)
-   Configurable symbol position (`before` or `after`)
-   Currency code override support
-   Automatic fallback to default locale if locale not supported

**Input Types:**

-   Float: `formatCurrency(100.50)`
-   Integer: `formatCurrency(100)`
-   String: `formatCurrency('100.50')`
-   Null/empty: Returns empty string

### I18nService

All helper functions use `I18nService` internally to access locale configuration. The service provides:

-   `getLocale()` - Get current locale
-   `getDefaultLocale()` - Get default locale
-   `getFallbackLocale()` - Get fallback locale
-   `getSupportedLocales()` - Get all supported locales
-   `getValidLocale(?string $locale)` - Get valid locale (fallback to default if not supported)
-   `getLocaleMetadata(?string $locale)` - Get locale metadata
-   `isLocaleSupported(string $locale)` - Check if locale is supported
-   `isRtl(?string $locale)` - Check if locale is RTL
-   `getHtmlLangAttribute()` - Get HTML lang attribute value
-   `getHtmlDirAttribute()` - Get HTML dir attribute value

**Always use `I18nService` for locale-related code** - Do not directly access `config('i18n.*')`.

## View Composers

The application uses View Composers to share services with Blade templates. The `BladeServiceProvider` registers composers for:

-   **I18nService**: Shared with all layout templates (`components.layouts.app`, `components.layouts.app.*`, `components.layouts.auth`, `components.layouts.auth.*`)
-   **SideBarMenuService**: Shared only with `components.layouts.app.sidebar`

**Usage in Blade:**

```blade
{{-- I18nService is automatically available as $i18n --}}
<html lang="{{ $i18n->getHtmlLangAttribute() }}" dir="{{ $i18n->getHtmlDirAttribute() }}">

{{-- SideBarMenuService is automatically available as $menuService in sidebar --}}
<x-layouts.app.sidebar>
    {{-- $menuService is available here --}}
</x-layouts.app.sidebar>
```

**Note**: Do not use `@inject` directives for services that are shared via View Composers. Use View Composers for global data instead.

## Reference

-   **Config file**: `config/i18n.php`
-   **Default locale translations**: `lang/en_US/`
-   **Command**: `php artisan lang:sync`
-   **Helper functions**: `app/helpers/dateTime.php`, `app/helpers/currency.php`
-   **Service**: `App\Services\I18nService`
-   **View Composers**: `App\Providers\BladeServiceProvider`
-   **Laravel localization docs**: https://laravel.com/docs/localization
