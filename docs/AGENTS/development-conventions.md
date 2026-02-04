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
    -   **Global Namespace for Built-in Functions**: All PHP built-in functions (e.g., `is_array`, `count`, `array_merge`, `in_array`, `json_encode`, `json_decode`, etc.) MUST be called in the global namespace by prefixing them with a backslash (`\`) when used inside a namespaced file. This improves performance by avoiding a namespace lookup.
        -   **✅ Correct**: `\count($items)`, `\json_encode($data)`
        -   **❌ Incorrect**: `count($items)`, `json_encode($data)`
-   **PHPDoc & Commenting**: **(MANDATORY)** All code must be well-documented.
    -   **PHPDoc**: Add comprehensive PHPDoc comments to all methods, functions, and classes. Include `@param`, `@return`, and `@throws` tags with types and descriptions.
    -   **Logic Explanation**: For any non-trivial logic, you **MUST** provide inline comments explaining *why* something is done, not just *what* is done. Explain weird edge cases, complex business logic, or specific design decisions.
    -   **Usages**: If a function has specific usage constraints or intended patterns, document them clearly in the PHPDoc description.
    -   **Prefer PHPDoc blocks** over inline comments for method/function headers.
-   **Auth**: **Never use the `auth()` helper**. Always use the `Illuminate\Support\Facades\Auth` facade (e.g. `Auth::check()`, `Auth::user()`, `Auth::id()`, `Auth::guard(...)`).
-   **Helper Functions**: **Do NOT use `function_exists()` checks in helper files** - Helper files are autoloaded via Composer and will only be loaded once, so function existence checks are unnecessary
-   **I18nService**: **Always use `I18nService` for locale-related code** - Do not directly access `config('i18n.*')` in helper functions or other code. Use `I18nService` methods to centralize all locale-related logic (`getSupportedLocales()`, `getDefaultLocale()`, `getValidLocale()`, `getLocaleMetadata()`, etc.)
-   **View Composers**: **Use View Composers instead of `@inject` for global data** - Register View Composers in service providers to share data globally with all views. This is more efficient and cleaner than using `@inject` directives in every template.
-   **Leading Import Slashes**: **NO leading import slashes are allowed in PHP or Blade files** - Avoid using leading slashes in `use` statements or inline class references (e.g., use `App\Models\User` instead of `\App\Models\User`). Always prefer importing classes at the top of the file. If a name collision occurs, use the `as` keyword with descriptive context (e.g., `use App\Models\User as AppUser`).
-   **Use Enums Whenever Possible**: Always prefer PHP **backed Enums** over class constants or raw strings for type-bound properties (status, type, color, etc.). This ensures type safety and enables better IDE support.
-   **Dedicated UI Helpers**: Use `alpineColorClasses()` (from `app/helpers/ui-helpers.php`) for all dynamic class bindings in UI components. This helper ensures compatibility with the Tailwind 4 scanner without needing a manual safelist.
 

 ### Exception Handling

 -   **Global Handler**: The application uses a robust global exception handling system.
 -   **No Try-Catch**: **Do NOT use `try-catch` blocks in controllers, services, or other application code.**
     -   Let exceptions propagate to the global handler.
     -   The global handler will manage logging, user notifications, and error pages.
 -   **Exceptions**: `try-catch` blocks are only permitted in:
     -   Infrastructure code where crashing the handler itself must be avoided (e.g., error reporting channels).
     -   Specific third-party integrations where the library requires it *and* specific recovery logic is implemented (not just logging).
     -   Testing code where verifying exception throwing is required.
     -   Validation logic where exception flow is used for control flow (though avoid this if possible).

### Single Responsibility Principle (SRP) — MANDATORY

⚠️ **CRITICAL RULE**: All functions MUST have exactly ONE responsibility. This rule is **mandatory** and applies to all code.

#### Function Validity Rules

A function is considered valid ONLY IF:
-   It performs a single, clearly describable action
-   It operates at ONE level of abstraction
-   Its name fully describes its behavior **WITHOUT using "and", "or", "then"**
-   It can be summarized in one short sentence

#### Function Size & Structure

-   **Functions MUST be small**
-   **Default target**: 1–10 lines
-   **Hard limit**: 20 lines (exceptions require explicit justification)
-   **Parameter Limit**: A function MUST NOT have more than **3 parameters**. If more parameters are required, they MUST be encapsulated into a dedicated **Data Object** or **DTO**.
-   Nested control structures are discouraged
-   **Early returns are preferred**

#### DTO Organization & Implementation

-   **Location**: Place DTOs in `app/Support/[Domain]/` (e.g., `app/Support/Users/UserData.php`, `app/Support/DataTable/QueryOptions.php`).
-   **Structure**: Use PHP 8.2+ `readonly` classes with constructor property promotion whenever possible.
-   **Factory Methods**: Add static `fromArray()` or `forCreation()` / `forUpdate()` methods to simplify DTO instantiation from request data or arrays.
-   **Type Safety**: Always use strict type hints for all DTO properties.

**Example (DTO):**
```php
namespace App\Support\Users;

readonly class UserData
{
    public function __construct(
        public array $attributes,
        public array $roleUuids = [],
        public array $teamUuids = [],
        public array $permissionUuids = [],
        public bool $sendActivation = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            attributes: $data['attributes'] ?? [],
            roleUuids: $data['roles'] ?? [],
            // ...
        );
    }
}
```

**Example (Usage):**
```php
// ✅ CORRECT (Using DTO)
public function createUser(UserData $data): User
{
    // Implementation using $data->attributes, $data->roleUuids, etc.
}

