## Common Tasks

### Creating a New Livewire Component

```bash
# Full-page component (creates in pages/ directory)
php artisan make:livewire pages.example-page --test --pest

# Nested/reusable component (creates in components/ directory)
php artisan make:livewire ui.example-component --test --pest

# Multi-file component
php artisan make:livewire pages.example --mfc --test --pest

# Convert between formats
php artisan livewire:convert pages.example
```

**Important**:

-   **ALL full-page Livewire components MUST extend `App\Livewire\BasePageComponent`** (not `Livewire\Component`)
-   After creating a full-page component, update it to extend `BasePageComponent` and add `public ?string $pageTitle = 'ui.pages.example';` (use translation keys)
-   **Optional**: Add `public string $pageSubtitle = 'ui.pages.example.description';` for subtitle text (displayed below title in header)
-   **Translations**: Use translation keys like `'ui.pages.dashboard'` - they are automatically translated
-   **No `parent::mount()` needed** - title and subtitle sharing happens automatically via `boot()` lifecycle hook
-   Full-page components are created in `resources/views/pages/` and use `pages::` namespace in routes
-   Nested/reusable Livewire components are created in `resources/views/components/` and are referenced directly (e.g., `livewire:ui.example-component`)
-   All single-file components use `.blade.php` extension
-   See `docs/livewire-4/index.md` for complete documentation

### Creating a Model with Factory

```bash
php artisan make:model Product --factory --migration
```

**Important**: After creating the model and migration:

1.  **Migration**: Ensure it includes a UUID column:

    ```php
    $table->uuid('uuid')->unique()->index();
    ```

2.  **Model**: Extend the appropriate base model class:

    ```php
    // For regular models
    use App\Models\Base\BaseModel;

    class Product extends BaseModel
    {
        // BaseModel includes HasUuid automatically
    }

    // For authenticatable models (users, admins, etc.)
    use App\Models\Base\BaseUserModel;

    class Admin extends BaseUserModel
    {
        // BaseUserModel includes HasUuid, HasFactory, Notifiable automatically
    }
    ```

### Creating a Feature Test

```bash
php artisan make:test --pest Feature/ExampleTest
```

### Creating a Form Request

```bash
php artisan make:request StoreProductRequest
```

### Clearing Logs

Use the `logs:clear` command to clear log files:

```bash
# Clear all log files
php artisan logs:clear

# Clear logs for a specific level only
php artisan logs:clear --level=error
php artisan logs:clear --level=info
php artisan logs:clear --level=warning
```

The command clears:

-   Main log files (`laravel.log`, `browser.log`)
-   All level-specific log folders (emergency, alert, critical, error, warning, notice, info, debug)
-   Deprecated logs folder

### Internationalization System

The application uses a centralized internationalization (i18n) system. **See `docs/internationalization.md` for complete documentation.**

#### Key Rules

-   **Always use semantic translation keys by default**: `__('ui.auth.login.title')` not `__('Log In')`
-   **Use JSON string keys only for very small UI labels** (optional, not recommended)
-   **Translation keys are organized by namespace**:
    -   `ui.*` - User interface elements (buttons, labels, navigation, forms)
    -   `messages.*` - System messages, notifications, alerts, errors
-   **All locale settings are centralized in `config/i18n.php`**
-   **Default locale (`en_US`) is the source of truth** for syncing translations
-   **Protected files** (`validation.php`, `auth.php`, `pagination.php`, `passwords.php`) are never pruned by `lang:sync`

#### Translation File Structure

```
lang/
├── en_US/              # Default locale (source of truth)
│   ├── ui.php          # UI translations
│   ├── messages.php    # System messages
│   ├── extracted.php   # Newly discovered translations (temporary)
│   └── [protected files]
└── fr_FR/              # Other locales
    └── [same structure]
```

#### The `lang:sync` Command

```bash
# Dry-run (default - shows what would be done)
php artisan lang:sync

# Actually write changes
php artisan lang:sync --write

# Prune unused keys (safe - only extracted.php)
php artisan lang:sync --write --prune

# Prune unused keys from all files (including ui.php, messages.php)
php artisan lang:sync --write --prune-all
```

