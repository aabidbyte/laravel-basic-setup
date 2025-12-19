# Agent Documentation

> **Important**: This file must be updated during development to reflect changes, new patterns, conventions, and project context.

## Project Overview

This is a Laravel 12 SaaS application built with Livewire 4 (single-file components). The application follows modern Laravel conventions with a focus on stable configurations, comprehensive testing, and maintainable code.

## Technology Stack

### Core Framework & Language

-   **PHP**: 8.4.8
-   **Laravel Framework**: v12.0
-   **Laravel Structure**: Streamlined Laravel 12 structure (no `app/Http/Middleware/`, uses `bootstrap/app.php`)

### Authentication & Security

-   **Laravel Fortify**: v1.30 (headless authentication)
-   **Laravel Sanctum**: v4.0 (API authentication)
-   **Spatie Permission**: v6.23 (role and permission management)
-   **Features Enabled**:
    -   User registration
    -   Password reset
    -   Email verification
    -   Two-factor authentication (with password confirmation)
    -   Role and permission management (via Spatie Permission)

### Frontend Stack

-   **Livewire**: v4.0 (beta) (server-side interactivity with built-in single-file components)
-   **Tailwind CSS**: v4.0.7 (utility-first CSS)
-   **Vite**: v7.0.4 (asset bundling)
-   **Alpine.js**: Included with Livewire (no manual inclusion needed)
-   **Documentation**: See `docs/livewire-4.md` for complete Livewire 4 documentation, upgrade guide, and best practices

### Development Tools

-   **Laravel Boost**: v1.8 (MCP server for development)
-   **Laravel Pint**: v1.26 (code formatter)
-   **Pest**: v4.1 (testing framework)
-   **PHPUnit**: v12 (underlying test framework)
-   **Laravel Sail**: v1.41 (Docker development environment)
-   **Laravel Pail**: v1.2.2 (log viewer)

### Monitoring & Queue Management

-   **Laravel Telescope**: v5.16 (debugging and monitoring)
    -   Path: `admin/system/debug/monitoring`
    -   Storage: Database
    -   All watchers enabled by default
-   **Laravel Horizon**: v5.40 (queue monitoring)
    -   Path: `admin/system/queue-monitor`
    -   Redis-based queue management
-   **Log Viewer**: v3.21.1 (log file viewer)
    -   Path: `admin/system/log-viewer`
    -   View Laravel logs and other log types
    -   Secure access gate for production environments

### Real-time & Broadcasting

-   **Laravel Reverb**: v1.0 (WebSocket server)

### Data Storage

-   **Redis**:
    -   Development: Predis (pure PHP, no extension)
    -   Production/Staging: PhpRedis (better performance)
    -   Automatic client selection based on environment
-   **Database**: MySQL (default), SQLite (testing)

## Project Structure

### Key Directories

```
app/
├── Actions/
│   └── Fortify/          # Fortify authentication actions
├── Http/
│   └── Controllers/      # Traditional controllers (minimal usage)
├── Livewire/
│   └── Actions/          # Livewire actions
├── Models/
│   ├── Base/             # Base model classes (BaseModel, BaseUserModel)
│   ├── Concerns/         # Model traits (HasUuid, etc.)
│   └── *.php             # Eloquent models
└── Providers/            # Service providers

resources/
├── views/
│   ├── components/       # Blade components and nested/reusable Livewire components
│   │   └── layouts/     # Blade layout component wrappers
│   ├── layouts/         # Livewire 4 page layouts (with @livewireStyles/@livewireScripts)
│   ├── pages/           # Full-page Livewire components (use pages:: namespace)
│   └── partials/         # Reusable partials

routes/
├── web.php              # Web routes (uses Route::livewire() for pages)
├── api.php              # API routes
└── channels.php         # Broadcasting channels

tests/
├── Feature/             # Feature tests (Pest)
└── Unit/                # Unit tests (Pest)
```

## Configuration Philosophy

### Stable Configurations

The project uses **stable, environment-aware configurations** that minimize `.env` dependencies:

1. **Redis Client Selection**:

    - Automatically uses `predis` in `local`/`development` environments
    - Automatically uses `phpredis` in `production`/`staging` environments
    - Configured in `config/database.php`

2. **Session Configuration**:

    - Driver: `redis` (stable)
    - Encryption: `true` (stable)
    - Secure cookies: Uses `isProduction()` helper function
    - Cookie name: Uses `config('app.name')` for prefix

3. **Cache Configuration**:

    - Default: `redis`
    - Prefix: Uses `config('app.name')` for prefix

4. **Queue Configuration**:

    - Default: `redis`
    - Batching and failed jobs: Redis-based

5. **Telescope & Horizon**:

    - Secure default paths (not obvious)
    - Stable configurations with minimal env dependencies

