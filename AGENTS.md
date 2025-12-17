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
-   **PHPDoc**: Prefer PHPDoc blocks over inline comments
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
                    ->show(auth()->user()->hasRole('admin')),

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

The sidebar is split into separate components for better organization:

-   **`sidebar.blade.php`**: Main wrapper component using DaisyUI drawer
-   **`mobile-menu.blade.php`**: Mobile navbar (visible on small screens)
-   **`desktop-menu.blade.php`**: Desktop sidebar (visible on large screens)

**Usage**:

```php
@inject('menuService', \App\Services\SideBarMenuService::class)

<x-layouts.app.sidebar>
    <!-- Main content -->
</x-layouts.app.sidebar>
```

**Note**: The sidebar uses `@inject` directive for service injection and passes menu data to child components. Navigation items use `<div>` elements instead of `<ul>`/`<li>` for semantic HTML flexibility.

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

The `BladeServiceProvider` uses View Composers to share services with Blade templates:

-   **I18nService**: Shared with layout templates (`components.layouts.app`, `components.layouts.app.*`, `components.layouts.auth`, `components.layouts.auth.*`)
-   **SideBarMenuService**: Shared only with `components.layouts.app.sidebar`

**Usage in Blade:**

```blade
{{-- $i18n is automatically available in layout templates --}}
<html lang="{{ $i18n->getHtmlLangAttribute() }}" dir="{{ $i18n->getHtmlDirAttribute() }}">

{{-- $menuService is automatically available in sidebar template --}}
<x-layouts.app.sidebar>
    {{-- Use $menuService here --}}
</x-layouts.app.sidebar>
```

**Rule**: Use View Composers instead of `@inject` for global data shared with templates.

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

**Storage Strategy**:

-   **Guest users**: Preferences stored in session only
-   **Authenticated users**: Preferences stored in `users.frontend_preferences` JSON column + cached in session
-   **Performance**: First request loads from DB into session cache; subsequent reads use session cache only
-   **Updates**: When preferences change, both DB (if authenticated) and session cache are updated

**Stores** (SOLID design):

-   `App\Services\FrontendPreferences\Contracts\PreferencesStore` - Interface
-   `App\Services\FrontendPreferences\Stores\SessionPreferencesStore` - Session-based storage
-   `App\Services\FrontendPreferences\Stores\UserJsonPreferencesStore` - Database JSON storage

**Constants**: `App\Constants\FrontendPreferences` - Session keys, preference keys, defaults, validation

#### Available Preferences

-   **`locale`**: User's preferred language (validated via `I18nService`)
-   **`theme`**: UI theme (`light` or `dark`, validated)
-   **`timezone`**: User's timezone for display purposes only (validated PHP timezone identifier)
    -   **Important**: Timezone preference is for display only. All dates/times are stored in the database using the application timezone from `config/app.php`
    -   Date/time formatting helpers (`formatDate()`, `formatTime()`, `formatDateTime()`) automatically use the user's timezone preference when displaying dates/times

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

// Refresh from persistent store
$preferences->refresh();
```

**In Blade Templates:**

The `FrontendPreferencesService` is automatically shared with layout templates via View Composers:

```blade
{{-- $preferences is automatically available in layout templates --}}
<html data-theme="{{ $preferences->getTheme() }}">
```

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
13. **Update this file** when adding new patterns, conventions, or features

## Changelog

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

-   **Navigation System Refactoring**: Simplified navigation system and split sidebar components
    -   **Removed form/button support** from `NavigationItem`: Form and button methods (`form()`, `button()`) have been removed. Use static forms in Blade templates for actions like logout.
    -   **Removed class property**: The `class()` method has been removed from `NavigationItem`. Use `attributes(['class' => '...'])` instead.
    -   **Attributes as array**: `NavigationItem` now returns attributes as an array (not a string) for use with `$attributes->merge()` in Blade components.
    -   **Sidebar component split**: Split `sidebar.blade.php` into three components:
        -   `sidebar.blade.php`: Main wrapper component
        -   `mobile-menu.blade.php`: Mobile navbar component
        -   `desktop-menu.blade.php`: Desktop sidebar component
    -   **Service injection**: Updated to use `@inject` Blade directive instead of `app()` helper for cleaner code.
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
    -   **UI Components**: Language and theme switchers (`livewire:preferences.switchers`) in app/auth layouts
    -   **Constants**: `App\Constants\FrontendPreferences` for session keys, preference keys, defaults, validation
    -   **Database**: Added `frontend_preferences` JSON column to `users` table with array cast
    -   **Removed**: Settings → Appearance page (theme switcher moved to header/sidebar)
    -   **Theme Management**: Switched from client-side `localStorage` to server-side `data-theme` attribute
    -   **Comprehensive Tests**: 23 tests covering service, middleware, and UI component behavior
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
