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
        ],
        // ... more locales
    ],

    // Namespaces are automatically discovered from lang/en_US/*.php files
    'namespaces' => [],
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

### Integration with `config/app.php`

The `config/app.php` file sources locale settings from `config/i18n.php`:

```php
'locale' => config('i18n.default_locale', 'en_US'),
'fallback_locale' => config('i18n.fallback_locale', 'en_US'),
'faker_locale' => config('i18n.faker_locale', 'en_US'),
```

## Translation Key Conventions

### Semantic Keys (Default)

 **Always use semantic keys by default.** Semantic keys are now organized by dedicated files (namespaces) for better modularity.

 **Note**: Namespaces are **automatically discovered** from the files in your `lang/en_US/` directory. You do not need to register them in the config.

 ```php
 // UI elements
 __('navigation.dashboard')
 __('authentication.login.title')
 __('settings.profile.name_label')

 // Generic Pages
 __('pages.common.create.title', ['type' => __('types.user')])

 // Specific Resource
 __('users.name')

 // System messages
 __('notifications.success')
 ```

 **Key Structure:**

 -   `navigation.*` - Navigation items
 -   `actions.*` - Common buttons and actions
 -   `authentication.*` - Auth related labels (login, register...)
 -   `settings.*` - Settings pages
 -   `pages.*` - Generic page titles/descriptions
 -   `users.*`, `types.*`, etc. - Resource specific labels

 ### JSON String Keys (Optional)

 JSON string keys should **only** be used for very small UI labels when semantic keys are impractical. This is not the default approach.

 ## Translation File Structure

 ### Directory Structure

 ```
 lang/
 ├── en_US/              # Default locale (source of truth)
 │   ├── actions.php     # Common actions (Save, Edit...)
 │   ├── authentication.php # Auth translations
 │   ├── common.php      # Common UI terms
 │   ├── messages.php    # System messages
 │   ├── navigation.php  # Navigation items
 │   ├── pages.php       # Page titles/descriptions
 │   ├── settings.php    # Settings
 │   ├── types.php       # Entity types (User, Team...)
 │   ├── users.php       # User resource labels
 │   ├── ...             # Other modules (modals, table, notifications...)
 │   └── extracted.php   # Newly discovered translations (temporary)
 └── fr_FR/              # French locale
     ├── actions.php
     ├── ... (same structure)
 ```

 ### File Organization

 -   **`pages.php`**: Generic page translations (Common CRUD patterns)
 -   **`users.php`**: Resource-specific field labels and customized messages
 -   **`types.php`**: Entity type names (Singular/Plural)
 -   **`actions.php`**: Shared button/link text
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
3. **Adds missing keys**: Adds any missing keys to other locales.
    - New keys are added with value: `"TRANSLATION_NEEDED: Please see context at path/to/file.php:123"`
    - Updates existing keys that contain raw file paths to the new context format
4. **Prunes unused keys** (optional): Removes keys not found in codebase (respects protected files)

### Detection Capabilities

The command detects translation keys in various formats:

-   **Standard calls**: `__('key')`, `@lang('key')`, `trans('key')`
-   **Parameterized calls**: `__('key', ['params' => ...])`
-   **Notification Builder**: `->title('key')` and `->subtitle('key')` (automatically detected as keys)
-   **Concatenation patterns**: `__('locales.' . $code)`, `__('permissions.actions.' . $action)` (detected as dynamic keys)

### Dynamic Key Detection

When the command detects translation keys containing PHP variable interpolation or concatenation, it adds a special `DYNAMIC_KEY` instruction instead of `TRANSLATION_NEEDED`.

**Patterns detected as dynamic:**
- Variable interpolation: `"permissions.actions.{$action}"`
- String concatenation: `__('locales.' . $translation->locale)`
- Direct variable: `__("prefix.$variable")`

**Example output:**

```php
'locales.{$code}' => 'DYNAMIC_KEY: This translation key is dynamically constructed using PHP variables or concatenation. 
AI agents should resolve this by following these steps:

1. Navigate to the source location: resources/views/pages/settings/⚡preferences.blade.php:52
2. Identify where the dynamic portion comes from (e.g., loop variables, class constants, service methods like I18nService::getSupportedLocales())
3. Find ALL possible values for the variable (e.g., \'en_US\', \'fr_FR\' for locale codes)
4. Create individual translation entries for EACH resolved value:
   - Example: If key is \'locales.{$code}\', create \'locales.en_US\' => \'English (US)\', \'locales.fr_FR\' => \'Français\', etc.
5. REPLACE this dynamic pattern entry with the expanded translations (do not just remove it)

The goal is to transform dynamic keys into concrete, translatable entries.',
```