The command:

-   Scans PHP and Blade files for translation usage
-   Uses default locale as source of truth
-   Syncs missing keys to other locales
-   Optionally prunes unused keys (respects protected files)

#### Helper Functions

The application provides locale-aware helper functions for formatting dates, times, and currency:

**Date/Time Helpers** (`app/helpers/dateTime.php`):

-   `formatDate($date, ?string $locale = null): string` - Format dates using locale's `date_format`
-   `formatTime($time, ?string $locale = null): string` - Format times using locale's `time_format`
-   `formatDateTime($datetime, ?string $locale = null): string` - Format datetimes using locale's `datetime_format`

**Currency Helper** (`app/helpers/currency.php`):

-   `formatCurrency($amount, ?string $locale = null, ?string $currencyCode = null): string` - Format currency with locale-specific separators and symbol position

All helpers:

-   Accept Carbon instances, DateTime objects, or date strings
-   Use `I18nService` internally (never access `config('i18n.*')` directly)
-   Support locale overrides
-   Handle null/empty values gracefully (return empty string)
-   Use function guards and early returns
-   Do NOT use `function_exists()` checks

**Usage Examples:**

```blade
{{ formatDate(now()) }}              {{-- "12/16/2025" (en_US) or "16/12/2025" (fr_FR) --}}
{{ formatCurrency(100.50) }}         {{-- "$100.50" (en_US) or "100,50 €" (fr_FR) --}}
{{ formatCurrency(1000.50, 'fr_FR') }} {{-- "1 000,50 €" --}}
```

#### I18nService

The `I18nService` (`App\Services\I18nService`) centralizes all locale-related operations:

**Key Methods:**

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

**Rule**: Always use `I18nService` for locale-related code - Do not directly access `config('i18n.*')`.

#### View Composers

The `BladeServiceProvider` uses View Composers to share data with Blade templates. The provider is organized into separate methods for better maintainability:

-   **`initLayoutVariables()`**: Shares theme, locale, and HTML attributes with layout templates
-   **`initPageTitle()`**: Shares page title with header and head partials
-   **`initPageSubtitle()`**: Shares page subtitle with header and head partials

**Shared Variables**:

-   **Layout Templates** (`components.layouts.app`, `components.layouts.auth`, `layouts::app`, `layouts::auth`):
    -   `$currentTheme` - Current theme (light/dark)
    -   `$htmlLangAttribute` - HTML lang attribute value
    -   `$htmlDirAttribute` - HTML dir attribute value (ltr/rtl)
-   **Locale Switcher** (`components.preferences.locale-switcher`):
    -   `$currentLocale` - Current locale
    -   `$supportedLocales` - Array of supported locales
    -   `$localeMetadata` - Metadata for current locale (icon, name, etc.)
-   **Theme Switcher** (`components.preferences.theme-switcher`):
    -   `$currentTheme` - Current theme
-   **Sidebar Components** (`components.layouts.app.*`):
    -   `$sideBarTopMenus` - Top menu groups
    -   `$sideBarBottomMenus` - Bottom menu groups
    -   `$sideBarUserMenus` - User dropdown menu groups
-   **Header & Head** (`components.layouts.app.header`, `partials.head`):
    -   `$pageTitle` - Page title (from BasePageComponent or fallback)
    -   `$pageSubtitle` - Page subtitle (optional, from BasePageComponent)

**Usage in Blade:**

```blade
{{-- Specific values are automatically available in layout templates --}}
<html lang="{{ $htmlLangAttribute }}" dir="{{ $htmlDirAttribute }}" data-theme="{{ $currentTheme }}">

{{-- Menu data is automatically available in sidebar components --}}
<x-layouts.app.sidebar>
    {{-- $sideBarTopMenus, $sideBarBottomMenus, $sideBarUserMenus are available --}}
</x-layouts.app.sidebar>
```

