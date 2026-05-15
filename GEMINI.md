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

- **Backend:** PHP 8.4+, Laravel 12.0
- **Frontend:** Livewire 4.0 (SFC), Tailwind CSS 4.x, DaisyUI 5.x, Alpine.js (CSP-safe)
- **Database:** Supports MySQL/PostgreSQL/SQLite (uses UUIDs for primary keys)
- **Real-time:** Laravel Reverb (WebSocket)
- **Authentication:** Laravel Fortify (Backend), Sanctum (API), Spatie Permission (RBAC)
- **Dev Tools:** Vite 7.x, Pest (Testing), Pint (Formatting), Laravel Boost

## 3. Operational Directives (CRITICAL)

### Code & Style

- **UUIDs:** All models **must** extend `App\Models\Base\BaseModel` or `App\Models\Base\BaseUserModel` to automatically handle UUID generation.
- **Enums:** Use **backed Enums** instead of class constants for status/types. Located in `app/Enums/`.
- **Translations:** **Mandatory** for all user-facing text. Add keys to both `en_US` and `fr_FR` in `lang/`.
- **Imports:** No leading slashes in `use` statements (e.g., `use App\Models\User;` not `use \App\Models\User;`).
- **Global Namespace for Built-in Functions:** Prefix all PHP built-in functions with `\` when inside a namespaced file (e.g., `\count()`, `\is_array()`).
- **Parameter Limit & DTOs:** A function MUST NOT have more than **3 parameters**. Use dedicated **DTO classes** (placed in `app/Support/[Domain]/`) for multiple parameters.

### Frontend (Blade/Livewire/Alpine)

- **CSP Safety:** All Alpine.js logic must be extracted to registered components. **Avoid inline event handlers** that violate CSP.
- **JSON in x-data:** When passing JSON to `x-data`, use **Single Quotes** for the HTML attribute and **Double Quotes** for the inner JSON string.
    - **Right:** `x-data='{!! json_encode(["key" => "val"]) !!}'` or `x-data='myComponent({!! json_encode(...) !!})'`
    - **Wrong:** `x-data="{!! json_encode(...) !!}"` (Breaks on inner double quotes, causes "Unexpected Token: EOF")
    - Ensure any single quotes in data are escaped (`JSON_HEX_APOS`) so they don't break the outer attribute.
- **Livewire Routing:** Use the **singular model name** for route parameters to enable automatic binding (e.g., `/users/{user}`), never `{id}` or `{uuid}`.
- **Blade Components:** Do **not** use Blade directives (like `@if`) inside component opening tags. Use conditional attribute binding instead (`:attr="$condition ? 'val' : null"`).
- **Layouts:** Full-page Livewire components are automatically wrapped in the layout. **Do not** manually wrap them in `<x-layouts.app>`.
- **Alpine Component Nesting:** Avoid using generic variable names (like `open`) in both parent and child components (e.g., `Select` and `Sheet`). This causes scope shadowing that breaks `x-model`. Use distinct names (`selectOpen` vs `open`).
- **Livewire Entangle:** When entangling nested array keys (`$wire.entangle('filters.role')`), the key MUST exist in the backend array initialization (e.g., in `mount()`). Use `mountHasDatatableLivewireFilters` for DataTables.

### Testing

- **Framework:** Pest (v4) MUST BE USED EXCLUSIVELY.
    - **ALL tests MUST use Pest's functional API** (`it()` or `test()`).
    - **Class-based tests extending `TestCase` are STRICTLY FORBIDDEN.**
- **Location:** `tests/Feature` and `tests/Unit`
- **Command:** `php artisan test --parallel` or `composer run test`
  **When to Run Tests:**

* After **major updates** or **big implementations**
* After **significant architectural changes**
* When **explicitly requested** by the user
* Before committing breaking changes
* **Not** required for minor tweaks, styling changes, or small bug fixes

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

- `app/Models/Base/`: Base classes for models (UUID logic).
- `app/Livewire/`: Livewire components (Standard & SFC).
- `app/Actions/Fortify/`: Authentication logic.
- `docs/AGENTS/`: Detailed agent-specific documentation.
- `lang/`: Localization files (Strictly enforced).

## 6. Agent Behavior

- **Reference:** Always consult `docs/AGENTS/` for specific patterns before implementation.
- **Safety:** Do not assume "standard" Laravel conventions if a specific base class or trait is provided (e.g., `BaseModel`).
- **Verification:** Always run `php artisan test --parallel` after changes.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/horizon (HORIZON) - v5
- laravel/prompts (PROMPTS) - v0
- laravel/reverb (REVERB) - v1
- laravel/sanctum (SANCTUM) - v4
- laravel/telescope (TELESCOPE) - v5
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4
- laravel-echo (ECHO) - v2
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

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

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
    - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

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
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== volt/core rules ===

# Livewire Volt

- Single-file Livewire components: PHP logic and Blade templates in one file.
- Always check existing Volt components to determine functional vs class-based style.
- IMPORTANT: Always use `search-docs` tool for version-specific Volt documentation and updated code examples.
- IMPORTANT: Activate `volt-development` every time you're working with a Volt or single-file component-related task.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== spatie/guidelines-skills rules ===

# Project Coding Guidelines

- This codebase follows Spatie's coding guidelines.
- Always activate the `spatie-laravel-php` skill when writing, editing, reviewing, or formatting Laravel or PHP code.
- Always activate the `spatie-javascript` skill when writing, editing, reviewing, or formatting JavaScript or TypeScript code.
- Always activate the `spatie-version-control` skill when creating commits, branches, or managing Git operations.
- Always activate the `spatie-security` skill when configuring security, reviewing authentication, or setting up servers and databases.

</laravel-boost-guidelines>
