## Dropdown

**Location:** `resources/views/components/ui/dropdown.blade.php`

**Component Name:** `<x-ui.dropdown>`

### Description

A centralized, flexible dropdown component that provides consistent dropdown functionality across the application. Uses CSS focus pattern by default for better accessibility and keyboard navigation. Supports multiple placement options, menu styling, hover behavior, and custom content.

### Props

| Prop           | Type     | Default         | Description                                                                    |
| -------------- | -------- | --------------- | ------------------------------------------------------------------------------ |
| `placement`    | `string` | `'end'`         | Dropdown placement: `start`, `center`, `end`, `top`, `bottom`, `left`, `right` |
| `hover`        | `bool`   | `false`         | Enable hover to open dropdown (adds `dropdown-hover` class)                    |
| `contentClass` | `string` | `''`            | Additional CSS classes for dropdown content                                    |
| `bgClass`      | `string` | `'bg-base-100'` | Background color class for dropdown content (default: bg-base-100)             |
| `menu`         | `bool`   | `false`         | Enable menu styling (adds `menu` class to dropdown content)                    |
| `menuSize`     | `string` | `'md'`          | Menu size: `xs`, `sm`, `md`, `lg`, `xl` (only applies when `menu="true"`)      |

### Slots

-   **`trigger` slot (required):** The element that triggers the dropdown (button, div, avatar, etc.)
-   **Default slot:** The dropdown content (menu items, custom content, etc.)

### Usage Examples

#### Basic Dropdown with Custom Content

```blade
<x-ui.dropdown>
    <x-slot:trigger>
        <button class="btn">Click me</button>
    </x-slot:trigger>

    <div class="p-4">
        Custom content here
    </div>
</x-ui.dropdown>
```

#### Menu Dropdown

```blade
<x-ui.dropdown placement="end" menu menuSize="sm">
    <x-slot:trigger>
        <div class="btn btn-ghost">Menu</div>
    </x-slot:trigger>

    <a>Item 1</a>
    <a>Item 2</a>
    <a>Item 3</a>
</x-ui.dropdown>
```

#### Dropdown with Custom Styling

```blade
<x-ui.dropdown placement="end" menu
    contentClass="rounded-box z-[1] w-48 p-2 shadow-lg border border-base-300">
    <x-slot:trigger>
        <button class="btn btn-ghost btn-sm">
            <x-ui.icon name="globe-alt" />
        </button>
    </x-slot:trigger>

    <form method="POST" action="{{ route('preferences.locale') }}">
        @csrf
        <input type="hidden" name="locale" value="en_US">
        <button type="submit" class="btn btn-ghost btn-sm justify-start w-full">
            English
        </button>
    </form>
</x-ui.dropdown>
```

#### Hover Dropdown

```blade
<x-ui.dropdown hover>
    <x-slot:trigger>
        <button class="btn">Hover me</button>
    </x-slot:trigger>

    <div>Content appears on hover</div>
</x-ui.dropdown>
```

#### Dropdown with Different Placements

```blade
{{-- Dropdown on the left --}}
<x-ui.dropdown placement="start" menu>
    <x-slot:trigger>
        <button class="btn">Left</button>
    </x-slot:trigger>
    <a>Item</a>
</x-ui.dropdown>

{{-- Dropdown on the top --}}
<x-ui.dropdown placement="top" menu>
    <x-slot:trigger>
        <button class="btn">Top</button>
    </x-slot:trigger>
    <a>Item</a>
</x-ui.dropdown>
```

#### Dropdown with Avatar Trigger

```blade
<x-ui.dropdown placement="end" menu menuSize="sm">
    <x-slot:trigger>
        <div class="btn btn-ghost btn-circle avatar">
            <div class="w-10 rounded-full bg-base-300 text-base-content text-center flex items-center justify-center">
                <span class="text-xs">{{ Auth::user()->initials() }}</span>
            </div>
        </div>
    </x-slot:trigger>

    <div class="menu-title">
        <span>{{ Auth::user()->name }}</span>
    </div>
    <a>Profile</a>
    <a>Settings</a>
</x-ui.dropdown>
```

### Alpine.js Integration

The dropdown component supports Alpine.js `x-bind:class` for reactive class management. This allows dynamic classes to be applied based on Alpine state:

```blade
<x-ui.dropdown x-bind:class="{ 'dropdown-open': isOpen }" menu>
    <x-slot:trigger>
        <button @click="isOpen = true">Open</button>
    </x-slot:trigger>
    <a>Item</a>
</x-ui.dropdown>
```

The component uses `$attributes->merge(['class' => $dropdownClasses])` to properly merge static classes with Alpine-bound classes.

### Implementation Details

-   Uses CSS focus pattern by default (better accessibility than Alpine.js pattern)
-   Follows DaisyUI dropdown patterns and classes
-   Supports all DaisyUI placement options
-   Compatible with menu items and custom content
-   Includes proper ARIA attributes for accessibility
-   Supports keyboard navigation (Tab, Enter, Escape)
-   **Alpine.js Support**: Properly merges Alpine.js `x-bind:class` with static classes using `$attributes->merge()`

### Current Usage in Project

1. **Locale Switcher** (`resources/views/components/preferences/locale-switcher.blade.php`)

    - Uses menu styling with custom content classes
    - Contains form submissions for locale changes
    - Uses icon in trigger button

2. **User Menu** (`resources/views/components/layouts/app/header.blade.php`)
    - Uses avatar as trigger
    - Contains navigation items and logout form
    - Uses menu styling with small size

### Migration Notes

-   All existing dropdowns have been migrated to use this component
-   Previous Alpine.js-based dropdowns (like locale-switcher) have been migrated to CSS focus pattern
-   The component is fully compatible with DaisyUI's dropdown classes and behavior

---

## History

### Alpine.js Integration Enhancement (2025-12-19)
- **Active Class Binding**: Added support for `x-bind:class` to enable reactive class management (e.g., maintaining open state via Alpine).
- **Class Merging Logic**: Updated component to use props and `$attributes->merge()` to properly combine static and dynamic classes.

### Centralized Component Creation (2025-01-XX)
- **Centralization**: Created `<x-ui.dropdown>` as the unified dropdown component for the project.
- **Improved Pattern**: Migrated from Alpine-based toggle patterns to CSS focus pattern for better accessibility and simplicity.
- **DaisyUI Integration**: Standardized all dropdown placements and menu styles using DaisyUI.
