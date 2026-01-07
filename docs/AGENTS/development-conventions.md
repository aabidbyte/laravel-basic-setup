## Development Conventions

### Reading Documentation Files

-   **Large Markdown Files**: When reading documentation files (e.g., `docs/AGENTS/index.md`, `docs/*/index.md`) that are too large to read at once, split the reading into sections using the `offset` and `limit` parameters
-   **Efficient Reading**: Use `grep` to find specific sections first, then read those sections using offset/limit for focused reading
-   **Documentation Structure**: Documentation files are organized with clear headings - use these headings to navigate and read relevant sections. Large documentation files (>1000 lines) are split into index-based structure with separate section files
-   **Example**: Instead of reading all lines of a large documentation file, use `grep` to find the section you need, then read that specific section file directly

### Folder Structure Organization

-   **Domain-Based Organization**: Always structure folders to reflect the domain or purpose of their content
-   **Subfolders by Domain**: Create subfolders under main directories (like `Responses`, `Requests`, `Controllers`, etc.) to organize files by domain/feature
-   **Examples**:
    -   Fortify-related responses should be in `app/Http/Responses/Fortify/`
    -   Preference-related requests should be in `app/Http/Requests/Preferences/`
    -   Authentication-related requests should be in `app/Http/Requests/Auth/`
    -   Authentication controllers should be in `app/Http/Controllers/Auth/`
    -   Authentication middleware should be in `app/Http/Middleware/Auth/`
    -   DataTable enums should be in `app/Enums/DataTable/`
    -   Toast enums should be in `app/Enums/Toast/`
    -   Constants should be organized by domain (e.g., `app/Constants/Auth/`, `app/Constants/DataTable/`, `app/Constants/Logging/`, `app/Constants/Preferences/`)
    -   Events should be organized by domain (e.g., `app/Events/Notifications/`)
    -   Listeners should be organized by domain (e.g., `app/Listeners/Preferences/`)
    -   Observers should be organized by domain (e.g., `app/Observers/Notifications/`)
-   **Namespace Alignment**: The namespace should match the folder structure (e.g., `App\Http\Responses\Fortify\EmailVerificationNotificationSentResponse`)
-   **Benefits**: This structure improves code organization, makes it easier to find related files, and scales better as the application grows

### Documentation Structure Rule

