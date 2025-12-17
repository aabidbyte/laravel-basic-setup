# Dynamic Icon System

This application uses a dynamic icon system built with Blade Icons. Icons are rendered server-side using the `<x-ui.icon>` Blade component, providing secure, flexible icon rendering with multiple icon pack support.

## Installation

The following icon packages are installed:

-   `blade-ui-kit/blade-heroicons` - Heroicons (outline and solid)
-   `owenvoke/blade-fontawesome` - Font Awesome icons
-   `davidhsianturi/blade-bootstrap-icons` - Bootstrap Icons
-   `brunocfalcao/blade-feather-icons` - Feather Icons

## Icon Pack Mapping

The `IconPackMapper` service maps pack names to Blade Icons component names:

-   `heroicons` → `heroicon-o-{name}` (outline)
-   `heroicons-solid` → `heroicon-s-{name}` (solid)
-   `fontawesome` → `fas-{name}`
-   `bootstrap` → `bi-{name}`
-   `feather` → `feather-{name}`

## Usage

### Basic Usage

Specify an icon by name (heroicons is the default pack):

```blade
<x-ui.icon name="user" />
```

### With Size

Use predefined sizes or custom Tailwind classes:

```blade
<x-ui.icon name="user" size="md" />
<x-ui.icon name="user" class="w-8 h-8" />
```

### With Different Pack

Specify a different icon pack:

```blade
<x-ui.icon name="user" pack="heroicons-solid" />
<x-ui.icon name="star" pack="fontawesome" size="lg" />
```

### With Custom Classes

```blade
<x-ui.icon
    name="user-circle"
    pack="heroicons"
    class="w-10 h-10 text-primary"
/>
```

### In Loops

```blade
@foreach ($items as $item)
    <x-ui.icon
        name="{{ $item->icon_name }}"
        pack="{{ $item->icon_pack ?? 'heroicons' }}"
        class="w-5 h-5"
    />
@endforeach
```

## Component

### Icon Component (`<x-ui.icon>`)

A Blade component that renders icons using Blade Icons with comprehensive security validation.

**Location:** `resources/views/components/ui/icon.blade.php`

**Props:**

-   `name` (string, required): Icon name (e.g., 'home', 'user', 'settings'). Only alphanumeric characters, dashes, and underscores are allowed.
-   `pack` (string, optional): Icon pack name (default: 'heroicons'). Options: `heroicons`, `heroicons-solid`, `fontawesome`, `bootstrap`, `feather`
-   `size` (string, optional): Predefined size: `xs`, `sm`, `md`, `lg`, `xl`, or custom Tailwind class
-   `class` (string, optional): Additional CSS classes

**Features:**

-   Automatic fallback to question-mark icon if requested icon doesn't exist
-   Comprehensive input validation and sanitization for security
-   Supports all installed icon packs
-   Server-side rendering (no Livewire overhead)
-   Uses `@inject` directive for dependency injection

**Security:**

-   Icon names are sanitized to only allow alphanumeric characters, dashes, and underscores
-   Pack names are validated against supported packs (falls back to 'heroicons' if invalid)
-   CSS class attributes are sanitized to prevent XSS attacks
-   Blade Icons handles SVG content sanitization internally

## Performance

-   **Server-Side Rendering**: Icons are rendered server-side using Blade Icons (no client-side JavaScript)
-   **Inlined SVGs**: All icons are inlined SVGs (no HTTP requests)
-   **No Livewire Overhead**: Uses Blade component with `@inject` directive (no reactivity needed)
-   **Efficient**: Icons are resolved at render time based on pack and name

## Examples

### Example 1: User Profile Icon

```blade
<x-ui.icon
    name="user-circle"
    class="w-10 h-10 text-primary"
/>
```

### Example 2: Navigation Icons

```blade
<nav class="flex gap-4">
    <x-ui.icon name="home" size="md" />
    <x-ui.icon name="user" size="md" />
    <x-ui.icon name="settings" size="md" />
</nav>
```

### Example 3: Different Icon Packs

```blade
<!-- Heroicons Outline (default, pack can be omitted) -->
<x-ui.icon name="user" size="md" />

<!-- Heroicons Solid -->
<x-ui.icon pack="heroicons-solid" name="user" size="md" />

<!-- Font Awesome -->
<x-ui.icon pack="fontawesome" name="user" size="lg" />

<!-- Bootstrap Icons -->
<x-ui.icon pack="bootstrap" name="person" size="md" />

<!-- Feather Icons -->
<x-ui.icon pack="feather" name="user" size="md" />
```

### Example 4: Icon in Button

```blade
<x-ui.button variant="primary">
    <x-ui.icon name="plus" size="sm" />
    Add Item
</x-ui.button>
```

### Example 5: Icon with Locale Metadata

```blade
{{-- Using locale metadata from View Composer --}}
<x-ui.icon
    name="{{ $localeMetadata['icon']['name'] ?? 'globe-alt' }}"
    pack="{{ $localeMetadata['icon']['pack'] ?? 'heroicons' }}"
    class="h-5 w-5"
/>
```

## Migration from Livewire Component

The icon system was migrated from a Livewire component (`livewire:dynamic-icon-island`) to a Blade component (`<x-ui.icon>`) for better performance:

**Before:**

```blade
<livewire:dynamic-icon-island name="user" class="w-6 h-6" />
```

**After:**

```blade
<x-ui.icon name="user" size="md" />
```

**Benefits:**

-   No Livewire overhead (server-side rendering only)
-   Better performance (no reactivity needed for static icons)
-   Simpler API (Blade component instead of Livewire component)
-   Same security and validation features
