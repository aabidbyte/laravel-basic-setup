# GEMINI.md - Context & Operational Directives

> **Rule Zero:** Rules guide my behavior. Documentation is the source of truth. I never assume; I always verify.

## 🛸 CORE OPERATIONAL DIRECTIVES

1.  **Dual-Stage Context Acquisition**: I must scan global rules (`AGENTS.md`, `.gemini/GEMINI.md`) then task-specific docs (`docs/**`) before any implementation.
2.  **Convention Match**: Every line of code I write must align with the architecture and logic found in project documentation.
3.  **🔴 Zero-Error Guarantee**: I am responsible for ensuring no syntax errors, broken references, or logical bugs are left unattended.
4.  **🧪 Regression Testing**: I must always execute the test suite (Pest) after modifications.
5.  **The "Never Repeat" Rule**: I will update documentation immediately if a mistake is corrected or a new pattern is established.

## 1. Project Overview

**Name:** Laravel Basic Setup
**Description:** A comprehensive Laravel 12 starter kit featuring Livewire 4, UUID-based models, Fortify authentication, advanced monitoring (Telescope, Horizon, Log Viewer), and real-time capabilities via Reverb.
**Architecture:** Monolithic Laravel application with server-side rendering via Livewire.

## 2. Technology Stack

*   **Backend:** PHP 8.4+, Laravel 12.0
*   **Frontend:** Livewire 4.0 (SFC), Tailwind CSS 4.x, DaisyUI 5.x, Alpine.js (CSP-safe)
*   **Database:** Supports MySQL/PostgreSQL/SQLite (uses UUIDs for primary keys)
*   **Real-time:** Laravel Reverb (WebSocket)
*   **Authentication:** Laravel Fortify (Backend), Sanctum (API), Spatie Permission (RBAC)
*   **Dev Tools:** Vite 7.x, Pest (Testing), Pint (Formatting), Laravel Boost

## 3. Operational Directives (CRITICAL)

### Code & Style
*   **UUIDs:** All models **must** extend `App\Models\Base\BaseModel` or `App\Models\Base\BaseUserModel` to automatically handle UUID generation.
*   **Enums:** Use **backed Enums** instead of class constants for status/types. Located in `app/Enums/`.
*   **Translations:** **Mandatory** for all user-facing text. Add keys to both `en_US` and `fr_FR` in `lang/`.
*   **Imports:** No leading slashes in `use` statements (e.g., `use App\Models\User;` not `use \App\Models\User;`).
*   **Global Namespace for Built-in Functions:** Prefix all PHP built-in functions with `\` when inside a namespaced file (e.g., `\count()`, `\is_array()`).
*   **Parameter Limit & DTOs:** A function MUST NOT have more than **3 parameters**. Use dedicated **DTO classes** (placed in `app/Support/[Domain]/`) for multiple parameters.

### Frontend (Blade/Livewire/Alpine)
*   **CSP Safety:** All Alpine.js logic must be extracted to registered components. **Avoid inline event handlers** that violate CSP.
*   **JSON in x-data:** When passing JSON to `x-data`, use **Single Quotes** for the HTML attribute and **Double Quotes** for the inner JSON string.
    *   **Right:** `x-data='{!! json_encode(["key" => "val"]) !!}'` or `x-data='myComponent({!! json_encode(...) !!})'`
    *   **Wrong:** `x-data="{!! json_encode(...) !!}"` (Breaks on inner double quotes, causes "Unexpected Token: EOF")
    *   Ensure any single quotes in data are escaped (`JSON_HEX_APOS`) so they don't break the outer attribute.
*   **Livewire Routing:** Use the **singular model name** for route parameters to enable automatic binding (e.g., `/users/{user}`), never `{id}` or `{uuid}`.
*   **Blade Components:** Do **not** use Blade directives (like `@if`) inside component opening tags. Use conditional attribute binding instead (`:attr="$condition ? 'val' : null"`).
*   **Layouts:** Full-page Livewire components are automatically wrapped in the layout. **Do not** manually wrap them in `<x-layouts.app>`.
*   **Alpine Component Nesting:** Avoid using generic variable names (like `open`) in both parent and child components (e.g., `Select` and `Sheet`). This causes scope shadowing that breaks `x-model`. Use distinct names (`selectOpen` vs `open`).
*   **Livewire Entangle:** When entangling nested array keys (`$wire.entangle('filters.role')`), the key MUST exist in the backend array initialization (e.g., in `mount()`). Use `mountHasDatatableLivewireFilters` for DataTables.

### Testing
*   **Framework:** Pest (v4)
*   **Location:** `tests/Feature` and `tests/Unit`
*   **Command:** `php artisan test --parallel` or `composer run test`
**When to Run Tests:**
- After **major updates** or **big implementations**
- After **significant architectural changes**
- When **explicitly requested** by the user
- Before committing breaking changes
- **Not** required for minor tweaks, styling changes, or small bug fixes

## 4. Build & Run Commands

### Setup
```bash
composer run setup
# OR manual:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Development
```bash
# Runs Server, Horizon, Pail, Vite, and Reverb concurrently
composer run dev
```