// ❌ INCORRECT (Too many parameters)
public function createUser(array $attributes, array $roles, array $teams, bool $sendActivation): User
{
    // ...
}
```

#### Refactoring Requirement (MANDATORY)

If a function:
-   Contains multiple logical steps
-   Mixes orchestration and implementation
-   Requires inline comments to explain steps
-   Uses loops + conditionals + side effects together

**THEN the function MUST be decomposed into smaller functions.** Each sub-function must follow the same rules recursively.

#### Level of Abstraction Rule

A function MUST NOT mix:
-   Business logic
-   Infrastructure concerns (DB, HTTP, FS, Cache)
-   Formatting or transformation logic

Each responsibility MUST be extracted into a dedicated function or service.

#### Controller / Entry-Point Rule

Controllers, Commands, and Jobs:
-   **MUST only orchestrate**
-   **MUST NOT contain business logic**
-   **MUST only call domain-level actions/services**
-   **Max 10 lines per method** in controllers

#### Naming Enforcement

**❌ Invalid function names** (too vague):
-   `processData`, `handleRequest`, `doStuff`, `manageUser`

**✅ Valid function names** (specific and descriptive):
-   `calculateInvoiceTotal`, `validateSubscriptionStatus`, `persistUserProfile`

#### Laravel-Specific Rules

-   **Controllers**: max 10 lines per method, no DB queries
-   **Services / Actions**: one public method = one use case
-   **Private methods**: must support exactly one public action
-   **No HTTP / Request usage** inside domain services

#### AI Self-Check (MANDATORY)

Before finalizing code, YOU MUST:
1.  Review every function
2.  Assert its single responsibility
3.  Split any function that violates this rule
4.  Prefer clarity over brevity
5.  Prefer decomposition over cleverness

**If unsure → SPLIT THE FUNCTION**

When modifying or reviewing existing code:
-   You MUST refactor any function that violates SRP
-   Even if not explicitly requested
-   **Refactoring is NOT optional**

 ### Internationalization (i18n)
 
 -   **Namespaces**: **Always use granular namespaces** (e.g., `pages.*`, `users.*`, `actions.*`) instead of the monolithic `ui.*` prefix.
 -   **Generic Pattern**: **Prefer generic keys for CRUD operations**. Use `pages.common.*` combined with `types.*` entities to avoid redundancy.
     -   *Correct*: `__('pages.common.create.title', ['type' => __('types.user')])`
     -   *Incorrect*: `__('users.create_new_user')` (unless highly specific)
 -   **Hardcoded Strings**: **No hardcoded user-facing strings**. Always use `__('namespace.key')`.
 -   **Sync**: Run `php artisan lang:sync` after adding new keys.
 -   **Bulk Actions**: **Do not include the word "selected" in bulk action translations** (e.g., use "Delete" instead of "Delete Selected"). The context of a bulk action automatically implies it applies to selected items.
 -   **Locale Definitions**: **Locales MUST be defined in `lang/xx_XX/locales.php`**.
     -   Create a `locales.php` file in each language directory (e.g., `lang/en_US/locales.php`).
     -   Keys must be the locale code (e.g., `'en_US'` => `'English (US)'`).
     -   **Format Rule**: Values MUST follow the format `[Language Name] ([Country Code])`. Examples:
         -   English: `'en_US' => 'English (US)'`, `'en_GB' => 'English (GB)'`
         -   French (in French locale): `'fr_FR' => 'Français (FR)'`, `'fr_CA' => 'Français (CA)'`
         -   Spanish (in French locale): `'es_ES' => 'Espagnol (ES)'`
     -   Always use `__('locales.en_US')` to display language names, never hardcode "English" or rely on config Native Name for user-facing UI.
     -   This ensures the language list itself is translatable (e.g., a French user sees "Anglais (US)").

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

##### Smart Pagination
- **Standard**: The DataTable pagination component now handles "smart" display (showing relevant page windows) internally.
- **Implementation**: Simply use `{{ $rows->links('components.datatable.pagination') }}`. No manual `onEachSide()` call is required.

#### Avoiding Duplication

-   **Extract repeated patterns** into helper functions, closures, or methods
-   **Use configuration arrays** and loops when configuring multiple similar items
-   **When adding new translation keys, you **MUST** add them to all supported language directories in the `lang/` folder (currently `en_US` and `fr_FR`).
-   Use the `trans_choice` function for pluralization.
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
-   **Interactive Component Disabled States**: **ALL interactive components (buttons, links) must have loading/disabled state handling** to prevent double-clicks and improve UX:
    -   **Livewire requests**: Use `data-loading:opacity-50 data-loading:pointer-events-none` classes (automatic Livewire 4 feature)
    -   **Blade forms**: The `x-ui.form` component automatically uses `submitForm` Alpine component which disables buttons via DOM manipulation
    -   **CSP Compliance**: All Alpine logic must be inside **registered components** (`Alpine.data()`). Never use inline `x-data="{ ... }"` with JavaScript, and never use `x-bind` with complex expressions. DOM manipulation inside `init()` is CSP-safe.
    -   **Example**:
        ```blade
        {{-- Livewire: data-loading handled automatically --}}
        <x-ui.button wire:click="save">Save</x-ui.button>
        
        {{-- Blade form: submitForm component disables buttons automatically --}}
        <x-ui.form action="/users" method="POST">
            <x-ui.button type="submit">Submit</x-ui.button>
        </x-ui.form>
        ```
-   **Component Documentation**: **ALWAYS update `docs/components/index.md` when adding new UI components** - This ensures all components are documented with props, usage examples, and implementation details
-   **Component Tag Format**: **ALL Blade and Livewire component tags MUST use opening and closing tags, never self-closing tags** - Always write `<x-component></x-component>` or `<livewire:component></livewire:component>` instead of `<x-component />` or `<livewire:component />`, even if the component has no content. **Exception**: Standard HTML self-closing tags (void elements) like `<img />`, `<br />`, `<hr />`, `<input />`, `<meta />`, `<link />`, `<area />`, `<base />`, `<col />`, `<embed />`, `<source />`, `<track />`, `<wbr />` should remain self-closing as per HTML5 specification.
-   **No Directives in Component Tags**: **NEVER use Blade directives (e.g., `@if`, `@foreach`, `@auth`) inside component opening tags.** This is a critical rule because it causes syntax errors in the Blade compiler. Instead, use conditional attribute binding.
    -   **❌ Incorrect**:
        ```blade
        <x-ui.button @if($href) href="{{ $href }}" @endif>Click</x-ui.button>
        ```
    -   **✅ Correct**:
        ```blade
        <x-ui.button :href="$href ?: null">Click</x-ui.button>
        ```
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
-   **Translations**: Always add translation keys to all supported languages (e.g., `en_US` and `fr_FR`) when introducing new keys. Do not leave keys missing or with placeholders in any language.
-   **Strict Typing**: Use `declare(strict_types=1);` in all PHP files.
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

### Component Attribute Forwarding & Alpine.js Scope (CRITICAL)

When creating wrapper components (like `x-ui.input` or `x-ui.password`) that include slots and Alpine.js state, ensure that **Alpine logic is placed on the outermost relevant container**.

**Pattern:**
Promote attributes like `x-data`, `x-init`, and `x-cloak` from the component's `$attributes` to the root container. This ensures that any elements inside slots (like `prepend`/`append` icons or buttons) share the same Alpine scope as the main input/textarea.

**Rule for forward-facing components:**
1.  **Split `$attributes`**: Divide attributes into "container attributes" (`x-data`, `x-init`, `x-cloak`, `x-show`, `x-transition`) and "input attributes" (`wire:model`, `name`, `id`, `x-ref`, etc.).
2.  **Container Binding**: Apply "container attributes" to the root `div` of the component.
3.  **Input Binding**: Apply "input attributes" to the actual `<input>` or `<textarea>`.
4.  **Rationale**: This prevents scope shadowing and ensures that `$refs` or shared state (like `showPassword` in a password toggle) are accessible to interactive elements within slots.

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
-   **Semantic lists**: `<ul>`, `<ol>`, `<li>`, `<dl>`, `dt`, `<dd>`
-   **Card structures**: DaisyUI's `.card`, `.card-body` classes (no component yet)
-   **Alerts**: DaisyUI's `.alert` classes (no component yet)

#### Rationale

1.  **Consistency**: Components ensure uniform styling across the app
2.  **Maintainability**: Style changes propagate automatically
3.  **Documentation**: Usage is self-documenting via component names
4.  **Refactoring**: Easy to update all instances at once

### Clean Blade Templates (MANDATORY)

> **CRITICAL RULE**: Blade templates MUST contain minimal logic. All conditional expressions, data transformations, and business logic MUST be in PHP methods/computed properties, NOT in Blade templates.

#### Requirements

-   **Blade is for structure only** - Templates should render data, not calculate or transform it
-   **PHP methods for logic** - All ternaries, conditionals, and data manipulation MUST be in Livewire computed properties or methods
-   **Single source of data** - Blade should receive clean, ready-to-display values from PHP
-   **Avoid inline ternaries** - No `{{ $condition ? 'value1' : 'value2' }}` in templates

#### Examples

**❌ Incorrect - Logic in Blade:**
```blade
<x-ui.title>
    {{ $isCreateMode 
        ? __('pages.common.create.title', ['type' => $isLayout ? __('types.layout') : __('types.content')])
        : __('pages.common.edit.title', ['type' => $isLayout ? __('types.layout') : __('types.content')]) 
    }}