**Rule**: Use View Composers instead of `@inject` for global data shared with templates. The provider shares specific values rather than service objects for better performance and clarity.

#### View Composers and Reactivity

**Critical Rule**: When using View Composers with services that have reactive state (like `FrontendPreferencesService`), **always access the service inside the closure**, not outside.

**❌ Incorrect - Values captured once (not reactive):**

```php
// Service provider boot() runs ONCE per request
$preferences = app(FrontendPreferencesService::class);
$currentTheme = $preferences->getTheme(); // Captured value

View::composer([...], function ($view) use ($currentTheme) {
    // $currentTheme is a STATIC VALUE from when boot() ran
    $view->with('currentTheme', $currentTheme);
});
```

**✅ Correct - Service accessed inside closure (reactive):**

```php
View::composer([...], function ($view) {
    // Service accessed EVERY TIME view is rendered
    $preferences = app(FrontendPreferencesService::class);
    $currentTheme = $preferences->getTheme(); // Fresh value from session/DB
    $view->with('currentTheme', $currentTheme);
});
```

**Why this matters:**

-   Service provider `boot()` runs **once per request** when the application starts
-   Values captured outside closures are **static** - they don't update during the request
-   Services accessed **inside closures** run **every time the view is rendered**, getting fresh values from session/database
-   This ensures preferences are **reactive** and reflect current user state

**Performance Impact:**

-   **Minimal overhead**: ~1-2ms per page (negligible)
-   Service container returns **singleton instances** (no object creation overhead)
-   Services use **session caching** (Redis) - first read ~1ms, subsequent reads ~0.1ms
-   The performance cost is far outweighed by correctness and maintainability benefits

#### RTL Support

The system includes first-class RTL support:

-   Layout components automatically set `dir="rtl"` for RTL locales
-   Use Tailwind's `rtl:` variant for RTL-specific styling
-   Configure `direction` in `config/i18n.php` for each locale

#### Adding a New Locale

1.  Add locale to `config/i18n.php`'s `supported_locales` array
2.  Create `lang/{locale}/` directory
3.  Copy structure from default locale
4.  Run `php artisan lang:sync --write`
5.  Translate keys in `lang/{locale}/ui.php` and `lang/{locale}/messages.php`

**Documentation**: See `docs/internationalization.md` for complete guide, best practices, and troubleshooting.

### DataTable Component

The application uses a Livewire-based DataTable component system. See `docs/components/datatable/index.md` for complete documentation.

**Key Points**:

-   All DataTable components extend `App\Livewire\Datatable`
-   State is managed directly in Livewire component properties (search, sort, filters, pagination)
-   Uses `DataTableQueryBuilder` for query building with automatic relationship joins
-   No service layer needed - all logic is in the component

### Authentication Code Refactoring (2025-01-XX)

Improved code quality, removed duplication, and enhanced separation of concerns:

-   **Created Authentication Helpers**: Added `app/helpers/auth-helpers.php` with centralized authentication helper functions
    -   `getIdentifierFromRequest()` - Centralizes identifier extraction logic (removes duplication)
    -   `setTeamSessionForUser()` - Centralizes team session setting logic (removes duplication)
-   **Created Permission Helpers**: Added `app/helpers/permission-helpers.php` with centralized permission cache clearing
    -   `clearPermissionCache()` - Centralizes Spatie Permission cache clearing logic used across seeders
-   **Seeder Refactoring**: Refactored all seeders to use `clearPermissionCache()` helper instead of inline cache clearing
-   **Removed Duplication**: Eliminated duplicate identifier-to-email mapping logic from `FortifyServiceProvider` (middleware already handles it)
-   **Code Organization**: Refactored `FortifyServiceProvider` to use helper functions, improving maintainability
-   **Updated LoginRequest**: Now uses centralized `setTeamSessionForUser()` helper instead of inline logic
-   **Composer Autoload**: Added `auth-helpers.php` and `permission-helpers.php` to Composer autoload files

### Super Admin Gate Pattern (2025-01-XX)