### Testing & Quality
```bash
# Run Tests
php artisan test --parallel

# Format Code (PHP)
composer run pint

# Format Code (Blade/JS)
npm run format:all
```

## 5. File Structure Highlights
*   `app/Models/Base/`: Base classes for models (UUID logic).
*   `app/Livewire/`: Livewire components (Standard & SFC).
*   `app/Actions/Fortify/`: Authentication logic.
*   `docs/AGENTS/`: Detailed agent-specific documentation.
*   `lang/`: Localization files (Strictly enforced).

## 6. Agent Behavior
*   **Reference:** Always consult `docs/AGENTS/` for specific patterns before implementation.
*   **Safety:** Do not assume "standard" Laravel conventions if a specific base class or trait is provided (e.g., `BaseModel`).
*   **Verification:** Always run `php artisan test --parallel` after changes.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.8
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/horizon (HORIZON) - v5
- laravel/prompts (PROMPTS) - v0
- laravel/reverb (REVERB) - v1
- laravel/sanctum (SANCTUM) - v4
- laravel/telescope (TELESCOPE) - v5
- livewire/livewire (LIVEWIRE) - v4
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4
- laravel-echo (ECHO) - v2
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `livewire-development` — Develops reactive Livewire 4 components. Activates when creating, updating, or modifying Livewire components; working with wire:model, wire:click, wire:loading, or any wire: directives; adding real-time updates, loading states, or reactivity; debugging component behavior; writing Livewire tests; or when the user mentions Livewire, component, counter, or reactive UI.
- `pest-testing` — Tests applications using the Pest 4 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, browser testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.
- `developing-with-fortify` — Laravel Fortify headless authentication backend development. Activate when implementing authentication features including login, registration, password reset, email verification, two-factor authentication (2FA/TOTP), profile updates, headless auth, authentication scaffolding, or auth guards in Laravel applications.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces using only PHP — no JavaScript required.
- Instead of writing frontend code in JavaScript frameworks, you use Alpine.js to build the UI when client-side interactions are required.
- State lives on the server; the UI reflects it. Validate and authorize in actions (they're like HTTP requests).
- IMPORTANT: Activate `livewire-development` every time you're working with Livewire-related tasks.

=== pint/core rules ===

# Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

=== laravel/fortify rules ===

# Laravel Fortify

- Fortify is a headless authentication backend that provides authentication routes and controllers for Laravel applications.
- IMPORTANT: Always use the `search-docs` tool for detailed Laravel Fortify patterns and documentation.
- IMPORTANT: Activate `developing-with-fortify` skill when working with Fortify authentication features.
</laravel-boost-guidelines>
