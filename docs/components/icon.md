## Icon

**Location:** `resources/views/components/ui/icon.blade.php`

**Component Name:** `<x-ui.icon>`

### Description

A dynamic icon component that provides secure, flexible icon rendering using Blade Icons. Supports multiple icon packs (heroicons, fontawesome, bootstrap, feather) with automatic fallback handling and comprehensive security validation.

### Props

| Prop    | Type           | Default      | Description                                                                                                                            |
| ------- | -------------- | ------------ | -------------------------------------------------------------------------------------------------------------------------------------- |
| `name`  | `string`       | **Required** | Icon name (e.g., 'home', 'user', 'settings'). Only alphanumeric characters, dashes, and underscores are allowed.                       |
| `pack`  | `string\|null` | `null`       | Icon pack name: `heroicons` (default), `heroicons-solid`, `fontawesome`, `bootstrap`, `feather`. Falls back to `heroicons` if invalid. |
| `size`  | `string\|null` | `null`       | Predefined size: `xs`, `sm`, `md`, `lg`, `xl`, or custom Tailwind class (e.g., `w-6 h-6`). Defaults to `w-6 h-6` if not provided.      |
| `class` | `string`       | `''`         | Additional CSS classes. Valid CSS class characters only (alphanumeric, dash, underscore, space, dot).                                  |

### Security Features

-   **Input Validation**: Icon names are sanitized to only allow alphanumeric characters, dashes, and underscores
-   **Pack Validation**: Pack names are validated against supported packs (falls back to 'heroicons' if invalid)
-   **Class Sanitization**: CSS class attributes are sanitized to prevent XSS attacks
-   **SVG Sanitization**: Blade Icons handles SVG content sanitization internally
-   **Fallback Handling**: Automatically falls back to a question mark icon if the requested icon doesn't exist

### Usage Examples

#### Basic Icon

```blade
<x-ui.icon name="home" />
```

#### Icon with Size

```blade
<x-ui.icon name="user" size="md" />
```

#### Icon with Custom Class

```blade
<x-ui.icon name="settings" class="h-5 w-5 text-primary" />
```

#### Icon Sizes

```blade
<x-ui.icon name="home" size="xs" /> {{-- w-4 h-4 --}}
<x-ui.icon name="home" size="sm" /> {{-- w-5 h-5 --}}
<x-ui.icon name="home" size="md" /> {{-- w-6 h-6 --}}
<x-ui.icon name="home" size="lg" /> {{-- w-8 h-8 --}}
<x-ui.icon name="home" size="xl" /> {{-- w-10 h-10 --}}
```

#### Custom Size

```blade
<x-ui.icon name="star" class="w-12 h-12" />
```

#### Different Icon Packs

```blade
{{-- Heroicons (default) --}}
<x-ui.icon name="home" />

{{-- Heroicons Solid --}}
<x-ui.icon name="home" pack="heroicons-solid" />

{{-- FontAwesome --}}
<x-ui.icon name="star" pack="fontawesome" size="lg" />

{{-- Bootstrap Icons --}}
<x-ui.icon name="gear" pack="bootstrap" />

{{-- Feather Icons --}}
<x-ui.icon name="user" pack="feather" />
```

#### Icon in Button

```blade
<x-ui.button variant="primary">
    <x-ui.icon name="plus" size="sm" />
    Add Item
</x-ui.button>
```

#### Icon in Navigation

```blade
<a href="{{ route('dashboard') }}" wire:navigate>
    <x-ui.icon name="home" class="h-5 w-5" />
    Dashboard
</a>
```

### Icon Search Rules

When searching for an icon name, follow these steps:

1. **First, fetch and parse:**

    ```
    https://raw.githubusercontent.com/iconify/icon-sets/master/collections.json
    ```

    to identify the correct `collection_id`.

2. **Then, fetch and parse:**
    ```
    https://raw.githubusercontent.com/iconify/icon-sets/master/json/{collection_id}.json
    ```
    to search for the requested icon.

**Rules:**

-   Use ONLY the above references.
-   Never guess icon names or collection IDs.
-   Search ONLY inside the `icons` object keys.
-   Matching must be case-insensitive.
-   Prefer exact matches, then partial matches.
-   If the icon does not exist in the reference, say it does not exist.
-   Do NOT rely on training data or prior knowledge for icon names.

### Implementation Details

-   Uses Blade Icons for icon rendering (supports multiple icon packs)
-   Uses `@inject` directive to inject `IconPackMapper` service (no Livewire overhead)
-   Provides consistent sizing through predefined size classes
-   Supports custom sizes via Tailwind classes
-   Automatically handles icon pack name mapping (e.g., 'home' → 'heroicon-o-home')
-   Includes comprehensive error handling with fallback icons
-   All input is validated and sanitized for security

---

## History

### Dynamic Icon System (2025-01-XX)
- **Refactor**: Converted the icon component from a static SVG wrapper to a dynamic system powered by Blade Icons.
- **Multi-Pack Support**: Added built-in support for Heroicons, FontAwesome, Bootstrap, and Feather icons.
- **Security**: Implemented strict input validation and sanitization for icon names and CSS classes to prevent XSS.