Implemented Spatie Permissions recommended Super-Admin pattern using `Gate::before()`:

-   **Implementation**: Added `Gate::before()` in `AppServiceProvider::boot()` to grant all permissions to users with `Roles::SUPER_ADMIN` role
-   **Location**: `app/Providers/AppServiceProvider.php` (in `boot()` method)
-   **Benefits**: Allows using permission-based controls (`@can()`, `$user->can()`) throughout the app without checking for Super Admin status everywhere
-   **Best Practice**: Follows Spatie Permissions best practices - primarily check permissions, not roles
-   **Gate Updates**: Enhanced existing Gate definitions in `TelescopeServiceProvider`, `HorizonServiceProvider`, and `LogViewerServiceProvider` to explicitly check for Super Admin role for clarity
-   **Important Note**: Direct calls to `hasPermissionTo()`, `hasAnyPermission()`, etc. bypass the Gate and won't get Super Admin access - always use `can()` methods instead
-   **Constants**: Uses `Roles::SUPER_ADMIN` constant (no hardcoded strings)
-   **Testing**: Use `Event::fake([Login::class])` to test preferences without triggering login sync

### Frontend Preferences System

The application includes a centralized **Frontend Preferences Service** that manages user preferences for locale, theme, timezone, and other frontend settings. The system uses a session-backed caching strategy for fast reads and persists preferences to the database for authenticated users.

#### Architecture

**Service**: `App\Services\FrontendPreferences\FrontendPreferencesService` (singleton)

**Service Registration**: **REQUIRED** - Must be registered as singleton in `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php
$this->app->singleton(\App\Services\FrontendPreferences\FrontendPreferencesService::class);
```

**Why Singleton Registration is Required:**

-   **State Preservation**: Service maintains `$sessionStore` instance for performance
-   **Performance**: Avoids repeated instantiation and ensures store instance persists across calls
-   **Consistency**: Same instance everywhere ensures consistent behavior

**I18nService Registration**: **RECOMMENDED** - Should be registered as singleton in `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php
$this->app->singleton(\App\Services\I18nService::class);
```

**Why I18nService Singleton is Recommended:**

-   **Stateless Service**: `I18nService` has no internal state (unlike `FrontendPreferencesService`)
-   **Performance**: Avoids creating multiple instances unnecessarily
-   **Consistency**: Same instance everywhere ensures consistent behavior
-   **Best Practice**: Services are typically singletons in Laravel
-   **Usage Pattern**: Accessed via `app()` in multiple places (BladeServiceProvider, helpers)

