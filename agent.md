# Agent Documentation

> **Important**: This file must be updated during development to reflect changes, new patterns, conventions, and project context.

## Project Overview

This is a Laravel 12 SaaS application built with Livewire Volt and Flux UI. The application follows modern Laravel conventions with a focus on stable configurations, comprehensive testing, and maintainable code.

## Technology Stack

### Core Framework & Language

-   **PHP**: 8.4.8
-   **Laravel Framework**: v12.0
-   **Laravel Structure**: Streamlined Laravel 12 structure (no `app/Http/Middleware/`, uses `bootstrap/app.php`)

### Authentication & Security

-   **Laravel Fortify**: v1.30 (headless authentication)
-   **Laravel Sanctum**: v4.0 (API authentication)
-   **Features Enabled**:
    -   User registration
    -   Password reset
    -   Email verification
    -   Two-factor authentication (with password confirmation)

### Frontend Stack

-   **Livewire**: v3 (server-side interactivity)
-   **Livewire Volt**: v1.7.0 (single-file components)
-   **Flux UI Free**: v2.9.0 (component library)
-   **Tailwind CSS**: v4.0.7 (utility-first CSS)
-   **Vite**: v7.0.4 (asset bundling)
-   **Alpine.js**: Included with Livewire (no manual inclusion needed)

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
│   ├── components/       # Blade components
│   ├── flux/            # Flux UI customizations
│   ├── livewire/        # Livewire component views
│   └── partials/         # Reusable partials

routes/
├── web.php              # Web routes (uses Volt for pages)
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

-   **Primary Pattern**: Livewire Volt (single-file components)
-   **UI Library**: Flux UI Free components (check available components before creating custom)
-   **Component Location**:
    -   Volt components: `resources/views/livewire/` or `resources/views/pages/`
    -   Blade components: `resources/views/components/`
-   **Naming**: Use descriptive names (e.g., `isRegisteredForDiscounts`, not `discount()`)

### Routing

-   **Web Routes**: Use `Volt::route()` for interactive pages
-   **Static Views**: Use `Route::view()` for simple pages
-   **Named Routes**: Always use named routes with `route()` helper
-   **Example**:
    ```php
    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
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

### Planned/In Development

-   Check project roadmap or issues for planned features

## Important Patterns

### Volt Component Pattern

```php
@volt
<?php
use function Livewire\Volt\state;

state(['count' => 0]);

$increment = fn () => $this->count++;
?>

<div>
    <h1>Count: {{ $count }}</h1>
    <flux:button wire:click="increment">+</flux:button>
</div>
@endvolt
```

### Livewire Best Practices

-   Single root element required
-   Use `wire:loading` for loading states
-   Use `wire:key` in loops
-   Use `wire:model.live` for real-time updates (deferred by default in v3)
-   Prefer lifecycle hooks (`mount()`, `updatedFoo()`)

### Flux UI Usage

-   Use Flux components when available
-   Check existing components before creating custom
-   Available components: avatar, badge, brand, breadcrumbs, button, callout, checkbox, dropdown, field, heading, icon, input, modal, navbar, otp-input, profile, radio, select, separator, skeleton, switch, text, textarea, tooltip

## Development Workflow

### Setup

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
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
    - Both protected by authorization middleware

## Common Tasks

### Creating a New Volt Component

```bash
php artisan make:volt pages.example --test --pest
```

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
12. **Update this file** when adding new patterns, conventions, or features

## Changelog

### 2025-01-XX

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