-   **Large Documentation Files**: Documentation files over 1000 lines must be split into index-based structure
-   **Folder Structure**: Create a folder with the same name as the main doc (without .md extension) under `docs/` folder
-   **Index File**: Create `index.md` in that folder as the main documentation file (acts as table of contents/index)
-   **Section Files**: Split each major section (## heading) into separate `.md` files in the folder
-   **Naming Convention**: Each section file should be named based on the section heading (kebab-case, e.g., "File Uploads" → `file-uploads.md`)
-   **Index Content**: The index.md should contain:
    -   Overview/introduction content
    -   Table of contents with links to section files
    -   Quick reference/index for AI assistants
-   **Benefits**: This structure enables fast indexing and easier navigation for AI tools, better maintainability, and scalability

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
 
 ### Internationalization (i18n)
 
 -   **Namespaces**: **Always use granular namespaces** (e.g., `pages.*`, `users.*`, `actions.*`) instead of the monolithic `ui.*` prefix.
 -   **Generic Pattern**: **Prefer generic keys for CRUD operations**. Use `pages.common.*` combined with `types.*` entities to avoid redundancy.
     -   *Correct*: `__('pages.common.create.title', ['type' => __('types.user')])`
     -   *Incorrect*: `__('users.create_new_user')` (unless highly specific)
 -   **Hardcoded Strings**: **No hardcoded user-facing strings**. Always use `__('namespace.key')`.
 -   **Sync**: Run `php artisan lang:sync` after adding new keys.

### Constants and Code Reusability

⚠️ **CRITICAL RULE**: **Always use constants instead of hardcoded strings when possible, and always avoid duplication for easy maintenance.**

#### Constants Usage

-   **Always define constants** for frequently used string values (log levels, channel names, permission names, role names, etc.)
-   **Constants classes** should be in `app/Constants/` directory, organized by domain (e.g., `app/Constants/Auth/`, `app/Constants/DataTable/`)
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

2.  **Livewire Components**:

    -   **MUST be organized by domain** - never place components directly in `app/Livewire/`
    -   **Required structure**:
        ```
        app/Livewire/
        ├── Bases/              # Base classes (LivewireBaseComponent, BasePageComponent)
        ├── DataTable/          # DataTable system
        │   ├── Datatable.php
        │   └── Concerns/       # DataTable-specific concerns/traits
        ├── Tables/             # Table implementations (UserTable, etc.)
        └── [Domain]/           # Feature-specific components organized by domain
        ```
    -   **Namespaces must match directory structure**:
        -   `App\Livewire\Bases\LivewireBaseComponent`
        -   `App\Livewire\DataTable\Datatable`
        -   `App\Livewire\DataTable\Concerns\HasDatatableLivewireActions`
        -   `App\Livewire\Tables\UserTable`
    -   **All Livewire components MUST extend `LivewireBaseComponent`** (provides placeholder functionality)
    -   **Page components extend `BasePageComponent`** (extends LivewireBaseComponent with title/subtitle handling)
    -   **Use domain-specific `Concerns/` subdirectory** for traits shared within that domain

3.  **Test Support Classes**:

    -   **Test models, helpers, and support classes MUST be in `tests/Support/`**
    -   Use namespace `Tests\Support\{Category}` matching directory structure
    -   Example: `tests/Support/Models/TestModel.php` → `namespace Tests\Support\Models;`
    -   **Never define classes directly in test files** - always create separate files in `tests/Support/`
    -   Test support classes are automatically autoloaded via the `Tests\` → `tests/` mapping

4.  **Test Files**:
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
-   **SFC Requirement**: **ALL Livewire components MUST use Single File Component (SFC) format** - Never create class-based components in `app/Livewire/`. All Livewire components must be single-file components with PHP class and Blade template in the same `.blade.php` file using anonymous class syntax (`new class extends Component { }`). This is the Livewire 4 standard and ensures consistency across the application.
-   **UI Library**: Standard HTML/Tailwind CSS components
-   **Component Reusability**: **ALWAYS use existing components when possible for consistency** - Before creating a new component, check if an existing component can be used or extended. This ensures consistency across the application and reduces code duplication.
-   **Loading States**: **ALWAYS use `<x-ui.loading>` component for loading spinners** - Never use inline `<span class="loading loading-spinner">` markup. Use the centralized component with appropriate props (`size`, `variant`, `color`, `centered`). See `docs/components/loading.md` for documentation. Example:
    ```blade
    {{-- Centered loading (default) --}}
    <x-ui.loading></x-ui.loading>
    
    {{-- Inline spinner --}}
    <x-ui.loading size="sm" :centered="false"></x-ui.loading>
    ```
-   **Component Documentation**: **ALWAYS update `docs/components/index.md` when adding new UI components** - This ensures all components are documented with props, usage examples, and implementation details
-   **Component Tag Format**: **ALL Blade and Livewire component tags MUST use opening and closing tags, never self-closing tags** - Always write `<x-component></x-component>` or `<livewire:component></livewire:component>` instead of `<x-component />` or `<livewire:component />`, even if the component has no content. **Exception**: Standard HTML self-closing tags (void elements) like `<img />`, `<br />`, `<hr />`, `<input />`, `<meta />`, `<link />`, `<area />`, `<base />`, `<col />`, `<embed />`, `<source />`, `<track />`, `<wbr />` should remain self-closing as per HTML5 specification.
-   **Component Props Comments**: **NO comments shall be inside `@props` directive** - All comments for component props MUST be placed at the top of the file, isolated in a Blade comment block (`{{-- --}}`). This keeps the `@props` directive clean and makes component documentation more readable. Example:
    ```blade
    {{--
        Component Props:
        - prop1: Description of prop1
        - prop2: Description of prop2
    --}}
    @props([
        'prop1' => 'default',
        'prop2' => null,
    ])
    ```
-   **Component Locations**:
    -   **Full-page components**: `resources/views/pages/` (use `pages::` namespace in routes)
    -   **Nested/reusable Livewire components**: `resources/views/components/` (use component name directly, e.g., `livewire:users.table`)
    -   **Blade components**: `resources/views/components/` (regular Blade components)
-   **File Extensions**: Single-file components must use `.blade.php` extension (not `.php`)
-   **Component Namespaces**: Configured in `config/livewire.php`:
    -   `pages` namespace → `resources/views/pages/`
    -   `layouts` namespace → `resources/views/layouts/`
-   **BasePageComponent**: **ALL full-page Livewire components MUST extend `App\Livewire\BasePageComponent`**
    -   Provides automatic page title and subtitle management via `$pageTitle` and `$pageSubtitle` properties
    -   Automatically shares title and subtitle with layout views via `View::share()` in `boot()` method (runs automatically)
    -   **Required**: Every component MUST define `public ?string $pageTitle = 'pages.example';` property
    -   **Optional**: Components can define `public string $pageSubtitle = 'pages.example.description';` property for subtitle text
    -   **Translations**: Use namespaced translation keys (e.g., `'pages.dashboard'`) - keys containing dots are automatically translated via `__()`. Avoid the legacy `ui.` prefix.
    -   **Generic Pattern**: For CRUD pages, use generic keys from `pages.common` combined with `types` (e.g., `'pages.common.create.title'` with parameters).
    -   **Plain Strings**: Can also use plain strings (e.g., `'Dashboard'`) if translation is not needed
    -   **Seamless**: No need to call `parent::mount()` - title and subtitle sharing happens automatically via `boot()` lifecycle hook
    -   **Example**: `new class extends BasePageComponent { public ?string $pageTitle = 'pages.dashboard'; public string $pageSubtitle = 'pages.dashboard.description'; }`
    -   **Rule**: Never extend `Livewire\Component` directly for full-page components - always use `BasePageComponent`
-   **Naming**: Use descriptive names (e.g., `isRegisteredForDiscounts`, not `discount()`)
-   **DataTable Components**:
    -   **Location**: `App\Livewire\Tables\`
    -   **Naming**: Must suffix with `Table` (e.g., `UserTable.php`)
    -   **Structure**: Must extend `App\Livewire\DataTableComponent` and provide configuration via methods.
    -   **Usage**: Use `<livewire:tables.user-table />` syntax.
-   **Plain Blade Pages**:
    -   **Title/Subtitle**: MUST use `setPageTitle()` helper at the top of the Blade file to set `$pageTitle` and `$pageSubtitle`.
    -   **Reason**: Abstraction over `view()->share()` for cleaner code.
    -   **Example**:
        ```blade
        @php
            setPageTitle(__('pages.dashboard'), __('pages.dashboard.description'));
        @endphp
        <x-layouts.app>...</x-layouts.app>
        ```

### Component-First UI Development

> **CRITICAL RULE**: All user-facing UI MUST use centralized `x-ui.*` components. Raw HTML tags for common UI patterns are NOT allowed.

#### Mandatory Component Usage

| Pattern | Use Component | NOT Raw HTML |
|---------|---------------|--------------|
| Titles (h1-h6) | `<x-ui.title level="2">` | `<h2 class="...">` |
| Avatars | `<x-ui.avatar :user="$user">` | `<div class="avatar">...</div>` |
| Links (styled) | `<x-ui.link href="...">` | `<a class="link link-primary">` |
| Buttons | `<x-ui.button>` | `<button class="btn btn-primary">` |
| Badges | `<x-ui.badge>` | `<span class="badge">` |
| Loading | `<x-ui.loading>` | `<span class="loading">` |
| Icons | `<x-ui.icon>` | `<svg>...</svg>` or raw Heroicons |
| Inputs | `<x-ui.input>` | `<input class="input">` |
| Forms | `<x-ui.form>` | `<form class="...">` |

#### Allowed Raw HTML

These structural elements are acceptable without components:
-   **Containers**: `<div>`, `<section>`, `<article>`, `<main>`, `<header>`, `<footer>`, `<nav>`, `<aside>`
-   **Layout utilities**: Tailwind's `flex`, `grid`, `gap-*`, `p-*`, `m-*`, `w-*`, `max-w-*`
-   **Semantic lists**: `<ul>`, `<ol>`, `<li>`, `<dl>`, `<dt>`, `<dd>`
-   **Card structures**: DaisyUI's `.card`, `.card-body` classes (no component yet)
-   **Alerts**: DaisyUI's `.alert` classes (no component yet)

#### Rationale

1.  **Consistency**: Components ensure uniform styling across the app
2.  **Maintainability**: Style changes propagate automatically
3.  **Documentation**: Usage is self-documenting via component names
4.  **Refactoring**: Easy to update all instances at once

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

### Livewire Event Parameters

-   **Reserved Parameter Names**: When using `$this->dispatch()` with named parameters, avoid these reserved names that may conflict with Livewire internals:
    -   `component` - Reserved by Livewire for component identification
    -   `id` - May conflict with component ID handling
    -   `type` - May be interpreted as internal type parameter
-   **Recommended Alternatives**: Use descriptive prefixes like `view*` or `modal*`:
    ```php
    // ❌ Bad - may cause ComponentNotFoundException
    $this->dispatch('open-modal', component: 'users.view', type: 'blade');

    // ✅ Good - descriptive and safe
    $this->dispatch('open-modal', viewPath: 'users.view', viewType: 'blade');
    ```
-   **Model Serialization**: When dispatching events with model data, pass UUIDs instead of model instances to avoid serialization issues:
    ```php
    // ❌ Bad - model loses methods after serialization
    ->bladeModal('view-modal', fn (User $user) => ['user' => $user])

    // ✅ Good - re-fetch in Blade view
    ->bladeModal('view-modal', fn (User $user) => ['userUuid' => $user->uuid])
    ```

### Database & Models

-   **ORM**: Eloquent (prefer over raw queries)
-   **Relationships**: Always use proper Eloquent relationships with return type hints
-   **N+1 Prevention**: Use eager loading (`with()`, `load()`)
-   **Query Builder**: Use `Model::query()` instead of `DB::`
-   **Casts**: Use `casts()` method on models (Laravel 12 convention)
-   **Model ID Exposure**: **NEVER communicate model IDs (integer primary keys) to the frontend unless explicitly told to do so**
    -   **Always use UUIDs** when exposing model identifiers in frontend views, API responses, JavaScript, or any client-facing code
    -   **Prefer UUIDs even for internal uses**: Use UUIDs in `wire:key` attributes and other internal tracking (e.g., `wire:key="item-{{ Auth::user()?->uuid ?? 'guest' }}"` instead of `wire:key="item-{{ Auth::id() }}"`)
    -   **Exceptions** (acceptable uses of integer IDs):
        -   Server-side validation rules (e.g., `Rule::unique(User::class)->ignore($user->id)`)
        -   Internal database queries and subqueries that are not exposed to frontend
        -   `App\Models\Notification` model extends `BaseModel` and handles `id` (auto-inc) and `uuid` (string) correctly. It maps Laravel's `DatabaseChannel` UUID to the `uuid` column.
    -   **Route Model Binding**: All models use UUID as route key name (configured in `HasUuid` trait)
    -   **DataTable Components**: Must use `uuid` field from row data, never fall back to `id` field
    -   **API Responses**: Always return UUIDs, never integer IDs
    -   **JavaScript/Client Code**: Never receive or send integer model IDs
-   **Base Model Classes**: **ALL new models MUST extend base model classes**
    -   **Regular models**: Extend `App\Models\Base\BaseModel` (includes HasUuid trait)
    -   **Authenticatable models**: Extend `App\Models\Base\BaseUserModel` (includes HasUuid, HasFactory, Notifiable)
    -   Never extend `Illuminate\Database\Eloquent\Model` or `Illuminate\Foundation\Auth\User` directly
    -   Base models automatically include UUID generation and other common functionality
    -   Base models are located in `app/Models/Base/` directory
-   **BaseUserModel Features**: All authenticatable models extending `BaseUserModel` automatically include:
    -   **User Status Management**: `isActive()`, `activate()`, `deactivate()` methods
    -   **Login Tracking**: `updateLastLoginAt()` method (automatically called on login)
    -   **Query Scopes**: `scopeActive()`, `scopeInactive()` for filtering active/inactive users
    -   **User ID 1 Protection**: Automatic protection against deletion and unauthorized updates of user ID 1 (MySQL trigger support)
    -   **Active Status Field**: `is_active` boolean field (default: `true` for new users)
    -   **Last Login Tracking**: `last_login_at` timestamp field (automatically updated on login)
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
-   **Dual Authentication**: Supports both email and username login
    -   Users can authenticate using either their email address or username
    -   Login form uses `identifier` field which accepts both email and username
    -   `User::findByIdentifier()` method handles lookup by email or username
    -   **Middleware**: `App\Http\Middleware\Auth\MapLoginIdentifier` maps `identifier` to `email` for Fortify validation compatibility
    -   **Service Provider**: `FortifyServiceProvider` configured with custom authentication pipeline
-   **Active User Check**: Inactive users cannot log in - authentication automatically checks `isActive()` before allowing login
-   **Login Tracking**: `last_login_at` timestamp is automatically updated on successful login via `SyncUserPreferencesOnLogin` listener
-   **Environment-Based Login UI**:
    -   **Production**: Standard text input for identifier (email/username)
    -   **Development**: Dropdown select with all users for quick testing (password auto-filled)
-   **Team Context**: On successful login, user's `team_id` is automatically set in session for `TeamsPermission` middleware (via `setTeamSessionForUser()` helper)
-   **Rate Limiting**: Custom rate limiter supports both `identifier` and `email` fields for throttling (uses `getIdentifierFromRequest()` helper)
-   **Code Quality**: Uses centralized authentication helpers (`app/helpers/auth-helpers.php`) to avoid code duplication and improve maintainability

### Authorization & Permissions

-   **Package**: Spatie Permission (v6.23)
-   **User Model**: `App\Models\User` includes `HasRoles` trait
-   **UUID Support**: Configured to use `model_uuid` instead of `model_id` for UUID-based User models
-   **Teams Permissions**: Enabled by default (`'teams' => true` in config)
-   **Configuration**: `config/permission.php`
-   **Migration**: Modified to support UUIDs in pivot tables (`model_has_permissions`, `model_has_roles`)
-   **Middleware**: `App\Http\Middleware\Teams\TeamsPermission` - Sets team ID from session
-   **Middleware Priority**: Registered in `AppServiceProvider` to run before `SubstituteBindings`
-   **Documentation**: See `docs/spatie-permission/index.md` for complete rules, best practices, and guidelines
-   **Constants**: Always use `App\Constants\Permissions` and `App\Constants\Roles` - **NO HARDCODED STRINGS ALLOWED**
-   **Best Practice**: Always check for **permissions** (not roles) using `can()` and `@can` directives
-   **Team ID**: Set via `session(['team_id' => $team->id])` on login, accessed via `setPermissionsTeamId()`
-   **Important**: User model must NOT have `role`, `roles`, `permission`, or `permissions` properties/methods/relations
-   **Switching Teams**: Always call `$user->unsetRelation('roles')->unsetRelation('permissions')` before querying after switching teams
-   **Super Admin Pattern**: Implemented via `Gate::before()` in `AppServiceProvider::boot()` - Users with `Roles::SUPER_ADMIN` role automatically have all permissions granted. This allows using permission-based controls (`@can()`, `$user->can()`) throughout the app without checking for Super Admin status. The pattern follows Spatie Permissions best practices. **Important**: Direct calls to `hasPermissionTo()`, `hasAnyPermission()`, etc. bypass the Gate and won't get Super Admin access - always use `can()` methods instead.

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
-   **Component Comments**: **NO comments are allowed inside HTML tags or Blade directives** - All comments must be isolated at the top of the file or before the section they describe. Comments inside `@if`, `@foreach`, `@props`, HTML tags, or any directives are not allowed. Use isolated comment blocks (`{{-- --}}` or `<!-- -->`) at the top of the file or before the relevant section.
-   **Frontend Reactivity Rule**: **CRITICAL RULE** - All frontend reactivity (UI state and behavior) MUST be implemented using Alpine.js.
    -   **Blade is limited to structure and data injection only** - Blade directives (`@if`, `@foreach`, etc.) are for structural rendering and data injection, NOT for controlling UI behavior or reactivity.
    -   **Livewire is limited to server-side state and actions** - Livewire handles server-side state, data fetching, and actions. It MUST NOT be used for UI-only state (modals, dropdowns, toggles, etc.).
    -   **The agent MUST:**
        -   Use `x-data` for all interactive UI state
        -   Use `x-show`, `:class`, and Alpine events for visibility and styling
        -   Inject initial data using `@js()` helper
        -   Call Livewire actions from Alpine when needed (e.g., `$wire.methodName()`)
    -   **The agent MUST NOT:**
        -   Use Blade (`@if`, `@class`) to control UI behavior or reactivity
        -   Use Livewire to toggle UI state (modals, dropdowns, etc.)
        -   Entangle UI-only state with Livewire
        -   Mix Blade logic inside Alpine expressions
    -   **Violations risk Livewire 4 island hydration and MUST be rewritten.**
-   **@entangle Directive Rule**: **CRITICAL RULE** - The agent MUST NOT use Blade's `@entangle` directive.
    -   `@entangle` is legacy (Livewire v2) and causes DOM-removal and hydration issues in Livewire 4.
    -   When bidirectional state sync is required, the agent MUST use `$wire.$entangle('property')` inside Alpine `x-data`.
    -   UI-only state MUST remain Alpine-local and MUST NOT be entangled.
    -   Any use of `@entangle` is INVALID and must be rewritten.
-   **Alpine.js Preference**: **Always prefer Alpine.js over plain JavaScript when possible**
    -   Alpine.js is included with Livewire (no manual inclusion needed)
    -   **Documentation**: See `docs/alpinejs/index.md` for complete Alpine.js documentation, directives, magics, plugins, and usage examples
    -   **Reference the documentation**: When working with Alpine.js, always refer to `docs/alpinejs/index.md` for comprehensive information about directives, magics, lifecycle hooks, and best practices
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