**For AI agents**: When you encounter a `DYNAMIC_KEY` entry:

1. **Navigate to source**: Go to the file and line mentioned in the instruction
2. **Identify the source**: Find where the dynamic values come from:
   - Loop variables (e.g., `@foreach($locales as $code => $name)`)
   - Service methods (e.g., `I18nService::getSupportedLocales()`)
   - Constants classes (e.g., `PermissionAction::all()`)
   - Model relationships or collections
3. **Extract all values**: Get ALL possible values for the variable
   - For locales: Check `config/i18n.php` supported_locales keys
   - For constants: Check the constants class methods
   - For models: Query the database or check seeders
   - **TIP**: Use PHP Thinker if available to execute code and resolve values (e.g., `app(I18nService::class)->getSupportedLocales()`)
4. **Create expanded entries**: Add individual translation entries:
   ```php
   // Instead of: 'locales.{$code}' => 'DYNAMIC_KEY...'
   // Create:
   'locales.en_US' => 'English (US)',
   'locales.fr_FR' => 'Français',
   ```
5. **Replace the pattern**: Remove the `DYNAMIC_KEY` entry after creating all expanded translations
6. **Update all locales**: Ensure the expanded translations are added to all supported language files



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

## Locale Switching

The application uses the **Frontend Preferences Service** (`App\Services\FrontendPreferences\FrontendPreferencesService`) to manage user locale preferences. Users can switch locales via the language switcher component in the application header/sidebar.

### How It Works

1. **User Preference Storage**:

    - **Guest users**: Locale preference stored in session
    - **Authenticated users**: Locale preference stored in `users.frontend_preferences` JSON column + cached in session

2. **Automatic Application**:

    - The `ApplyFrontendPreferences` middleware automatically sets `app()->setLocale()` on each request
    - Locale is validated via `I18nService::getValidLocale()` to ensure it's supported

3. **UI Component**:
    - Language switcher component (`livewire:preferences.switchers`) displays all supported locales
    - Uses locale metadata from `config/i18n.php` (icons, native names)
    - Changing locale triggers a page reload to apply the new locale

### Usage

**In Code:**

```php
use App\Services\FrontendPreferences\FrontendPreferencesService;

$preferences = app(FrontendPreferencesService::class);

// Get current locale preference
$locale = $preferences->getLocale(); // Returns validated locale

// Set locale preference
$preferences->setLocale('fr_FR'); // Validated via I18nService
```

**Note**: The application locale is automatically set by middleware based on user preferences. You typically don't need to manually call `app()->setLocale()`.

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
8. **Bulk Actions**: Do not include the word "selected" in bulk action translations (e.g., use "Delete" instead of "Delete Selected"). The context of a bulk action automatically implies it applies to selected items.

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

-   **`formatDate($date, ?string $locale = null, ?string $timezone = null): string`** - Formats a date using localized Carbon format (`isoFormat('L')`) and user's timezone preference
-   **`formatTime($time, ?string $locale = null, ?string $timezone = null): string`** - Formats a time using localized Carbon format (`isoFormat('LT')`) and user's timezone preference
-   **`formatDateTime($datetime, ?string $locale = null, ?string $timezone = null): string`** - Formats a datetime using localized Carbon format (`isoFormat('L LT')`) and user's timezone preference

**Usage:**

```php
// In Blade templates
{{ formatDate(now()) }}           // "12/16/2025" (en_US) or "16/12/2025" (fr_FR)
{{ formatTime(now()) }}           // "2:30 PM" (en_US) or "14:30" (fr_FR)
{{ formatDateTime(now()) }}       // "12/16/2025 2:30 PM" (en_US) or "16/12/2025 14:30" (fr_FR)

// With locale override
{{ formatDate('2025-12-16', 'fr_FR') }}  // "16/12/2025"
```

**Timezone Handling:**
-   All dates/times are stored in the database using the application timezone from `config/app.php`
-   The helpers automatically convert dates/times to the user's timezone preference (from `FrontendPreferencesService`) when displaying
-   You can pass a timezone override as the third parameter

**Input Types:**

-   Carbon instances: `formatDate(Carbon::now())`
-   DateTime objects: `formatDate(new DateTime())`
-   Date strings: `formatDate('2025-12-16')`
-   Null/empty: Returns empty string

