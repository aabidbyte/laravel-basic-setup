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
-   **PHPDoc**: Prefer PHPDoc blocks over inline comments

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
-   **Component Locations**:
    -   **Full-page components**: `resources/views/pages/` (use `pages::` namespace in routes)
    -   **Nested/reusable Livewire components**: `resources/views/components/` (use component name directly, e.g., `livewire:settings.delete-user-form`)
    -   **Blade components**: `resources/views/components/` (regular Blade components)
-   **File Extensions**: Single-file components must use `.blade.php` extension (not `.php`)
-   **Component Namespaces**: Configured in `config/livewire.php`:
    -   `pages` namespace → `resources/views/pages/`
    -   `layouts` namespace → `resources/views/layouts/`
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

### Planned/In Development

-   Check project roadmap or issues for planned features

## Important Patterns

### Livewire 4 Single-File Component Pattern

**File Location**: `resources/views/pages/example.blade.php` (must use `.blade.php` extension)

```php
<?php

use Livewire\Component;

new class extends Component {
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
13. **Update this file** when adding new patterns, conventions, or features

## Changelog

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
