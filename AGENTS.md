# Agent Documentation

> **Note**: This file has been moved to `docs/AGENTS/index.md` for better organization and maintainability.

The full documentation is now organized into sections for easier navigation and faster indexing. Each major section is in its own file under `docs/AGENTS/`.

## ⚠️ Critical Rules (Always Check First)

### CSP-Safe Alpine.js (MANDATORY)
**All Alpine.js components with methods/functions MUST be extracted to registered components.**

See [CSP Safety Guide](docs/AGENTS/csp-safety.md) and [Important Patterns](docs/AGENTS/important-patterns.md#csp-safe-alpinejs-development-critical) for full CSP documentation.

### Colocated Scripts (MANDATORY)
**Component-specific JavaScript logic MUST be colocated with the Blade component using the `@assets` directive.** Do not create new global JS files for single components.

See [Colocated Scripts Pattern](docs/AGENTS/colocated-scripts.md) for details.
 
### No Blade Directives in Component Tags (CRITICAL)
**NEVER use Blade directives (e.g., `@if`, `@foreach`) inside component opening tags.** This causes syntax errors in the Blade compiler. Use conditional attribute binding (`:attr="$val ?: null"`) instead.

See [Development Conventions](docs/AGENTS/development-conventions.md#no-directives-in-component-tags) for details.

### No Leading Import Slashes (PHP/Blade)
**Avoid leading slashes (`\`) in `use` statements and class references.** Use full namespaces in `use` statements and short names in the code.

```php
// ❌ FORBIDDEN
use \App\Models\User;
catch (\Exception $e)

// ✅ REQUIRED
use App\Models\User;
catch (Exception $e)
```

See [Development Conventions](docs/AGENTS/development-conventions.md#no-leading-import-slashes) for details.

### Global Namespace for Built-in Functions
**All PHP built-in functions MUST be called in the global namespace (prefix with `\`) when inside a namespaced file.**

```php
// ❌ FORBIDDEN
if (is_array($data)) {
    return count($data);
}

// ✅ REQUIRED
if (\is_array($data)) {
    return \count($data);
}
```

See [Development Conventions](docs/AGENTS/development-conventions.md#global-namespace-for-built-in-functions) for details.

### Mandatory Translations
**When adding new translation keys, you MUST add them to all supported language directories in the `lang/` folder (currently `en_US` and `fr_FR`).** Never leave keys missing or with placeholders in any language.

**Locale Display Rule**: Always use `lang/xx_XX/locales.php` to define and display language names (e.g., `__('locales.en_US')`). Do NOT hardcode "English" or rely on config Native Name for user-facing UI.

**Locale Translation Format**: When translating locale codes (en_US, fr_FR, es_ES, etc.) in `locales.php`, always use the format: `[Language Name] ([Country Code])`. Examples:
 -   `en_US` => "English (US)"
 -   `fr_FR` => "Français (FR)" 
 -   `fr_CA` => "Français (CA)"
 -   `es_ES` => "Espagnol (ES)" (in French locale)

This ensures consistency and clarity across all locale displays.

See [Development Conventions](docs/AGENTS/development-conventions.md#translations) for details.

### Enums Over Constants (CRITICAL)
**Always use PHP enums instead of class constants for fixed value sets.** Enums provide type safety, Laravel integration, and automatic translation resolution.

**Examples:**
- ✅ `EmailTemplateStatus::DRAFT` (backed enum)
- ❌ `EmailTemplateConstants::STATUS_DRAFT` (class constant)

**When creating new value sets:**
1. Create a backed enum in `app/Enums/[Domain]/` (e.g., `app/Enums/EmailTemplate/`)
2. Add `color()` method for badge colors (if applicable)
3. Add `label()` method for translations
4. Add enum cast to model
5. Add resolver to `config/translation-resolvers.php`
6. Use `Enum` validation rule in forms

See [Development Conventions](docs/AGENTS/development-conventions.md#enum-usage) for detailed enum patterns.

### Livewire Route Model Binding (CRITICAL)
**Always use the model name (singular, lowercase) as the route parameter for Livewire routes**, not `{uuid}` or `{id}`.

**Examples:**
- ✅ `Route::livewire('/users/{user}', ...)` with `mount(User $user)`
- ✅ `Route::livewire('/{template}/edit', ...)` with `mount(EmailTemplate $template)`
- ❌ `Route::livewire('/{uuid}', ...)` - breaks automatic binding

**Why:** Livewire's automatic route model binding only works when the parameter name matches the model name. This enables type-hinted mount methods and automatic UUID resolution.

See [Livewire Route Model Binding](docs/AGENTS/livewire-route-model-binding.md) for complete documentation.

### No Layout Wrapper (Livewire Components Only)
**Do NOT wrap full-page Livewire components in `<x-layouts.app>`.**
Configuration `config/livewire.php` sets `'component_layout' => 'layouts::app'`, so Livewire wraps them automatically.

**Exceptions (Standard Blade)**:
Standard Blade views (controlled by Controllers/Routes returning `view()`) **MUST** still include `<x-layouts.app>`.

**Example (Livewire Component):**
```blade
{{-- ❌ WRONG --}}
<x-layouts.app>
    <x-layouts.page>...</x-layouts.page>
</x-layouts.app>

{{-- ✅ CORRECT --}}
<x-layouts.page>...</x-layouts.page>
```

## Quick Links


- **[Full Documentation](docs/AGENTS/index.md)** - Complete agent documentation with table of contents
- **[Project Overview](docs/AGENTS/project-overview.md)**
- **[Development Conventions](docs/AGENTS/development-conventions.md)**
- **[Common Tasks](docs/AGENTS/common-tasks.md)**
- **[Important Patterns](docs/AGENTS/important-patterns.md)**
- **[Unified Create/Edit Pattern](docs/AGENTS/unified-create-edit-pattern.md)** - Refactoring pattern for consolidating create/edit workflows
- **[Page Layout Title Pattern](docs/AGENTS/page-layout-title-pattern.md)** - Automatic title handling via layouts

For the complete documentation, please see [docs/AGENTS/index.md](docs/AGENTS/index.md).