### Currency Helper

Located in `app/helpers/currency.php`:

-   **`formatCurrency($amount, ?string $currencyCode = 'USD', ?string $locale = 'en_US'): string`** - Formats currency using Laravel's `Number::currency()` helper

**Usage:**

```php
// In Blade templates
{{ formatCurrency(100.50) }}                    // "$100.50" (defaults to USD, en_US)
{{ formatCurrency(100.50, 'EUR', 'fr_FR') }}    // "100,50 €"
{{ formatCurrency(100.50, 'JPY') }}             // "¥101"
```

**Features:**
-   Uses native `Number::currency()` formatting
-   Supports standard currency codes (USD, EUR, GBP, etc.)
-   Configurable locale for formatting rules (separators, symbol position)

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

The application uses View Composers to share data with Blade templates. The `BladeServiceProvider` is organized into separate methods for better maintainability:

-   **`initLayoutVariables()`**: Shares theme, locale, and HTML attributes with layout templates
-   **`initPageTitle()`**: Shares page title with header and head partials
-   **`initPageSubtitle()`**: Shares page subtitle with header and head partials

**Shared Variables:**

-   **Layout Templates** (`components.layouts.app`, `components.layouts.auth`, `layouts::app`, `layouts::auth`):
    -   `$currentTheme` - Current theme (light/dark)
    -   `$htmlLangAttribute` - HTML lang attribute value
    -   `$htmlDirAttribute` - HTML dir attribute value (ltr/rtl)
-   **Locale Switcher** (`components.preferences.locale-switcher`):
    -   `$currentLocale` - Current locale
    -   `$supportedLocales` - Array of supported locales
    -   `$localeMetadata` - Metadata for current locale (icon, name, etc.)

**Usage in Blade:**

```blade
{{-- Specific values are automatically available in layout templates --}}
<html lang="{{ $htmlLangAttribute }}" dir="{{ $htmlDirAttribute }}" data-theme="{{ $currentTheme }}">

{{-- Locale metadata is automatically available in locale switcher --}}
<x-preferences.locale-switcher>
    {{-- $currentLocale, $supportedLocales, $localeMetadata are available --}}
</x-preferences.locale-switcher>
```

**Note**: Do not use `@inject` directives for services that are shared via View Composers. The provider shares specific values rather than service objects for better performance and clarity.

## History

### DateTime and Currency Helper Functions (2025-12-16)

Created locale-aware helper functions for formatting dates, times, and currency:

- Created `app/helpers/dateTime.php` with `formatDate()`, `formatTime()`, and `formatDateTime()` functions
- Created `app/helpers/currency.php` with `formatCurrency()` function
- Updated `config/i18n.php` to include `symbol_position`, `decimal_separator`, and `thousands_separator` for currency configuration
- All helpers use `I18nService` internally instead of direct config access
- Added comprehensive tests (18 tests for dateTime, 14 tests for currency)
- Updated `composer.json` to autoload new helper files

### I18nService Enhancements (2025-12-16)

Enhanced `I18nService` with additional methods for centralized locale management:

- Added `getSupportedLocales()`, `getDefaultLocale()`, `getFallbackLocale()`
- Added `getLocaleMetadata(?string $locale)`, `isLocaleSupported()`, `getValidLocale()`
- Updated service to use its own methods internally for consistency
- Removed default fallback values - these methods now rely entirely on `config('i18n.*')` values
- Added comprehensive tests (18 tests)

### BladeServiceProvider (2025-12-16)

Created dedicated service provider for Blade/view-related functionality:

- Moved View Composer logic from `AppServiceProvider` to `BladeServiceProvider`
- Shares `I18nService` with layout templates via View Composers
- Shares `SideBarMenuService` only with sidebar template
- Replaced all `@inject` directives with View Composers
- Changed from sharing service objects to sharing specific values (`$htmlLangAttribute`, `$currentTheme`, etc.)
- Added comprehensive tests (4 tests)

## Reference

-   **Config file**: `config/i18n.php`
-   **Default locale translations**: `lang/en_US/`
-   **Command**: `php artisan lang:sync`
-   **Helper functions**: `app/helpers/dateTime.php`, `app/helpers/currency.php`
-   **Service**: `App\Services\I18nService`
-   **View Composers**: `App\Providers\BladeServiceProvider`
-   **Laravel localization docs**: https://laravel.com/docs/localization