**Note**: While `I18nService` singleton registration is not strictly required for correctness (since it's stateless), it's recommended for performance and consistency.

**Storage Strategy**:

-   **Session as Single Source of Truth**: Session is always the single source of truth for reads. All preference reads come from session after initial sync.
-   **Guest users**: Preferences stored in session only
-   **Authenticated users**:
    -   Preferences stored in `users.frontend_preferences` JSON column (persistent storage)
    -   On first read: Preferences are loaded from database and synced to session
    -   Subsequent reads: All reads come from session (single source of truth)
    -   On update: Database is updated first, then session is updated
-   **Performance**: Fast reads from session (single source of truth) with automatic DB sync for authenticated users
-   **Update Flow**:
    -   **Authenticated users**: Update database first, then update session
    -   **Guest users**: Update session only

#### Architecture Details

**Session as Single Source of Truth**:

The service uses a **session-first architecture** where session is always the single source of truth for reads:

1. **Loading Flow**:

    - **Authenticated users (on login)**:
        - Preferences are automatically synced from database to session via `Login` event listener
        - This ensures preferences are immediately available in session after login
    - **Authenticated users (first read, if not synced on login)**:
        - Check if session is empty
        - If empty, load preferences from database
        - Sync database preferences to session
        - Return from session
    - **Authenticated users (subsequent reads)**:
        - Read directly from session (single source of truth)
    - **Guest users**:
        - Read directly from session
        - If empty and request provided, detect browser preferences and save to session

2. **Update Flow**:

    - **Authenticated users**:
        - Update database first (persistent storage)
        - Then update session (single source of truth for reads)
    - **Guest users**:
        - Update session only

3. **Benefits**:
    - **Single source of truth**: All reads come from session, simplifying logic
    - **Performance**: Fast reads from session (no database queries on every read)
    - **Persistence**: Authenticated user preferences persist in database
    - **Consistency**: Database and session stay in sync for authenticated users

**Stores** (SOLID design):

-   `App\Services\FrontendPreferences\Contracts\PreferencesStore` - Interface
-   `App\Services\FrontendPreferences\Stores\SessionPreferencesStore` - Session-based storage
-   `App\Services\FrontendPreferences\Stores\UserJsonPreferencesStore` - Database JSON storage

**Constants**: `App\Constants\Preferences\FrontendPreferences` - Session keys, preference keys, defaults, validation

#### Available Preferences

-   **`locale`**: User's preferred language (validated via `I18nService`)
-   **`theme`**: UI theme preference (`light` or `dark` - validated)
    -   Default is `"light"` for first-time visitors
    -   The `data-theme` attribute is always set on the `<html>` element with the user's preference
-   **`timezone`**: User's timezone for display purposes only (validated PHP timezone identifier)
    -   **Important**: Timezone preference is for display only. All dates/times are stored in the database using the application timezone from `config/app.php`
    -   Date/time formatting helpers (`formatDate()`, `formatTime()`, `formatDateTime()`) automatically use the user's timezone preference when displaying dates/times

#### Auto-Detection on First Visit

The system automatically detects browser preferences on a user's first visit (when no preferences are set) using **server-side request headers only**:

**Language Detection** (Server-side):

-   Automatically detects browser language from `Accept-Language` header
-   Uses `$request->header('Accept-Language')` to read the header
-   Parses and matches against supported locales in `config/i18n.php`
-   Supports quality values (e.g., `fr-FR,fr;q=0.9,en;q=0.8`)
-   Falls back to default locale if browser language is not supported
-   Only detects on first visit (when no locale preference exists)

**Theme Preference**:

-   **No automatic theme detection** - Theme preference is not detected on first visit
-   Default theme preference is `"light"` for first-time visitors
-   Users can manually set theme preference via theme switcher (light or dark)
-   Theme preference is stored in session (guests) or database (authenticated users)
-   The `data-theme` attribute is always set on the `<html>` element with the user's preference

**Detection Behavior**:

-   Language detection only occurs when no preferences are set (first visit)
-   All detection is done server-side using request headers
-   Once preferences are set (manually or via detection), they are persisted
-   **For guests**: Detected preferences are saved to session
-   **For authenticated users**: Detected preferences are saved to both database and session
-   Subsequent visits use saved preferences from session (single source of truth) instead of detecting again

**Implementation Details**:

-   **No JavaScript required** - All detection is server-side
-   **No cookies used** - All preferences stored in session (guests) or database (authenticated users)
-   Language detection uses `Accept-Language` header (standard HTTP header)
-   Theme uses `"system"` by default - DaisyUI handles OS preference detection via CSS
-   System theme is defined in `resources/css/theme.css` with CSS media query support for `prefers-color-scheme`

#### Usage

**In PHP Code:**

```php
use App\Services\FrontendPreferences\FrontendPreferencesService;

$preferences = app(FrontendPreferencesService::class);

// Get preferences
$locale = $preferences->getLocale();
$theme = $preferences->getTheme();
$timezone = $preferences->getTimezone();

// Set preferences
$preferences->setLocale('fr_FR');
$preferences->setTheme('dark');
$preferences->setTimezone('America/New_York');

// Generic get/set
$value = $preferences->get('custom_key', 'default');
$preferences->set('custom_key', 'value');

// Refresh from database to session (for authenticated users)
// This reloads user preferences from DB and syncs to session
$preferences->refresh();
```

**In Blade Templates:**

The `FrontendPreferencesService` is automatically shared with layout templates via View Composers:

```blade
{{-- $preferences is automatically available in layout templates --}}
<html data-theme="{{ $preferences->getTheme() }}">
```

#### Login Event Listener

**`App\Listeners\Preferences\SyncUserPreferencesOnLogin`** listens to the `Illuminate\Auth\Events\Login` event:

-   Automatically syncs user preferences from database to session immediately after login
-   Ensures preferences are available in session right away, without waiting for first read
-   Uses `FrontendPreferencesService::syncUserPreferencesToSession()` method
-   Registered in `AppServiceProvider::boot()` method
-   Uses dependency injection to receive `FrontendPreferencesService` instance

#### Middleware

**`App\Http\Middleware\Preferences\ApplyFrontendPreferences`** automatically applies preferences on each request:

-   Sets application locale: `app()->setLocale($preferences->getLocale())`
-   **Timezone**: Timezone preference is NOT applied globally. It is used only by date/time formatting helpers (`formatDate()`, `formatTime()`, `formatDateTime()`) for display purposes. Database storage always uses the application timezone from `config/app.php`.

Registered in `bootstrap/app.php` web middleware pipeline (after session middleware).

#### UI Components

**Separate Blade Components** (POST form-based, not Livewire):

-   **Theme Switcher**: `resources/views/components/preferences/theme-switcher.blade.php`
    -   Toggle between light/dark themes via POST form
    -   Gets current theme from view composer (`$currentTheme`)
    -   Updates theme via `PreferencesController@updateTheme`
-   **Locale Switcher**: `resources/views/components/preferences/locale-switcher.blade.php`
    -   Dropdown with all supported locales from `I18nService`
    -   Gets current locale and supported locales from view composer (`$currentLocale`, `$supportedLocales`)
    -   Updates locale via `PreferencesController@updateLocale`

**View Composers** (in `BladeServiceProvider`):

-   Shares `$currentTheme`, `$currentLocale`, `$supportedLocales`, and `$i18n` with layout templates
-   Values are automatically available in components included within layouts

**Usage:**

```blade
{{-- Include separately in layouts - no props needed, values come from view composers --}}
<x-preferences.theme-switcher />
<x-preferences.locale-switcher />
```

**Controller**: `App\Http\Controllers\Preferences\PreferencesController`

-   `updateTheme()` - Handles theme preference updates via POST
-   `updateLocale()` - Handles locale preference updates via POST
-   Both methods validate input and redirect back with success/error messages

**Routes**:

-   `POST /preferences/theme` → `preferences.theme`
-   `POST /preferences/locale` → `preferences.locale`

#### Database Schema

**Migration**: Adds `frontend_preferences` JSON column to `users` table

**Model Cast**: `User` model casts `frontend_preferences` as `array`

#### Testing

All functionality is covered by comprehensive Pest tests:

-   **Service Tests**: Guest/authenticated behavior, caching, validation, refresh
-   **Middleware Tests**: Locale/timezone application, defaults
-   **Controller Tests**: Theme/locale update handling, validation, persistence for guests and authenticated users

**Rule**: Always test preference changes to ensure they persist correctly for both guest and authenticated users.

### Creating Release Tags

Use the `release:tag` command to automatically create and push release tags:

```bash
# Auto-increment minor version (default behavior)
php artisan release:tag

# Auto-increment and push to remote
php artisan release:tag --push

# Specify a custom version
php artisan release:tag --tag-version=2.0.0

# Custom version with custom message
php artisan release:tag --tag-version=2.0.0 --message="Major release"

# Dry run to see what would be done
php artisan release:tag --dry-run

# Skip uncommitted changes check (useful for CI/CD)
php artisan release:tag --push --force
```

**Behavior:**

-   If no version is provided, automatically increments minor version (e.g., `v1.0.0` → `v1.1.0`)
-   If no tags exist, starts with `v1.0.0`
-   Validates semantic versioning format
-   Checks for uncommitted changes (warns but allows override, or use `--force` to skip)
-   Optionally pushes to remote with `--push` flag