6. **Logging Configuration**:
    - Daily log rotation enabled for all level-specific channels using Monolog's RotatingFileHandler
    - Logs are separated by level into individual folders: `storage/logs/{level}/laravel-{date}.log`
    - Each log level (emergency, alert, critical, error, warning, notice, info, debug) has its own channel
    - **Exact level filtering**: Each log file contains ONLY messages of its exact level (using Monolog's FilterHandler)
    - Deprecated logs are stored in `storage/logs/deprecations/laravel-{date}.log` with daily rotation
    - Default stack channel routes to all level-specific channels
    - Retention: Configurable via `LOG_DAILY_DAYS` environment variable (default: 14 days)
    - Custom factory: `App\Logging\LevelSpecificLogChannelFactory` handles exact level filtering with daily rotation

### Configuration Best Practices

-   Use `config('app.name')` instead of `env('APP_NAME')`
-   Use helper functions for environment checks: `isProduction()`, `isDevelopment()`, `isStaging()`, `isLocal()`, `isTesting()`
-   Use `appEnv()` to get the current environment (respects config caching)
-   Only use `env()` for credentials and connection details
-   Prefer stable defaults over environment variables for non-sensitive settings

### Environment Helper Functions

The project includes helper functions in `app/helpers/app-helpers.php`:

-   `appEnv(): string` - Get current environment (uses `config('app.env')` to respect config caching)
-   `isProduction(): bool` - Check if running in production/prod
-   `isDevelopment(): bool` - Check if running in local/development/dev
-   `isStaging(): bool` - Check if running in staging/stage
-   `isLocal(): bool` - Check if running in local environment
-   `isTesting(): bool` - Check if running in testing environment
-   `inEnvironment(string ...$environments): bool` - Check if environment matches any of the given environments

These helpers are automatically loaded via Composer autoload and should be used instead of direct `config('app.env')` checks.

## Development Conventions

### Code Style

-   **Formatter**: Laravel Pint (run `vendor/bin/pint` before committing)
-   **PHP Standards**:
    -   Always use curly braces for control structures
    -   Use PHP 8 constructor property promotion
    -   Always use explicit return type declarations
    -   Use appropriate type hints for method parameters
    -   **Always use function guards and early returns** - Check for invalid conditions first and return early to reduce nesting and improve readability
-   **PHPDoc**: **Always add comprehensive PHPDoc comments to all methods and functions when possible** - This enables better IDE autocomplete, type checking, and code documentation. Include:
    -   `@param` annotations with types and descriptions for all parameters
    -   `@return` annotations with return types
    -   `@throws` annotations for exceptions that may be thrown
    -   Detailed descriptions explaining what the method does
    -   Prefer PHPDoc blocks over inline comments
-   **Auth**: **Never use the `auth()` helper**. Always use the `Illuminate\Support\Facades\Auth` facade (e.g. `Auth::check()`, `Auth::user()`, `Auth::id()`, `Auth::guard(...)`).
-   **Helper Functions**: **Do NOT use `function_exists()` checks in helper files** - Helper files are autoloaded via Composer and will only be loaded once, so function existence checks are unnecessary
-   **I18nService**: **Always use `I18nService` for locale-related code** - Do not directly access `config('i18n.*')` in helper functions or other code. Use `I18nService` methods to centralize all locale-related logic (`getSupportedLocales()`, `getDefaultLocale()`, `getValidLocale()`, `getLocaleMetadata()`, etc.)
-   **View Composers**: **Use View Composers instead of `@inject` for global data** - Register View Composers in service providers to share data globally with all views. This is more efficient and cleaner than using `@inject` directives in every template.

### Constants and Code Reusability

⚠️ **CRITICAL RULE**: **Always use constants instead of hardcoded strings when possible, and always avoid duplication for easy maintenance.**

#### Constants Usage

-   **Always define constants** for frequently used string values (log levels, channel names, permission names, role names, etc.)
-   **Constants classes** should be in `app/Constants/` directory
-   **NO HARDCODED STRINGS** are allowed for values that should be constants
-   Examples of constants classes:
    -   `App\Constants\LogLevels` - Log level constants (emergency, alert, critical, error, warning, notice, info, debug)
    -   `App\Constants\LogChannels` - Log channel constants (stack, single, daily, emergency, alert, etc.)
    -   `App\Constants\Permissions` - Permission name constants
    -   `App\Constants\Roles` - Role name constants

#### Avoiding Duplication

-   **Extract repeated patterns** into helper functions, closures, or methods
-   **Use configuration arrays** and loops when configuring multiple similar items
-   **Create factory functions** for generating similar configurations
-   **DRY Principle**: Don't Repeat Yourself - if you find yourself writing the same code pattern multiple times, extract it

#### Examples

**❌ Incorrect - Hardcoded strings and duplication:**

```php
'emergency' => [
    'driver' => 'daily',
    'path' => storage_path('logs/emergency/laravel.log'),
    'level' => 'emergency',
    'days' => env('LOG_DAILY_DAYS', 14),
    'replace_placeholders' => true,
],
'alert' => [
    'driver' => 'daily',
    'path' => storage_path('logs/alert/laravel.log'),
    'level' => 'alert',
    'days' => env('LOG_DAILY_DAYS', 14),
    'replace_placeholders' => true,
],
```

**✅ Correct - Using constants and helper function:**

```php
$createDailyChannel = function (string $channel, string $level): array {
    return [
        'driver' => 'daily',
        'path' => storage_path("logs/{$channel}/laravel.log"),
        'level' => $level,
        'days' => env('LOG_DAILY_DAYS', 14),
        'replace_placeholders' => true,
    ];
};

LogChannels::EMERGENCY => $createDailyChannel(LogChannels::EMERGENCY, LogLevels::EMERGENCY),
LogChannels::ALERT => $createDailyChannel(LogChannels::ALERT, LogLevels::ALERT),
```

### PSR-4 Autoloading Standards

**All classes MUST comply with PSR-4 autoloading standards.** This ensures proper class loading and prevents autoloader warnings.

#### Autoload Mappings

The project uses the following PSR-4 autoload mappings (defined in `composer.json`):

-   **Application Classes**: `App\` → `app/`
-   **Database Factories**: `Database\Factories\` → `database/factories/`
-   **Database Seeders**: `Database\Seeders\` → `database/seeders/`
-   **Test Classes**: `Tests\` → `tests/` (dev only)

#### Rules for Class Organization

1.  **Application Classes**:

    -   All classes in `app/` must use the `App\` namespace
    -   Directory structure must match namespace structure
    -   Example: `app/Models/User.php` → `namespace App\Models;`

2.  **Test Support Classes**:

    -   **Test models, helpers, and support classes MUST be in `tests/Support/`**
    -   Use namespace `Tests\Support\{Category}` matching directory structure
    -   Example: `tests/Support/Models/TestModel.php` → `namespace Tests\Support\Models;`
    -   **Never define classes directly in test files** - always create separate files in `tests/Support/`
    -   Test support classes are automatically autoloaded via the `Tests\` → `tests/` mapping

3.  **Test Files**:
    -   Test files themselves should be in `tests/Feature/` or `tests/Unit/`
    -   Test files don't need namespaces (Pest handles this)
    -   Import test support classes using their full namespace: `use Tests\Support\Models\TestModel;`

#### Common Patterns

**❌ Incorrect - Class defined in test file:**

```php
// tests/Feature/Models/HasUuidTraitTest.php
class TestModel extends BaseModel { } // ❌ Violates PSR-4
```

**✅ Correct - Class in support directory:**

```php
// tests/Support/Models/TestModel.php
<?php
namespace Tests\Support\Models;

use App\Models\Base\BaseModel;

class TestModel extends BaseModel { }
```

```php
// tests/Feature/Models/HasUuidTraitTest.php
<?php
use Tests\Support\Models\TestModel; // ✅ Proper import

it('tests something', function () {
    $model = TestModel::create([...]);
});
```

#### Verification

-   After creating new classes, run `composer dump-autoload` to regenerate autoloader
-   Check for PSR-4 warnings: Classes should autoload without warnings
-   Verify with: `php -r "require 'vendor/autoload.php'; var_dump(class_exists('Your\\Namespace\\Class'));"`

### Component Development

-   **Primary Pattern**: Livewire 4 single-file components (built-in, no Volt needed)
-   **UI Library**: Standard HTML/Tailwind CSS components
-   **Component Reusability**: **ALWAYS use existing components when possible for consistency** - Before creating a new component, check if an existing component can be used or extended. This ensures consistency across the application and reduces code duplication.
-   **Component Documentation**: **ALWAYS update `docs/components.md` when adding new UI components** - This ensures all components are documented with props, usage examples, and implementation details
-   **Component Locations**:
    -   **Full-page components**: `resources/views/pages/` (use `pages::` namespace in routes)
    -   **Nested/reusable Livewire components**: `resources/views/components/` (use component name directly, e.g., `livewire:settings.delete-user-form`)
    -   **Blade components**: `resources/views/components/` (regular Blade components)
-   **File Extensions**: Single-file components must use `.blade.php` extension (not `.php`)
-   **Component Namespaces**: Configured in `config/livewire.php`:
    -   `pages` namespace → `resources/views/pages/`
    -   `layouts` namespace → `resources/views/layouts/`
-   **BasePageComponent**: **ALL full-page Livewire components MUST extend `App\Livewire\BasePageComponent`**
    -   Provides automatic page title and subtitle management via `$pageTitle` and `$pageSubtitle` properties
    -   Automatically shares title and subtitle with layout views via `View::share()` in `boot()` method (runs automatically)
    -   **Required**: Every component MUST define `public ?string $pageTitle = 'ui.pages.example';` property
    -   **Optional**: Components can define `public string $pageSubtitle = 'ui.pages.example.description';` property for subtitle text
    -   **Translations**: Use translation keys (e.g., `'ui.pages.dashboard'`) - keys containing dots are automatically translated via `__()`
    -   **Plain Strings**: Can also use plain strings (e.g., `'Dashboard'`) if translation is not needed
    -   **Seamless**: No need to call `parent::mount()` - title and subtitle sharing happens automatically via `boot()` lifecycle hook
    -   Example: `new class extends BasePageComponent { public ?string $pageTitle = 'ui.pages.dashboard'; public string $pageSubtitle = 'ui.pages.dashboard.description'; }`
    -   **Rule**: Never extend `Livewire\Component` directly for full-page components - always use `BasePageComponent`
-   **Naming**: Use descriptive names (e.g., `isRegisteredForDiscounts`, not `discount()`)
-   **Documentation**: See `docs/livewire-4.md` for complete Livewire 4 documentation

### Routing

-   **Web Routes**: Use `Route::livewire()` for interactive pages (preferred method in Livewire 4)
-   **Static Views**: Use `Route::view()` for simple pages
-   **Named Routes**: Always use named routes with `route()` helper
-   **Full-Page Components**: Use `pages::` namespace for components in `resources/views/pages/`
-   **Examples**:

    ```php
    // Full-page component (in pages/ directory)
    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');

    // Nested component (in components/ directory)
    <livewire:settings.delete-user-form />
    ```

### Database & Models

-   **ORM**: Eloquent (prefer over raw queries)
-   **Relationships**: Always use proper Eloquent relationships with return type hints
-   **N+1 Prevention**: Use eager loading (`with()`, `load()`)
-   **Query Builder**: Use `Model::query()` instead of `DB::`
-   **Casts**: Use `casts()` method on models (Laravel 12 convention)
-   **Base Model Classes**: **ALL new models MUST extend base model classes**
    -   **Regular models**: Extend `App\Models\Base\BaseModel` (includes HasUuid trait)
    -   **Authenticatable models**: Extend `App\Models\Base\BaseUserModel` (includes HasUuid, HasFactory, Notifiable)
    -   Never extend `Illuminate\Database\Eloquent\Model` or `Illuminate\Foundation\Auth\User` directly
    -   Base models automatically include UUID generation and other common functionality
    -   Base models are located in `app/Models/Base/` directory
-   **UUID Requirement**: **ALL tables MUST have a `uuid` column**
    -   Add `$table->uuid('uuid')->unique()->index();` to every table creation
    -   Place the UUID column after the primary key (or after the first column for tables with string primary keys)
    -   UUID columns must be unique and indexed
    -   This applies to all new migrations and any existing tables that don't have UUIDs
-   **Automatic UUID Generation**: **ALL models automatically generate UUIDs via base classes**
    -   `BaseModel` and `BaseUserModel` include the `HasUuid` trait automatically
    -   UUIDs are generated using `Str::uuid()` and checked for uniqueness
    -   If a UUID is manually provided, it will not be overwritten
    -   Models using base classes will use UUID as the route key name
    -   Add `uuid` to `$fillable` array if you need to manually set UUIDs (optional)
-   **Soft Delete Requirement**: **ALL models MUST have soft deletes enabled by default**
    -   **Default behavior**: `BaseModel` and `BaseUserModel` include the `SoftDeletes` trait automatically
    -   **Migration requirement**: All tables MUST include `$table->softDeletes();` in their migration
    -   **Exceptions**: The following tables/models should NOT have soft deletes:
        -   `password_reset_tokens` - Temporary tokens that should be hard deleted when expired
        -   `personal_access_tokens` - Access tokens (Sanctum) that should be hard deleted
        -   Pivot tables - `team_user`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`, `telescope_entries_tags`
        -   Telescope tables - `telescope_entries`, `telescope_entries_tags`, `telescope_monitoring` (monitoring/debugging tables)
        -   Any other temporary or system tables that don't need soft deletion
    -   **Exception handling**: For exceptions, models should extend `Illuminate\Database\Eloquent\Model` directly (not `BaseModel`) and include only necessary traits (e.g., `HasUuid`) manually
    -   **Example exception**: `PasswordResetToken` extends `Model` directly and includes `HasUuid` manually, avoiding the `SoftDeletes` trait from `BaseModel`
    -   **Documentation**: All exceptions must include PHPDoc comments explaining why soft deletes are not used

### Authentication

-   **Backend**: Laravel Fortify (headless)
-   **Actions**: Customize in `app/Actions/Fortify/`
-   **Views**: Customize in `FortifyServiceProvider`
-   **Features**: Configure in `config/fortify.php`

### Authorization & Permissions

-   **Package**: Spatie Permission (v6.23)
-   **User Model**: `App\Models\User` includes `HasRoles` trait
-   **UUID Support**: Configured to use `model_uuid` instead of `model_id` for UUID-based User models
-   **Teams Permissions**: Enabled by default (`'teams' => true` in config)
-   **Configuration**: `config/permission.php`
-   **Migration**: Modified to support UUIDs in pivot tables (`model_has_permissions`, `model_has_roles`)
-   **Middleware**: `App\Http\Middleware\TeamsPermission` - Sets team ID from session
-   **Middleware Priority**: Registered in `AppServiceProvider` to run before `SubstituteBindings`
-   **Documentation**: See `docs/spatie-permission.md` for complete rules, best practices, and guidelines
-   **Constants**: Always use `App\Constants\Permissions` and `App\Constants\Roles` - **NO HARDCODED STRINGS ALLOWED**
-   **Best Practice**: Always check for **permissions** (not roles) using `can()` and `@can` directives
-   **Team ID**: Set via `session(['team_id' => $team->id])` on login, accessed via `setPermissionsTeamId()`
-   **Important**: User model must NOT have `role`, `roles`, `permission`, or `permissions` properties/methods/relations
-   **Switching Teams**: Always call `$user->unsetRelation('roles')->unsetRelation('permissions')` before querying after switching teams

### Testing

-   **Framework**: Pest v4
-   **Test Types**:
    -   Feature tests (most common)
    -   Unit tests (for isolated logic)
    -   Browser tests (for complex interactions)
-   **Test Location**: `tests/Feature/` and `tests/Unit/`
-   **Test Command**: `php artisan test --filter=testName`
-   **Coverage**: Every change must be tested
-   **Factories**: Use model factories in tests

### Frontend Development

-   **Build Tool**: Vite
-   **Development**: `npm run dev` or `composer run dev`
-   **Production Build**: `npm run build`
-   **Styling**: Tailwind CSS v4 (use `@import "tailwindcss"` not `@tailwind` directives)
-   **Dark Mode**: Support dark mode using `dark:` classes when applicable
-   **Spacing**: Use `gap` utilities instead of margins for flex/grid layouts
-   **Alpine.js Preference**: **Always prefer Alpine.js over plain JavaScript when possible**
    -   Alpine.js is included with Livewire (no manual inclusion needed)
    -   Use Alpine.js directives (`x-data`, `x-init`, `x-show`, `x-on:click`, `@click`, etc.) instead of `onclick`, `addEventListener`, `querySelector`, etc.
    -   Use `$el` to reference the current element in Alpine.js expressions
    -   Use `$nextTick()` for DOM updates that need to wait for the next render cycle
    -   Use `$refs` for referencing child elements when possible (e.g., `x-ref="modal"` then `$refs.modal`)
    -   **When plain JavaScript is acceptable:**
        -   Complex third-party library integrations that require direct DOM manipulation
        -   Web APIs that don't work well with Alpine.js (e.g., some browser APIs)
        -   Debug instrumentation code (temporary logging/debugging)
        -   When referencing elements by ID that aren't the current element (though `$refs` is preferred)
    -   **Examples:**
        -   ✅ `@click="$el.closest('dialog').close()"` instead of `onclick="document.getElementById('id').close()"`
        -   ✅ `x-init="$nextTick(() => $el.showModal())"` instead of `x-init="$nextTick(() => { const modal = document.getElementById('id'); if (modal) modal.showModal(); })"`
        -   ✅ `x-data="{ open: false }" x-show="open"` instead of manually toggling classes with JavaScript
        -   ✅ `x-ref="modal"` then `$refs.modal.showModal()` instead of `document.getElementById('modal').showModal()`

### Asset Management (CSS/JS Structure)

The application uses a modular CSS/JS structure to avoid duplication and optimize bundle sizes. Assets are organized using CSS imports (supported by Tailwind CSS v4) rather than separate Vite entry points.

#### CSS File Structure

**Base CSS** (`resources/css/base.css`):

-   Contains all Tailwind CSS and DaisyUI configuration
-   Includes theme setup, custom variants, and font configuration
-   Shared foundation for all layouts

**App CSS** (`resources/css/app.css`):

-   Imports `base.css` + `sidebar.css`
-   Used in authenticated app layout (with sidebar)
-   Contains base styles + sidebar-specific styles

**Auth CSS** (`resources/css/auth.css`):

-   Imports only `base.css`
-   Used in authentication layout (no sidebar)
-   Contains only base styles (smaller bundle)

**Sidebar CSS** (`resources/css/sidebar.css`):

-   Contains only sidebar component styles
-   No Tailwind imports (imported via `app.css`)
-   Uses `@layer components` for component-specific styles

#### JavaScript File Structure

**App JS** (`resources/js/app.js`):

-   Main application JavaScript
-   Loaded in both app and auth layouts

**Notification Center JS** (`resources/js/notification-center.js`):

-   Real-time notification handling
-   Alpine.js store and helpers for notifications
-   Loaded only in app layout (not needed for auth pages)

#### Asset Loading by Layout

**App Layout** (`resources/views/partials/head.blade.php`):

```blade
@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/notification-center.js'])
```

-   Loads: `app.css` (base + sidebar), `app.js`, `notification-center.js`

**Auth Layout** (`resources/views/partials/auth/head.blade.php`):

```blade
@vite(['resources/css/auth.css', 'resources/js/app.js'])
```

-   Loads: `auth.css` (base only), `app.js`

#### Vite Configuration

Entry points in `vite.config.js`:

-   `resources/css/app.css` - App layout styles
-   `resources/css/auth.css` - Auth layout styles
-   `resources/js/app.js` - Main JavaScript
-   `resources/js/notification-center.js` - Notification handling

#### Benefits

-   **No CSS Duplication**: Base styles are shared via CSS imports, not duplicated
-   **Smaller Bundle Sizes**: Auth pages don't load sidebar styles or notification JS
-   **Maintainable**: Single source of truth for base styles in `base.css`
-   **Optimized**: Tailwind CSS v4 automatically bundles `@import` statements

#### Adding New CSS/JS Files

**To add CSS that should load in both layouts:**

1. Add styles to `base.css` or create a new file
2. Import the new file in both `app.css` and `auth.css`

**To add CSS that should load only in app layout:**

1. Create a new CSS file (e.g., `resources/css/feature.css`)
2. Import it in `app.css`: `@import "./feature.css";`
3. No need to add it as a Vite entry point (imported via `app.css`)

**To add CSS that should load only in auth layout:**

1. Create a new CSS file (e.g., `resources/css/auth-feature.css`)
2. Import it in `auth.css`: `@import "./auth-feature.css";`
3. No need to add it as a Vite entry point (imported via `auth.css`)

**To add JavaScript that should load in both layouts:**

1. Add to `app.js` or create a new file
2. Add as Vite entry point in `vite.config.js`
3. Include in both head partials: `@vite([..., 'resources/js/new-file.js'])`

**To add JavaScript that should load only in app layout:**

1. Create a new JS file (e.g., `resources/js/feature.js`)
2. Add as Vite entry point in `vite.config.js`
3. Include only in app head partial: `@vite([..., 'resources/js/feature.js'])`

## Key Features

### Current Features

1. **User Authentication**:

    - Registration
    - Login/Logout
    - Password reset
    - Email verification
    - Two-factor authentication

2. **User Settings**:

    - Profile management
    - Password update
    - Appearance settings
    - Two-factor management

3. **Monitoring**:

    - Telescope (debugging)
    - Horizon (queue monitoring)

4. **Logging**:

    - Daily log rotation with level-specific folders
    - Log clearing command (`php artisan logs:clear`)

5. **Notifications**:
    - Toast notifications via Laravel Reverb (always broadcast)
    - Persistent notifications (optional, stored in database)
    - Notification center page for viewing/managing notifications
    - Automatic pruning of read notifications (30 days)
    - Support for user, team, and global channels

### Planned/In Development

-   Check project roadmap or issues for planned features

## Navigation Builder System

The project includes a fluent navigation builder system for dynamically generating menus and sidebars.

### Architecture

```
app/Services/
├── SideBarMenuService.php              # Main menu service with getTopMenus(), getBottomMenus(), getUserMenus()
└── Navigation/
    ├── NavigationBuilder.php           # Fluent builder for menu groups/sections
    └── NavigationItem.php              # Fluent builder for individual menu items
```

### Key Classes

**NavigationItem** (`app/Services/Navigation/NavigationItem.php`):

-   Fluent builder for individual menu items
-   Supports: title, URL/route, icons, badges, nested items, conditional visibility, external links, HTML attributes
-   Methods: `make()`, `title()`, `url()`, `route()`, `icon()`, `show()`, `external()`, `items()`, `badge()`, `active()`, `attributes()`
-   **Note**: Form and button support have been removed. Use static forms in Blade templates for actions like logout.
-   **Attributes**: Returns attributes as an array (not a string) for use with `$attributes->merge()` in Blade components
-   **Icons**: Accepts icon component names (e.g., 'home', 'user', 'settings') which are rendered using the `<x-ui.icon>` Blade component. Icons support multiple icon packs (heroicons, fontawesome, bootstrap, feather) and include security validation.

**NavigationBuilder** (`app/Services/Navigation/NavigationBuilder.php`):

-   Fluent builder for menu groups/sections
-   Contains multiple NavigationItem instances
-   Methods: `make()`, `title()`, `items()`, `icon()`, `show()`

**SideBarMenuService** (`app/Services/SideBarMenuService.php`):

-   Centralized service for defining navigation menus
-   Three methods:
    -   `getTopMenus()`: Returns array of NavigationBuilder for top section
    -   `getBottomMenus()`: Returns array of NavigationBuilder for bottom section
    -   `getUserMenus()`: Returns array of NavigationBuilder for user dropdown
-   **Note**: Logout is handled as a static form in the sidebar components, not through NavigationItem

### Usage Example

```php
// In SideBarMenuService.php
use Illuminate\Support\Facades\Auth;

public function getTopMenus(): array
{
    return [
        NavigationBuilder::make()
            ->title('Platform')
            ->items(
                NavigationItem::make()
                    ->title('Dashboard')
                    ->route('dashboard')
                    ->icon('home')
                    ->show(Auth::user()->hasRole('admin')),

                NavigationItem::make()
                    ->title('Users')
                    ->route('users.index')
                    ->badge(fn() => User::count())
                    ->items(
                        NavigationItem::make()
                            ->title('Active Users')
                            ->route('users.active')
                    )
            ),
    ];
}
```

### In Blade Templates

```php
@inject('menuService', \App\Services\SideBarMenuService::class)

<!-- Render top menus -->
<div class="menu">
    @foreach($menuService->getTopMenus() as $group)
        <x-navigation.group :group="$group" />
    @endforeach
</div>
```

### Sidebar Components

The sidebar uses a unified component structure:

-   **`sidebar.blade.php`** (`<x-layouts.app.sidebar>`): Main wrapper component using DaisyUI drawer with integrated navbar and content area
    -   Location: `resources/views/components/layouts/app/sidebar.blade.php`
    -   Contains: Drawer structure, navbar with mobile toggle, main content area, and includes `<x-layouts.app.sidebar-menus />`
-   **`sidebar-menus.blade.php`** (`<x-layouts.app.sidebar-menus />`): Unified sidebar menu component
    -   Location: `resources/views/components/layouts/app/sidebar-menus.blade.php`
    -   Contains: Sidebar menu with top menus, bottom menus, and logo
    -   Responsive behavior is handled by DaisyUI's drawer component (`lg:drawer-open` class)
-   **`header.blade.php`** (`<x-layouts.app.header />`): Header component displaying page title, subtitle, and user menu
    -   Location: `resources/views/components/layouts/app/header.blade.php`
    -   Contains: Page title, optional subtitle, theme switcher, locale switcher, and user dropdown menu

**Usage**:

```php
<x-layouts.app.sidebar>
 <!-- Main content -->
</x-layouts.app.sidebar>
```

**Component Structure**:

```php
<!-- sidebar.blade.php -->
<div class="drawer lg:drawer-open">
    <div class="drawer-content">
        <div class="navbar">
            <x-layouts.app.header />
        </div>
        <main>{{ $slot }}</main>
    </div>
    <x-layouts.app.sidebar-menus />
</div>
```

**Note**: The sidebar uses View Composers (registered in `BladeServiceProvider`) to automatically inject menu data (`$sideBarTopMenus`, `$sideBarBottomMenus`, `$sideBarUserMenus`) into sidebar components. No manual service injection needed. Navigation items use `<div>` elements instead of `<ul>`/`<li>` for semantic HTML flexibility.

### Features

-   ✅ Fluent, chainable API
-   ✅ Permission-based visibility (`show()` with closures)
-   ✅ **Backend filtering**: Invisible items are filtered server-side for security
-   ✅ Nested menus (unlimited depth via `items()`)
-   ✅ Dynamic badges (closures for real-time counts)
-   ✅ External link handling
-   ✅ Active state detection (automatic route matching)
-   ✅ Icon support (icon component names via `<x-ui.icon>`, supports multiple icon packs with security validation)
-   ✅ HTML attributes support (returns array for `$attributes->merge()`)
-   ✅ Fully testable (24 unit tests)
-   ✅ Reusable across multiple services
-   ✅ **No form/button support**: Use static forms in Blade templates for actions
-   ✅ **Semantic HTML**: Uses `<div>` elements instead of `<ul>`/`<li>` for flexibility

### Testing

-   Unit tests: `tests/Unit/Services/Navigation/`
-   All unit tests pass (24 tests, 52 assertions)
-   Tests cover: fluent API, visibility filters, nested items, badges, active states, attributes
-   **Note**: Form/button tests have been removed as this functionality is no longer supported

### Design Patterns

-   **Builder Pattern**: Fluent interface for constructing navigation
-   **Factory Pattern**: Static `make()` methods for instantiation
-   **Composite Pattern**: Nested items (tree structure)
-   **Service Pattern**: Centralized menu definition
-   **Lazy Evaluation**: Closures for `show()` and `badge()` evaluated at render time

### Security

**Backend Filtering**: All visibility checks are performed server-side. Items with `show(false)` or failed permission checks are filtered out before being sent to the frontend, ensuring:

-   No sensitive menu items are exposed in HTML/JavaScript
-   Better performance (fewer items to render)
-   Security by default (frontend cannot bypass visibility rules)

The filtering happens at three levels:

1. **NavigationItem**: `getItems()` only returns visible nested items
2. **NavigationBuilder**: `getItems()` only returns visible items
3. **SideBarMenuService**: Each method filters out invisible groups/items before returning

### Extension Points

To add new navigation sections:

1. Add a new method to `SideBarMenuService` (e.g., `getAdminMenus()`)
2. Use NavigationBuilder and NavigationItem to define the structure
3. Filter invisible items: `array_filter($items, fn($item) => $item->isVisible())`
4. Render in Blade using `<x-navigation.group>` component

## Notification Builder System

The project includes a comprehensive notification system with a fluent API similar to the Navigation Builder pattern. The system supports both toast notifications (temporary UI messages) and persistent notifications (stored in the database), all broadcast via Laravel Reverb for real-time delivery.

**Documentation**: See `docs/notifications.md` for complete documentation, usage examples, and best practices.

### Architecture

```
app/Services/Notifications/
├── NotificationBuilder.php        # Fluent builder for creating notifications
├── ToastPayload.php                # DTO for toast notification data
└── NotificationContent.php         # Content wrapper (string/html/view)

app/Enums/
├── ToastType.php                   # Toast type enum (success, info, warning, error, classic)
├── ToastPosition.php               # Toast position enum (top-right, top-left, etc.)
└── ToastAnimation.php              # Toast animation enum (slide, etc.)

app/Events/
└── ToastBroadcasted.php            # Broadcast event for toast notifications

resources/views/
├── components/notifications/
│   └── toast-center.blade.php      # Alpine.js toast UI component
└── pages/notifications/
    └── ⚡index.blade.php            # Notification center page (Livewire 4)
```

### Key Classes

**NotificationBuilder** (`app/Services/Notifications/NotificationBuilder.php`):

-   Fluent builder for creating notifications
-   **Default behavior**: Toast-only, success type, current user channel
-   Methods: `make()`, `title()`, `subtitle()`, `content()`, `html()`, `view()`, `success()`, `info()`, `warning()`, `error()`, `classic()`, `position()`, `animation()`, `persist()`, `toUser()`, `toTeam()`, `toUserTeams()`, `global()`, `link()`, `send()`
-   **Title is required**: Must call `title()` before `send()`
-   **Content support**: String, HTML (trusted), or Blade view via `view()`
-   **Persistence**: Call `persist()` to save to database (creates DatabaseNotification records)
-   **Channels**: Defaults to current user, or use `toUser()`, `toTeam()`, `toUserTeams()`, `global()`

**ToastPayload** (`app/Services/Notifications/ToastPayload.php`):

-   DTO for toast notification data
-   Contains: title, subtitle, content, type, position, animation, link
-   Serializes to array for JSON broadcasting

**ToastBroadcasted** (`app/Events/ToastBroadcasted.php`):

-   Implements `ShouldBroadcastNow` for immediate broadcasting
-   Broadcasts to private channels: `private-notifications.user.{uuid}`, `private-notifications.team.{uuid}`, `private-notifications.global`
-   Event name: `toast.received`

### Usage Examples

```php
use App\Services\Notifications\NotificationBuilder;
use App\Enums\ToastType;
use App\Enums\ToastPosition;

// Simple toast notification (toast-only, current user)
NotificationBuilder::make()
    ->title('Task completed')
    ->success()
    ->send();

// Toast with subtitle and content
NotificationBuilder::make()
    ->title('New message')
    ->subtitle('From John Doe')
    ->content('Hello, how are you?')
    ->info()
    ->send();

// Persistent notification with link
NotificationBuilder::make()
    ->title('Payment received')
    ->persist()
    ->link('/payments/123')
    ->toUser($user)
    ->send();

// Team notification
NotificationBuilder::make()
    ->title('Team update')
    ->persist()
    ->toTeam($team)
    ->warning()
    ->send();

// User teams notification (sends to all teams a user belongs to)
NotificationBuilder::make()
    ->title('Update for your teams')
    ->persist()
    ->toUserTeams() // Uses current authenticated user
    ->info()
    ->send();

// User teams notification for specific user
NotificationBuilder::make()
    ->title('Welcome to all your teams')
    ->toUserTeams($user)
    ->success()
    ->send();

// Global notification
NotificationBuilder::make()
    ->title('System maintenance')
    ->persist()
    ->global()
    ->error()
    ->send();

// Using Blade view for content
NotificationBuilder::make()
    ->title('Custom notification')
    ->view('notifications.custom', ['data' => $data])
    ->send();
```

### Toast UI Component

**Toast Center** (`resources/views/components/notifications/toast-center.blade.php`):

-   Alpine.js component that subscribes to Echo private channels
-   Alpine store + helpers live in `resources/js/notification-center.js`
-   Automatically included in authenticated app layout
-   **Idempotent subscriptions**: Uses idempotent subscription logic to prevent duplicate subscriptions when components are re-initialized (e.g., during Livewire navigation)
-   Features:
    -   Subscribes to user, team, and global channels
    -   Renders toasts using DaisyUI alert components
    -   Supports all toast types with appropriate icons
    -   Auto-dismisses after 5 seconds
    -   Supports click-to-navigate via link
    -   Slide animation (enters from right, exits to right)

### Notification Dropdown Component

**Notification Dropdown** (`resources/views/components/notifications/⚡dropdown.blade.php`):

-   Livewire 4 single-file component (lazy-loaded)
-   Component: `<livewire:notifications.dropdown lazy>`
-   Features:
    -   Shows last 5 notifications (sorted by unread first, then by creation date)
    -   Unread count badge (shows count up to 99, displays "99+" if over 99)
    -   Badge calculation via Livewire computed property `getUnreadBadgeProperty()`
    -   Automatically marks visible notifications as read when dropdown closes (only if opened)
    -   Uses Alpine.js reactive state (`isOpen`, `wasOpened`) to manage dropdown state
    -   `dropdown-open` class managed via `x-bind:class` to maintain state during Livewire updates
    -   Refreshes in real-time when notifications change (via Alpine notifications store fan-out)
-   **State Management Pattern**:
    ```blade
    <div x-data="notificationDropdown($wire)" x-init="init()"
        @click.away="
            if (wasOpened) {
                $wire.markVisibleAsRead();
                wasOpened = false;
            }
            isOpen = false;
        ">
        <x-ui.dropdown x-bind:class="{ 'dropdown-open': isOpen }">
            <x-slot:trigger>
                <button @click="
                    isOpen = true;
                    wasOpened = true;
                ">
                    <!-- Badge shows {{ $this->unreadBadge }} -->
                </button>
            </x-slot:trigger>
        </x-ui.dropdown>
    </div>
    ```

### Notification Center Page

**Notification Center** (`resources/views/pages/notifications/⚡index.blade.php`):

-   Livewire 4 single-file component extending `BasePageComponent`
-   Route: `/notifications` (named: `notifications.index`)
-   Features:
    -   Lists all user notifications (shows 10 newest by default, with "Load more" button)
    -   Uses `#[Computed]` attribute for computed properties (`notifications()`, `unreadCount()`, `totalCount()`)
    -   Auto-marks as read on viewport intersection (`x-intersect.once`)
    -   Marks as read on click
    -   "Mark all as read" button
    -   "Clear all" button (shows total count)
    -   Shows unread badge for unread notifications
    -   Displays notification type icons, title, subtitle, content, link, and timestamp
    -   Refreshes in real-time when new notifications are broadcast (via Alpine notifications store fan-out in `resources/js/notification-center.js`)

### Broadcast Channels

Channels are defined in `routes/channels.php`:

-   **User channel** (`private-notifications.user.{userUuid}`): Authorized for matching user UUID
-   **Team channel** (`private-notifications.team.{teamUuid}`): Authorized for team members
-   **User teams channel**: When using `toUserTeams()`, broadcasts to each team channel the user belongs to (or falls back to user channel if user has no teams)
-   **Global channel** (`private-notifications.global`): Authorized for any authenticated user

### Database Notification Refresh

For real-time UI refresh when the `notifications` table changes, the app broadcasts a dedicated event:

-   `App\Events\DatabaseNotificationChanged` (broadcast name: `notification.changed`)
-   Triggered via `App\Observers\DatabaseNotificationObserver` observing `Illuminate\Notifications\DatabaseNotification`

### Persistence Behavior

When `->persist()` is called:

-   **User channel**: Creates single DatabaseNotification for target user
-   **Team channel**: Creates DatabaseNotification for each team member
-   **User teams channel**: Creates DatabaseNotification for each team member in each team the user belongs to (or single notification for user if no teams)
-   **Global channel**: Creates DatabaseNotification for all users (batched inserts)

Notifications are stored in Laravel's standard `notifications` table with:

-   `id`: UUID primary key
-   `type`: Notification class name (string)
-   `notifiable_type` / `notifiable_id`: Polymorphic relationship to User
-   `data`: JSON containing title, subtitle, content, type, link
-   `read_at`: Timestamp when marked as read (null if unread)

### Pruning

-   Command: `php artisan notifications:prune-read` (default: 30 days)
-   Scheduled: Runs daily (configured in `routes/console.php`)
-   Behavior: Deletes read notifications where `read_at < now()->subDays(30)`
-   Unread notifications are never pruned

### Teams Integration

-   All users are automatically assigned to a personal team on registration
-   Team UUID is available for team channel notifications
-   Team ID is stored in session on login (for TeamsPermission middleware compatibility)

### Rules & Best Practices

-   **Always use NotificationBuilder**: Don't manually create DatabaseNotification records
-   **Title required**: Must always call `title()` before `send()`
-   **Toast-first**: All notifications broadcast toasts; persistence is optional
-   **Channel selection**: Default to current user unless you need team/global
-   **Content rendering**: Use `view()` for complex content, `html()` for trusted HTML, `content()` for plain strings
-   **Persistence**: Only use `persist()` when notifications need to be reviewable later
-   **Testing**: Use `Event::fake([ToastBroadcasted::class])` to test notifications without broadcasting

## Important Patterns

### Livewire 4 Single-File Component Pattern

**File Location**: `resources/views/pages/example.blade.php` (must use `.blade.php` extension)

```php
<?php

use App\Livewire\BasePageComponent;

new class extends BasePageComponent {
    public ?string $pageTitle = 'ui.pages.example';

    public string $pageSubtitle = 'ui.pages.example.description'; // Optional

    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
};
?>

<div>
    <h1>Count: {{ $count }}</h1>
    <button wire:click="increment" class="rounded-lg bg-zinc-900 px-4 py-2 text-white">+</button>
</div>
```

**Route Registration**:

```php
Route::livewire('/example', 'pages::example')->name('example');
```

**Important Notes**:

-   **ALL full-page Livewire components MUST extend `App\Livewire\BasePageComponent`** (not `Livewire\Component`)
-   Set `public ?string $pageTitle = 'ui.pages.example';` property for automatic title management (use translation keys)
-   **Optional**: Set `public string $pageSubtitle = 'ui.pages.example.description';` property for subtitle text (displayed below title in header)
-   **Translations**: Translation keys (containing dots) are automatically translated - use `'ui.pages.*'` format
-   **Plain Strings**: Can also use plain strings if translation is not needed
-   **No `parent::mount()` needed** - title and subtitle sharing happens automatically via `boot()` lifecycle hook
-   Single-file components must use `.blade.php` extension (not `.php`)
-   Full-page components go in `resources/views/pages/` and use `pages::` namespace
-   Nested/reusable Livewire components go in `resources/views/components/` and are referenced directly (e.g., `livewire:settings.delete-user-form`)
-   See `docs/livewire-4.md` for complete documentation

### Livewire Best Practices

-   Single root element required
-   Use `wire:loading` for loading states
-   Use `wire:key` in loops
-   Use `wire:model.live` for real-time updates
-   Prefer lifecycle hooks (`mount()`, `updatedFoo()`)
-   Always validate form data in Livewire actions
-   Always run authorization checks in Livewire actions

### UI Components

-   Use standard HTML elements with Tailwind CSS classes
-   Check existing components before creating custom
-   Components are built using Tailwind CSS utility classes

#### Icon Component (`<x-ui.icon>`)

The application includes a dynamic icon component located at `resources/views/components/ui/icon.blade.php` that provides secure, flexible icon rendering using Blade Icons.

**Features:**

-   **Multiple Icon Packs**: Supports heroicons (default), fontawesome, bootstrap, and feather
-   **Security**: Input validation and sanitization for icon names, pack names, and CSS classes
-   **Size Support**: Predefined sizes (xs, sm, md, lg, xl) or custom Tailwind classes
-   **Fallback Handling**: Automatically falls back to a question mark icon if the requested icon doesn't exist
-   **Blade Component**: Uses `@inject` for dependency injection (no Livewire overhead)

**Usage:**

```blade
{{-- Basic usage --}}
<x-ui.icon name="home" />

{{-- With size --}}
<x-ui.icon name="user" size="md" />

{{-- With custom class --}}
<x-ui.icon name="settings" class="h-5 w-5 text-primary" />

{{-- With different icon pack --}}
<x-ui.icon name="star" pack="fontawesome" size="lg" />
```

**Security Measures:**

-   Icon names are sanitized to only allow alphanumeric characters, dashes, and underscores
-   Pack names are validated against supported packs (falls back to 'heroicons' if invalid)
-   CSS class attributes are sanitized to prevent XSS attacks
-   Blade Icons handles SVG content sanitization internally

**Component Location:** `resources/views/components/ui/icon.blade.php`

**Service Dependency:** Uses `App\Services\IconPackMapper` (injected via `@inject` directive)

#### Dropdown Component (`<x-ui.dropdown>`)

The application includes a centralized, flexible dropdown component located at `resources/views/components/ui/dropdown.blade.php` that provides consistent dropdown functionality across the application.

**Features:**

-   **Multiple Placement Options**: Supports all DaisyUI placements (start, center, end, top, bottom, left, right)
-   **CSS Focus Pattern**: Uses CSS focus pattern by default for better accessibility and keyboard navigation
-   **Menu Support**: Optional menu styling with size variants (xs, sm, md, lg, xl)
-   **Hover Support**: Optional hover-to-open behavior
-   **Flexible Content**: Supports both menu items and custom content
-   **Accessibility**: Built-in ARIA attributes and keyboard navigation support

**Props:**

-   `placement` (default: `'end'`): Dropdown placement - `start`, `center`, `end`, `top`, `bottom`, `left`, `right`
-   `hover` (default: `false`): Enable hover to open dropdown
-   `contentClass` (default: `''`): Additional CSS classes for dropdown content
-   `bgClass` (default: `'bg-base-100'`): Background color class for dropdown content (default: bg-base-100)
-   `menu` (default: `false`): Enable menu styling (adds `menu` class)
-   `menuSize` (default: `'md'`): Menu size - `xs`, `sm`, `md`, `lg`, `xl`
-   `id` (default: `null`): Optional ID for accessibility (auto-generated if not provided)

**Usage:**

```blade
{{-- Basic dropdown with custom content --}}
<x-ui.dropdown>
    <x-slot:trigger>
        <button class="btn">Click me</button>
    </x-slot:trigger>

    <div class="p-4">
        Custom content here
    </div>
</x-ui.dropdown>

{{-- Menu dropdown --}}
<x-ui.dropdown placement="end" menu menuSize="sm">
    <x-slot:trigger>
        <div class="btn btn-ghost">Menu</div>
    </x-slot:trigger>

    <li><a>Item 1</a></li>
    <li><a>Item 2</a></li>
</x-ui.dropdown>

{{-- Dropdown with custom styling --}}
<x-ui.dropdown placement="end" menu contentClass="rounded-box z-[1] w-48 p-2 shadow-lg border border-base-300">
    <x-slot:trigger>
        <button class="btn btn-ghost btn-sm">
            <x-ui.icon name="globe-alt" />
        </button>
    </x-slot:trigger>

    <li>
        <form method="POST" action="{{ route('preferences.locale') }}">
            @csrf
            <input type="hidden" name="locale" value="en_US">
            <button type="submit" class="btn btn-ghost btn-sm justify-start w-full">English</button>
        </form>
    </li>
</x-ui.dropdown>

{{-- Hover dropdown --}}
<x-ui.dropdown hover>
    <x-slot:trigger>
        <button class="btn">Hover me</button>
    </x-slot:trigger>

    <div>Content appears on hover</div>
</x-ui.dropdown>
```

**Component Location:** `resources/views/components/ui/dropdown.blade.php`

**Migration Notes:**

-   All existing dropdowns have been migrated to use this component
-   The component uses CSS focus pattern by default (better accessibility than Alpine.js pattern)
-   Previous Alpine.js-based dropdowns (like locale-switcher) have been migrated to CSS focus pattern
-   The component is fully compatible with DaisyUI's dropdown classes and behavior

## Development Workflow

### Setup

```bash
composer install
npm install
php artisan key:generate
php artisan setup:application  # Interactive setup
npm run build
```

### Development

```bash
composer run dev  # Runs server, queue, logs, and vite concurrently
```

### Testing

```bash
php artisan test                    # All tests
php artisan test --filter=testName  # Specific test
```

### Code Formatting

```bash
vendor/bin/pint                    # Format all files
vendor/bin/pint --dirty            # Format only changed files
```

## Environment Configuration

### Required Environment Variables

-   `APP_NAME`: Application name
-   `APP_ENV`: Environment (local, development, staging, production)
-   `APP_KEY`: Encryption key
-   `DB_*`: Database connection details
-   `REDIS_*`: Redis connection details (optional, has defaults)

### Optional Environment Variables

Most configurations have stable defaults. Only override when necessary.

## Security Considerations

1. **Session Security**:

    - Encrypted sessions enabled
    - Secure cookies in production
    - HttpOnly cookies enabled
    - SameSite: lax

2. **Authentication**:

    - Two-factor authentication available
    - Password confirmation for sensitive operations
    - Rate limiting on authentication routes

3. **Monitoring Access**:
    - Telescope: `admin/system/debug/monitoring`
    - Horizon: `admin/system/queue-monitor`
    - Log Viewer: `admin/system/log-viewer`
    - All protected by authorization gates

## Common Tasks

### Creating a New Livewire Component

```bash
# Full-page component (creates in pages/ directory)
php artisan make:livewire pages.settings.profile --test --pest

# Nested/reusable component (creates in components/ directory)
php artisan make:livewire settings.delete-user-form --test --pest

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
-   Nested/reusable Livewire components are created in `resources/views/components/` and are referenced directly (e.g., `livewire:settings.delete-user-form`)
-   All single-file components use `.blade.php` extension
-   See `docs/livewire-4.md` for complete documentation

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

**Constants**: `App\Constants\FrontendPreferences` - Session keys, preference keys, defaults, validation

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

**`App\Listeners\SyncUserPreferencesOnLogin`** listens to the `Illuminate\Auth\Events\Login` event:

-   Automatically syncs user preferences from database to session immediately after login
-   Ensures preferences are available in session right away, without waiting for first read
-   Uses `FrontendPreferencesService::syncUserPreferencesToSession()` method
-   Registered in `AppServiceProvider::boot()` method
-   Uses dependency injection to receive `FrontendPreferencesService` instance

#### Middleware

**`App\Http\Middleware\ApplyFrontendPreferences`** automatically applies preferences on each request:

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

**Controller**: `App\Http\Controllers\PreferencesController`

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

## Intelephense Helper

The project includes `IntelephenseHelper.php` at the root to provide type hints for Intelephense (PHP language server). This file contains interface definitions for Laravel facades and contracts to help Intelephense understand method signatures and return types.

### Fixing Intelephense Errors

**Rule**: When encountering Intelephense errors like "Undefined method 'X'.intelephense(P1013)", always fix them by adding the missing method definitions to `IntelephenseHelper.php`.

1. **Identify the missing method**: Check which facade or interface needs the method
2. **Add to appropriate interface**: Add the method signature to the corresponding interface in `IntelephenseHelper.php`
3. **Include proper PHPDoc**: Add proper return types and parameter documentation
4. **Match Laravel's API**: Ensure the method signature matches Laravel's actual API

### Common Patterns

-   **Facade methods**: Add static methods to the facade interface (e.g., `Auth::logout()`)
-   **Guard methods**: Add instance methods to `Guard` or `StatefulGuard` interfaces
-   **Builder methods**: Add methods to `Builder` interface for query builder macros
-   **Model methods**: Add methods to model-related interfaces if needed

### Example

If you see `Auth::guard('web')->logout()` causing an error:

1. Add `logout(): void` to the `StatefulGuard` interface
2. Update `Auth::guard()` return type to `StatefulGuard` instead of `Guard`
3. Optionally add `logout()` directly to `Auth` facade for convenience

## Notes for AI Agents

1. **Always check existing code** before creating new components
2. **Use Laravel Boost tools** for documentation and debugging
3. **Follow existing patterns** - check sibling files for conventions
4. **Test all changes** - write or update tests
5. **Format code** with Pint before finalizing
6. **Use stable configs** - prefer `config()` over `env()` where possible
7. **Base model classes required** - ALL new models must extend `App\Models\Base\BaseModel` or `App\Models\Base\BaseUserModel`
8. **UUID columns required** - ALL tables must have a UUID column in their migrations
9. **UUID generation required** - ALL models automatically generate UUIDs via base classes
10. **Fix Intelephense errors** - Always update `IntelephenseHelper.php` when encountering undefined method errors
11. **PSR-4 compliance required** - ALL classes must follow PSR-4 autoloading standards. Test support classes must be in `tests/Support/` with proper namespaces, never defined directly in test files
12. **Use constants, avoid duplication** - Always use constants instead of hardcoded strings when possible, and always avoid duplication for easy maintenance
13. **Component documentation required** - **ALWAYS update `docs/components.md` when adding new UI components** - Include props, usage examples, implementation details, and add to component index
14. **Update this file** when adding new patterns, conventions, or features

## Changelog

### 2025-01-XX

-   **Notification System Improvements**:
    -   **Fixed duplicate toast notifications**: Fixed issue where `toastCenter` component was creating duplicate subscriptions when re-initialized (e.g., during Livewire navigation). Changed from cleanup-based approach to idempotent subscription logic - component now checks if already subscribed and returns early instead of cleaning up and re-subscribing.
    -   **Added `toUserTeams()` method**: New method in `NotificationBuilder` to send notifications to all teams a user belongs to. Broadcasts to each team channel separately, or falls back to user channel if user has no teams. Supports persistence for all team members in each team.
    -   **Updated documentation**: Added `toUserTeams()` usage examples and updated broadcasting channels section in `docs/notifications.md` and `AGENTS.md`.

### 2025-01-XX

-   **Asset Management Optimization**: Refactored CSS/JS structure to avoid duplication and optimize bundle sizes
    -   **Created Base CSS**: Created `resources/css/base.css` containing all Tailwind/DaisyUI configuration (shared foundation)
    -   **Modular CSS Structure**:
        -   `app.css` imports `base.css` + `sidebar.css` (for authenticated app layout)
        -   `auth.css` imports only `base.css` (for authentication layout, smaller bundle)
        -   `sidebar.css` contains only component styles (no Tailwind imports)
    -   **Conditional Asset Loading**:
        -   App layout loads: `app.css`, `app.js`, `notification-center.js`
        -   Auth layout loads: `auth.css`, `app.js` (no sidebar styles or notification JS)
    -   **Benefits**:
        -   No CSS duplication (base styles shared via CSS imports)
        -   Smaller bundle sizes (auth pages don't load unnecessary assets)
        -   Maintainable (single source of truth for base styles)
        -   Uses Tailwind CSS v4's automatic `@import` bundling
    -   **Vite Configuration**: Updated entry points to `app.css`, `auth.css`, `app.js`, `notification-center.js`
    -   **Documentation**: Added comprehensive Asset Management section to `AGENTS.md` with file structure, loading patterns, and guidelines for adding new assets

### 2025-12-19

-   **Notification Dropdown Enhancements**: Improved dropdown state management and badge calculation
    -   **Badge Calculation**: Moved badge calculation from Blade template to Livewire computed property `getUnreadBadgeProperty()` (capped at "99+")
    -   **State Management**: Added Alpine.js reactive state (`isOpen` and `wasOpened`) to track dropdown open/close state
    -   **Auto-Mark as Read**: Notifications are now marked as read when the dropdown closes (via `@click.away`), but only if it was actually opened by the user
    -   **Persistent State**: The `dropdown-open` class is managed via Alpine.js `x-bind:class` to maintain state during Livewire updates
    -   **Badge Styling**: Updated badge to use `badge-xs` with `w-4 h-4` fixed size for smaller, cleaner appearance
    -   **Dropdown Component Enhancement**: Updated dropdown component to properly merge Alpine.js `x-bind:class` with static classes using `$attributes->merge()`
    -   **Notification Center**: Updated to use `#[Computed]` attribute instead of `getXxxProperty()` methods for better Livewire 4 compatibility

### 2025-01-XX

-   **Frontend Preferences System Refactoring**: Refactored `FrontendPreferencesService` to use session as single source of truth
    -   **Session-First Architecture**: Session is now always the single source of truth for all preference reads
    -   **Loading Flow**:
        -   Authenticated users: On first read, preferences are loaded from database and synced to session. Subsequent reads come from session.
        -   Guest users: All reads come from session
    -   **Update Flow**:
        -   Authenticated users: Database is updated first, then session is updated
        -   Guest users: Session is updated only
    -   **Benefits**:
        -   Single source of truth simplifies logic
        -   Fast reads from session (no database queries on every read)
        -   Preferences persist in database for authenticated users
        -   Database and session stay in sync for authenticated users
    -   **Implementation**:
        -   Removed `$persistentStore` property, replaced with `$sessionStore`
        -   Added `syncFromDatabaseIfNeeded()` method to sync DB preferences to session on first read
        -   Added `syncUserPreferencesToSession()` method to sync preferences for a specific user
        -   Updated `set()` and `setMany()` to update DB first for authenticated users, then session
        -   All reads now come from session after initial sync
    -   **Login Event Listener**: Created `SyncUserPreferencesOnLogin` listener class using `php artisan make:listener` to sync preferences from DB to session immediately on login
    -   **Tests**: All 21 FrontendPreferencesService tests pass (including login sync test), all 8 PreferencesController tests pass
    -   **Documentation**: Updated `AGENTS.md` with new architecture details, loading/update flows, and login event listener

### 2025-01-XX

-   **Sidebar CSS Cleanup**: Removed unused mobile menu classes

    -   **Removed Classes**: Removed `.sidebar-desktop` and `.sidebar-mobile` CSS classes from `resources/css/sidebar.css`
    -   **Removed from Blade**: Removed `.sidebar-desktop` class from `sidebar.blade.php` component
    -   **Responsive Behavior**: Responsive behavior is now handled entirely by DaisyUI's drawer component (`lg:drawer-open` class)
    -   **Simplified CSS**: CSS now only includes styles for `.sidebar-top-menus`, `.sidebar-bottom-menus`, and `.sidebar-user-menus`
    -   **Documentation**: Updated `AGENTS.md` to reflect removal of mobile menu classes

-   **BladeServiceProvider Refactoring**: Improved View Composer organization and data sharing

    -   **Method Organization**: Split into separate methods (`initLayoutVariables()`, `initPageTitle()`, `initPageSubtitle()`) for better maintainability
    -   **Value-Based Sharing**: Changed from sharing service objects (`$i18n`, `$preferences`, `$menuService`) to sharing specific values (`$htmlLangAttribute`, `$currentTheme`, `$sideBarTopMenus`, etc.)
    -   **Targeted Composers**: Removed wildcard patterns, using more specific view paths for better performance
    -   **Sidebar Menu Data**: Changed from sharing `$menuService` object to sharing specific menu arrays (`$sideBarTopMenus`, `$sideBarBottomMenus`, `$sideBarUserMenus`)
    -   **Layout Templates**: Updated to use specific values (`$htmlLangAttribute`, `$htmlDirAttribute`, `$currentTheme`) instead of service objects
    -   **Documentation**: Updated View Composers section to reflect new structure and shared variables

-   **Auth Layout Simplification**: Streamlined authentication layout structure

    -   **Removed Components**: Deleted `auth/card.blade.php` and `auth/simple.blade.php` components
    -   **Single Layout**: Now only uses `auth/split.blade.php` component for all authentication pages
    -   **Component Structure**: `split.blade.php` changed from full HTML document to component-only (removed DOCTYPE, html, head, body tags)
    -   **Layout Wrapper**: `auth.blade.php` now wraps `split.blade.php` component instead of `simple.blade.php`
    -   **Route Updates**: Changed logo links from `route('home')` to `route('dashboard')` in split layout
    -   **File Structure**:
        -   `resources/views/components/layouts/auth.blade.php` - Main auth layout wrapper
        -   `resources/views/components/layouts/auth/split.blade.php` - Split-screen auth component

-   **App Layout Simplification**: Cleaned up app layout structure

    -   **Removed Nested Main**: Removed nested `<main>` tag from `app.blade.php` (moved to `sidebar.blade.php`)
    -   **Value-Based Variables**: Changed from service objects to specific values (`$htmlLangAttribute`, `$currentTheme`)
    -   **Simplified Structure**: Cleaner component hierarchy

-   **I18nService Enhancement**: Removed default fallback values

    -   **No Defaults**: `getDefaultLocale()` and `getFallbackLocale()` methods no longer have hardcoded fallback values
    -   **Config Required**: These methods now rely entirely on `config('i18n.*')` values
    -   **Better Error Handling**: Ensures configuration is properly set

-   **BasePageComponent Enhancement**: Added subtitle support to page title management system

    -   **Page Subtitle**: `BasePageComponent` now supports optional `$pageSubtitle` property alongside `$pageTitle`
    -   **Automatic Sharing**: Subtitles are automatically shared via `View::share()` in `boot()` method, just like titles
    -   **Translation Support**: Subtitles support translation keys (containing dots) - automatically translated via `__()`
    -   **Header Display**: Header component (`components.layouts.app.header`) now conditionally displays subtitles below the title
    -   **View Composers**: `BladeServiceProvider` updated to share `$pageSubtitle` with header and head partials
    -   **Usage**: Set `public string $pageSubtitle = 'ui.pages.example.description';` property in components (optional)
    -   **Updated Components**: Settings pages (profile, password, two-factor) now use subtitles for better UX
    -   **Documentation**: Updated `AGENTS.md` with subtitle usage examples and requirements

-   **Sidebar Component Refactoring**: Unified sidebar structure for better maintainability
    -   **Unified Component**: Removed separate `desktop-menu.blade.php` and `mobile-menu.blade.php` components
    -   **Single Component**: Created unified `sidebar-menus.blade.php` component (`<x-layouts.app.sidebar-menus />`)
    -   **Removed Mobile Menu Classes**: Removed `.sidebar-desktop` and `.sidebar-mobile` CSS classes - responsive behavior is handled by DaisyUI's drawer component
    -   **File Structure**:
        -   `resources/views/components/layouts/app/sidebar.blade.php` - Main wrapper (`<x-layouts.app.sidebar>`)
        -   `resources/views/components/layouts/app/sidebar-menus.blade.php` - Unified menu component (`<x-layouts.app.sidebar-menus />`)
        -   `resources/views/components/layouts/app/header.blade.php` - Header component (`<x-layouts.app.header />`)
    -   **View Composers**: Sidebar now uses View Composers (in `BladeServiceProvider`) to inject menu data automatically
    -   **No Props Needed**: Removed need to pass menu data as props - data is automatically available via View Composers
    -   **Integrated Navbar**: Mobile menu toggle is now integrated directly into the navbar within `sidebar.blade.php`
    -   **Simplified Structure**: Cleaner component hierarchy with fewer files to maintain
    -   **Updated Documentation**: Updated sidebar component documentation to reflect unified structure with exact file paths and component references

### 2025-01-XX

-   **Automatic Page Title Management**: Implemented automatic page title management system using `$pageTitle` variable
    -   **BasePageComponent**: Created `App\Livewire\BasePageComponent` base class for all full-page Livewire components
    -   **Title Resolution**: Supports static (component property) and controller (view data) methods
    -   **Translations**: Automatic translation support - translation keys (containing dots) are automatically translated via `__()`
    -   **View Composer**: Added View Composer in `BladeServiceProvider` to share `$pageTitle` with `partials.head` and `components.layouts.app.header`
    -   **SPA Navigation**: Full support for `wire:navigate` with automatic title updates via `View::share()` in component's `boot()` method
    -   **Seamless**: Uses `boot()` lifecycle hook - no need to call `parent::mount()`
    -   **Rule**: ALL full-page Livewire components MUST extend `BasePageComponent` (not `Livewire\Component`)
    -   **Usage**: Set `public ?string $pageTitle = 'ui.pages.example';` property in components (use translation keys)
    -   **Translation Files**: Added `ui.pages.*` section to translation files (`lang/en_US/ui.php`, `lang/fr_FR/ui.php`)
    -   **Updated Components**: All existing Livewire page components now extend `BasePageComponent` and use translation keys
    -   **Documentation**: Updated `AGENTS.md` with BasePageComponent requirement, translation usage, and usage examples
    -   **Later Enhanced**: Added `$pageSubtitle` support for optional subtitle text displayed below page titles in header

### 2025-01-XX

-   **Icon Component Refactoring**: Converted icon component from Livewire to Blade component
    -   **Converted to Blade Component**: Changed from Livewire component (`⚡dynamic-icon-island.blade.php`) to regular Blade component (`ui/icon.blade.php`)
    -   **Moved to UI Folder**: Component is now located at `resources/views/components/ui/icon.blade.php`
    -   **Updated Usage**: All references changed from `<livewire:dynamic-icon-island>` to `<x-ui.icon>`
    -   **Added Security**: Implemented input validation and sanitization for icon names, pack names, and CSS classes
    -   **Size Support**: Added support for predefined sizes (xs, sm, md, lg, xl) for backward compatibility
    -   **Performance**: Removed Livewire overhead for static icon rendering (no reactivity needed)
    -   **Dependency Injection**: Uses `@inject` directive to inject `IconPackMapper` service
    -   **Updated Documentation**: Navigation system documentation updated to reflect new icon component usage

### 2025-01-XX

-   **BasePageComponent Enhancement**: Added subtitle support to page title management system

    -   **Page Subtitle**: `BasePageComponent` now supports optional `$pageSubtitle` property alongside `$pageTitle`
    -   **Automatic Sharing**: Subtitles are automatically shared via `View::share()` in `boot()` method, just like titles
    -   **Translation Support**: Subtitles support translation keys (containing dots) - automatically translated via `__()`
    -   **Header Display**: Header component (`components.layouts.app.header`) now conditionally displays subtitles below the title
    -   **View Composers**: `BladeServiceProvider` updated to share `$pageSubtitle` with header and head partials
    -   **Usage**: Set `public string $pageSubtitle = 'ui.pages.example.description';` property in components (optional)
    -   **Updated Components**: Settings pages (profile, password, two-factor) now use subtitles for better UX
    -   **Documentation**: Updated `AGENTS.md` with subtitle usage examples and requirements

-   **Sidebar Component Refactoring**: Unified sidebar structure for better maintainability

    -   **Unified Component**: Removed separate `desktop-menu.blade.php` and `mobile-menu.blade.php` components
    -   **Single Component**: Created unified `sidebar-menus.blade.php` component
    -   **Removed Mobile Menu Classes**: Removed `.sidebar-desktop` and `.sidebar-mobile` CSS classes - responsive behavior is handled by DaisyUI's drawer component
    -   **View Composers**: Sidebar now uses View Composers (in `BladeServiceProvider`) to inject menu data automatically
    -   **No Props Needed**: Removed need to pass menu data as props - data is automatically available via View Composers
    -   **Integrated Navbar**: Mobile menu toggle is now integrated directly into the navbar within `sidebar.blade.php`
    -   **Simplified Structure**: Cleaner component hierarchy with fewer files to maintain
    -   **Updated Documentation**: Updated sidebar component documentation to reflect unified structure

-   **Navigation System Refactoring**: Simplified navigation system and split sidebar components
    -   **Removed form/button support** from `NavigationItem`: Form and button methods (`form()`, `button()`) have been removed. Use static forms in Blade templates for actions like logout.
    -   **Removed class property**: The `class()` method has been removed from `NavigationItem`. Use `attributes(['class' => '...'])` instead.
    -   **Attributes as array**: `NavigationItem` now returns attributes as an array (not a string) for use with `$attributes->merge()` in Blade components.
    -   **Service injection**: Updated to use View Composers instead of `@inject` directive for automatic menu data injection.
    -   **Semantic HTML**: Navigation components now use `<div>` elements instead of `<ul>`/`<li>` for better flexibility.
    -   **Static logout form**: Logout is now handled as a static form in the sidebar components, not through `NavigationItem`.
    -   **Updated tests**: Removed `NavigationItemFormTest.php` as form/button functionality no longer exists. Test count: 24 tests, 52 assertions.

### 2025-01-XX

-   **Livewire 4 Folder Structure Reorganization**: Removed `livewire/` directory to align with Livewire 4 philosophy
    -   Moved auth views from `livewire/auth/` to `pages/auth/` (full-page components)
    -   Moved nested components from `livewire/settings/` to `components/settings/` (reusable components)
    -   Updated `FortifyServiceProvider` to reference new auth view paths (`pages.auth.*`)
    -   Removed `livewire` from `component_locations` in `config/livewire.php`
    -   **New Structure**:
        -   Full-page components: `resources/views/pages/` (use `pages::` namespace)
        -   Nested/reusable Livewire components: `resources/views/components/` (referenced directly, e.g., `livewire:settings.delete-user-form`)
        -   Regular Blade components: `resources/views/components/`
    -   Since Livewire is the default in Livewire 4, no separate `livewire/` folder is needed

### 2025-12-13

-   **Livewire 4 Folder Structure Migration**: Completed migration to Livewire 4 folder structure
    -   Moved full-page components from `livewire/settings/` to `pages/settings/` with `.blade.php` extension
    -   Updated routes to use `pages::settings.*` namespace format
    -   Created Livewire layouts in `resources/views/layouts/` with `@livewireStyles` and `@livewireScripts`
    -   Created Blade component wrappers in `resources/views/components/layouts/` for regular views
    -   Updated `config/livewire.php` to include `pages` in `component_locations` and `component_namespaces`
    -   All single-file components now use `.blade.php` extension (required by Livewire 4)

### 2025-01-XX

-   **Livewire 4 Comprehensive Documentation**: Created comprehensive `docs/livewire-4.md` with AI-friendly indexing system
    -   Added detailed AI-friendly index at the top with quick reference by topic and search keywords
    -   Comprehensive coverage of all Livewire 4 features: Components, Properties, Actions, Forms, Events, Lifecycle Hooks, Nesting, Testing, AlpineJS Integration, Navigation, Islands, Lazy Loading, Loading States, Validation, File Uploads, Pagination, URL Query Parameters, File Downloads, Teleport, Morphing, Hydration, Synthesizers, JavaScript, Troubleshooting, Security, CSP
    -   Each section includes code examples, usage patterns, and cross-references
    -   Search keywords section for AI assistants to quickly locate specific functionality
    -   Organized by core concepts, advanced features, validation & data, UI & interaction, advanced technical, testing & troubleshooting, and security & configuration
    -   **Documentation**: See `docs/livewire-4.md` for complete Livewire 4 documentation with AI-friendly indexing

### 2025-12-13

-   **Logging Configuration**: Configured daily log rotation with level-specific folders and exact level filtering
    -   Each log level (emergency, alert, critical, error, warning, notice, info, debug) now has its own folder: `storage/logs/{level}/laravel-{date}.log`
    -   Daily rotation enabled for all level-specific channels using Monolog's RotatingFileHandler
    -   **Exact level filtering**: Each log file contains ONLY messages of its exact level using Monolog's FilterHandler
    -   Created `App\Logging\LevelSpecificLogChannelFactory` to handle exact level filtering with daily rotation
    -   Deprecated logs configured with daily rotation in `storage/logs/deprecations/laravel-{date}.log`
    -   Default stack channel routes to all level-specific channels
    -   Retention configurable via `LOG_DAILY_DAYS` environment variable (default: 14 days)
-   **Constants and Code Reusability Rule**: Added critical rule for using constants and avoiding duplication
    -   Created `App\Constants\LogLevels` class for log level constants
    -   Created `App\Constants\LogChannels` class for log channel constants
    -   Refactored `config/logging.php` to use constants and helper function to eliminate duplication
    -   Added rule to agent.md: Always use constants instead of hardcoded strings when possible, and always avoid duplication for easy maintenance
-   **Log Clearing Command**: Created `php artisan logs:clear` command
    -   Clears all log files from `storage/logs` directory
    -   Supports `--level` option to clear logs for a specific level only
    -   Uses constants from `LogChannels` class
    -   Provides helpful feedback with Laravel Prompts

### 2025-12-16

-   **Frontend Preferences System**: Implemented centralized frontend preferences service for managing user preferences (locale, theme, timezone)

    -   **Service**: `App\Services\FrontendPreferences\FrontendPreferencesService` (singleton) with session-backed caching
    -   **Storage**: Guest users store preferences in session; authenticated users persist to `users.frontend_preferences` JSON column
    -   **Performance**: First request loads from DB into session cache; subsequent reads use session cache only
    -   **Middleware**: `ApplyFrontendPreferences` automatically applies locale and timezone preferences on each request
    -   **UI Components**: Language and theme switchers (`<x-preferences.locale-switcher />`, `<x-preferences.theme-switcher />`) in app/auth layouts
    -   **Constants**: `App\Constants\FrontendPreferences` for session keys, preference keys, defaults, validation
    -   **Database**: Added `frontend_preferences` JSON column to `users` table with array cast
    -   **Removed**: Settings → Appearance page (theme switcher moved to header/sidebar)

-   **Theme Management**: Switched from client-side `localStorage` to server-side `data-theme` attribute
-   **Auto-Detection**: Automatic browser language detection on first visit (server-side only, no JavaScript)
    -   Language detection from `Accept-Language` header using `$request->header('Accept-Language')`
    -   **No theme detection** - Default theme preference is `"light"` for first-time visitors
    -   **No JavaScript required** - All detection is server-side using request headers
    -   **No cookies used** - All preferences stored in session (guests) or database (authenticated users)
    -   Detection only occurs when no preferences are set (first visit)
    -   Detected preferences are automatically saved and persisted
-   **Comprehensive Tests**: 31 tests covering service, middleware, UI components, and auto-detection behavior
-   **Documentation**: Added Frontend Preferences section to `AGENTS.md` and locale switching info to `docs/internationalization.md`

-   **DateTime and Currency Helper Functions**: Created locale-aware helper functions for formatting dates, times, and currency
    -   Created `app/helpers/dateTime.php` with `formatDate()`, `formatTime()`, and `formatDateTime()` functions
    -   Created `app/helpers/currency.php` with `formatCurrency()` function
    -   Updated `config/i18n.php` to include `symbol_position`, `decimal_separator`, and `thousands_separator` for currency configuration
    -   All helpers use `I18nService` internally instead of direct config access
    -   Added comprehensive tests (18 tests for dateTime, 14 tests for currency)
    -   Updated `composer.json` to autoload new helper files
    -   Updated documentation (`docs/internationalization.md`) with helper function usage
-   **I18nService Enhancements**: Enhanced `I18nService` with additional methods for centralized locale management
    -   Added `getSupportedLocales()`, `getDefaultLocale()`, `getFallbackLocale()`
    -   Added `getLocaleMetadata(?string $locale)`, `isLocaleSupported()`, `getValidLocale()`
    -   Updated service to use its own methods internally for consistency
    -   Added comprehensive tests (18 tests)
-   **BladeServiceProvider**: Created dedicated service provider for Blade/view-related functionality
    -   Moved View Composer logic from `AppServiceProvider` to `BladeServiceProvider`
    -   Shares `I18nService` with layout templates via View Composers
    -   Shares `SideBarMenuService` only with sidebar template
    -   Replaced all `@inject` directives with View Composers
    -   Added comprehensive tests (4 tests)
-   **Code Style Rules**: Added new rules to `AGENTS.md`
    -   Always use function guards and early returns
    -   Do NOT use `function_exists()` checks in helper files
    -   Always use `I18nService` for locale-related code
    -   Use View Composers instead of `@inject` for global data

### 2025-01-XX

-   **Livewire 4 Upgrade**: Upgraded from Livewire v3 + Volt to Livewire v4 (beta) with built-in single-file components
    -   Updated `composer.json` to require `livewire/livewire:^4.0@beta` and removed `livewire/volt`
    -   Converted all Volt components to Livewire 4 single-file components (replaced `Livewire\Volt\Component` with `Livewire\Component`)
    -   Updated routes from `Volt::route()` to `Route::livewire()` (preferred method in Livewire 4)
    -   Removed `VoltServiceProvider` and updated `bootstrap/providers.php`
    -   **Folder Structure Reorganization**:
        -   Moved full-page components to `resources/views/pages/` with `pages::` namespace
        -   Created `resources/views/layouts/` for Livewire page layouts (with `@livewireStyles`/`@livewireScripts`)
        -   Created Blade component wrappers in `resources/views/components/layouts/` for regular views
        -   Updated `config/livewire.php` with proper `component_locations` and `component_namespaces`
    -   **File Extensions**: All single-file components must use `.blade.php` extension (not `.php`)
    -   Created comprehensive `docs/livewire-4.md` documentation file
    -   Updated agent.md to reflect Livewire 4 patterns and reference documentation
    -   **Documentation**: See `docs/livewire-4.md` for complete Livewire 4 documentation, upgrade guide, and best practices
-   Initial agent.md creation
-   Documented stable configuration patterns
-   Documented Redis client environment-based selection
-   Documented project structure and conventions
-   Added environment helper functions (`app/helpers/app-helpers.php`)
    -   Functions: `appEnv()`, `isProduction()`, `isDevelopment()`, `isStaging()`, `isLocal()`, `isTesting()`, `inEnvironment()`
    -   Updated config files to use helper functions instead of direct config checks
-   **UUID Requirement**: All tables must have a UUID column
    -   Updated all existing migrations to include UUID columns
    -   Added rule for future development: all new tables must include `$table->uuid('uuid')->unique()->index();`
-   **Automatic UUID Generation**: Implemented `HasUuid` trait and base model classes
    -   Created `App\Models\Concerns\HasUuid` trait that automatically generates unique UUIDs
    -   Created `App\Models\Base\BaseModel` base class for regular models (includes HasUuid)
    -   Created `App\Models\Base\BaseUserModel` base class for authenticatable models (includes HasUuid, HasFactory, Notifiable)
    -   Updated User model to extend `BaseUserModel`
    -   UUIDs are generated on model creation and checked for uniqueness
    -   Models using base classes use UUID as route key name
    -   Added comprehensive tests for UUID generation
    -   **Rule**: All new models must extend `App\Models\Base\BaseModel` or `App\Models\Base\BaseUserModel` instead of Eloquent base classes
-   **Soft Delete Requirement**: Implemented soft deletes for all models by default
    -   Added `SoftDeletes` trait to `BaseModel` and `BaseUserModel` base classes
    -   Updated migrations to include `$table->softDeletes();` for: `users`, `teams`, `permissions`, `roles`, `notifications`
    -   Added `SoftDeletes` trait to `Permission` and `Role` models (extend Spatie's models)
    -   **Exceptions**: `PasswordResetToken` model extends `Model` directly (not `BaseModel`) to avoid soft deletes, as password reset tokens are temporary and should be hard deleted
    -   **Rule**: All new models must have soft deletes enabled by default via base classes
    -   **Rule**: All new migrations must include `$table->softDeletes();` unless the table is an exception (temporary tokens, pivot tables, monitoring tables)
-   **Intelephense Helper**: Added rule and documentation for fixing Intelephense errors
    -   Updated `IntelephenseHelper.php` with missing Auth and Session facade methods
    -   Added `logout()`, `login()`, `attempt()` methods to `StatefulGuard` and `Auth` interfaces
    -   Added `Session` facade interface with common methods (`invalidate()`, `regenerateToken()`, etc.)
    -   **Rule**: Always fix Intelephense errors by adding missing method definitions to `IntelephenseHelper.php`
-   **PSR-4 Autoloading Standards**: Added comprehensive PSR-4 autoloading rules
    -   Documented autoload mappings in `composer.json`
    -   **Rule**: Test support classes (models, helpers) MUST be in `tests/Support/` with proper namespaces
    -   **Rule**: Never define classes directly in test files - always create separate files in `tests/Support/`
    -   Moved `TestModel` from test file to `tests/Support/Models/TestModel.php` with namespace `Tests\Support\Models`
    -   Added examples of correct vs incorrect patterns
    -   **Rule**: All classes must comply with PSR-4 autoloading standards to prevent autoloader warnings

---

**Remember**: This file is a living document. Update it as the project evolves!