</x-ui.title>

<x-ui.button href="{{ $isCreateMode ? route('items.index') : route('items.show', $model) }}">
    {{ $isCreateMode ? __('actions.create') : __('actions.save') }}
</x-ui.button>
```

**✅ Correct - Logic in PHP, clean Blade:**
```blade
<x-ui.title>{{ $this->pageTitle }}</x-ui.title>

<x-ui.button href="{{ $this->cancelUrl }}">
    {{ $this->submitButtonText }}
</x-ui.button>
```

```php
// In PHP block
public function getPageTitleProperty(): string
{
    $typeKey = $this->isLayout ? 'types.layout' : 'types.content';
    $actionKey = $this->isCreateMode ? 'pages.common.create.title' : 'pages.common.edit.title';
    return __($actionKey, ['type' => __($typeKey)]);
}

public function getCancelUrlProperty(): string
{
    return $this->isCreateMode
        ? route('items.index')
        : route('items.show', $this->model);
}

public function getSubmitButtonTextProperty(): string
{
    return $this->isCreateMode ? __('actions.create') : __('actions.save');
}
```

#### Benefits

1.  **Readability**: Templates are clean and easy to read
2.  **Testability**: Logic in PHP methods can be unit tested
3.  **Maintainability**: Business logic changes don't require touching templates
4.  **Reusability**: Computed properties can be reused across multiple templates
5.  **SRP**: Separates presentation (Blade) from logic (PHP)


-   **Web Routes**: Use `Route::livewire()` for interactive pages (preferred method in Livewire 4)
-   **Static Views**: Use `Route::view()` for simple pages
-   **Named Routes**: Always use named routes with `route()` helper
-   **Full-Page Components**: Use `pages::` namespace for components in `resources/views/pages/`
-   **Examples**:

    ```php
    // Full-page component (in pages/ directory)
    Route::livewire('settings/account', 'pages::settings.account')->name('settings.account');

    // Nested component (in components/ directory)
    <livewire:settings.two-factor.setup-modal />
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
-   **Pivot Models**: **ALL many-to-many relationships MUST use explicit pivot models**
    -   **Base class**: All pivot models must extend `App\Models\Base\BasePivotModel`
    -   **Location**: Pivot models are located in `app/Models/Pivots/` directory
    -   **Naming convention**: Use concatenated model names (e.g., `RoleUser`, `PermissionRole`, `TeamUser`)
    -   **Usage**: Use `->using(PivotModel::class)` on belongsToMany relationships
    -   **Available pivot models**:
        -   `RoleUser` - For `role_user` pivot table (users ↔ roles)
        -   `PermissionRole` - For `permission_role` pivot table (permissions ↔ roles)
        -   `PermissionUser` - For `permission_user` pivot table (permissions ↔ users, direct permissions)
        -   `TeamUser` - For `team_user` pivot table (teams ↔ users)
    -   **Example**:
        ```php
        use App\Models\Pivots\RoleUser;
        
        public function roles(): BelongsToMany
        {
            return $this->belongsToMany(Role::class)
                ->using(RoleUser::class);
        }
        ```
-   **UUID-Only Frontend Communication**: **CRITICAL RULE - Never expose integer IDs to the frontend**
    -   **ALL frontend communication MUST use UUIDs, never integer IDs**
    -   **Acceptable backend-only ID usage**:
        -   Database queries and relationships (e.g., foreign keys)
        -   Validation rules (e.g., `Rule::unique()->ignore($model->id)`)
        -   Internal server-side logic
        -   Pivot table relationships
    -   **FORBIDDEN ID exposure to frontend**:
        -   JSON responses or API endpoints
        -   JavaScript/Alpine.js data attributes
        -   Livewire wire:model bindings with IDs
        -   Hidden form inputs with IDs
        -   Query parameters in URLs
    -   **Why**: Security best practice - integer IDs expose system internals and enable enumeration attacks. UUIDs are opaque and unpredictable.
    -   **Implementation**:
        -   Use `$model->uuid` for all frontend references
        -   Use route model binding with `uuid` as route key
        -   Checkbox values should use UUIDs: `value="{{ $role->id }}"` → `value="{{ $role->uuid }}"`
        -   Wire:model id arrays should contain UUIDs, not integer IDs
    -   **Exception**: Session IDs and notification IDs from Laravel's built-in systems may use their default format
#### Self-Joins & Ambiguity

-   **Ambiguous Columns Rule**: **ALWAYS qualify columns with the table name when using joins.**
    -   When joining tables (especially self-joins), column names like `created_at`, `status`, or `type` become ambiguous.
    -   **Correct**: `where('users.created_at', ...)`
    -   **Incorrect**: `where('created_at', ...)`
-   **DataTable Filters**: Use `->fieldMapping('table_name.column')` in DataTable Filters to implicitly fix ambiguity without changing the filter key.
-   **Base Query**: In `baseQuery()`, always select `table_name.*` to ensure the primary model attributes are hydrated correctly and not overwritten by joined columns.

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
-   **RBAC Permission System**:
    -   **Single Source of Truth**: All permissions are defined in `App\Services\Auth\PermissionMatrix`
    -   **Dynamic Access (MANDATORY)**: Always use the `Permissions` class magic methods to access permissions
    -   **Example**: `Permissions::VIEW_USERS()` (Correct) vs `Permissions::VIEW_USERS` (Deprecated/Incorrect)
    -   **Constants**: Entity and Action constants are defined in `App\Constants\Auth\PermissionEntity` and `App\Constants\Auth\PermissionAction`
    -   **PHPDoc Generation**: Run `php artisan permissions:generate-phpdoc` after modifying the matrix to update IDE support
    -   **Documentation**: See `docs/AGENTS/rbac-system.md` for full details
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
-   **Script Colocation**: **ALWAYS colocate specific Alpine components with their Blade views**
    -   Use the `@assets` directive at the bottom of the Blade component file to define component-specific Alpine logic.
    -   This ensures specific scripts are loaded **on-demand** and **deduplicated** automatically by Livewire.
    -   **Pattern**: Use an IIFE with `alpine:init` registration check.
    -   **Documentation**: See `docs/AGENTS/colocated-scripts.md` for the complete pattern and examples.
    -   **Reference**: This replaces the old pattern of global `.js` files in `resources/js/alpine/data/`.

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

