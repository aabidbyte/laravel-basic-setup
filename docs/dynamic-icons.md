# Dynamic Icon System

This application uses a dynamic, on-demand icon system built with Livewire 4. Icons are loaded efficiently - only icons actually used are loaded, with no icon bundles or full libraries loaded upfront.

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
<livewire:dynamic-icon-island name="user" class="w-8 h-8" />
```

### With Different Pack

Specify a different icon pack:

```blade
<livewire:dynamic-icon-island pack="heroicons-solid" name="user" class="w-8 h-8" />
```

### With Custom Classes

```blade
<livewire:dynamic-icon-island
    pack="heroicons"
    name="user-circle"
    class="w-10 h-10 text-primary"
/>
```

### In Loops

```blade
@foreach ($items as $item)
    <livewire:dynamic-icon-island
        name="{{ $item->icon_name }}"
        pack="{{ $item->icon_pack ?? 'heroicons' }}"
        class="w-5 h-5"
        wire:key="icon-{{ $item->id }}"
    />
@endforeach
```

## Component

### DynamicIconIsland

A Livewire component that loads a single icon on-demand.

**Props:**

-   `name` (string, required): Icon name without prefix
-   `pack` (string, optional): Icon pack name (default: 'heroicons'). Options: heroicons, heroicons-solid, fontawesome, bootstrap, feather
-   `class` (string): CSS classes to apply to the icon (default: 'w-6 h-6')

**Features:**

-   Automatic fallback to question-mark icon if component doesn't exist
-   Efficient on-demand loading
-   Supports all installed icon packs

## Performance

-   **No Bundles**: Only icons actually used are loaded
-   **Inlined SVGs**: All icons are inlined SVGs (no HTTP requests)
-   **Dynamic Loading**: Icons are resolved at runtime based on pack and name

## Examples

### Example 1: User Profile Icon

```blade
<livewire:dynamic-icon-island
    name="user-circle"
    class="w-10 h-10 text-primary"
/>
```

### Example 2: Navigation Icons

```blade
<nav class="flex gap-4">
    <livewire:dynamic-icon-island name="home" class="w-6 h-6" />
    <livewire:dynamic-icon-island name="user" class="w-6 h-6" />
    <livewire:dynamic-icon-island name="settings" class="w-6 h-6" />
</nav>
```

### Example 3: Different Icon Packs

```blade
<!-- Heroicons Outline (default, pack can be omitted) -->
<livewire:dynamic-icon-island name="user" class="w-6 h-6" />

<!-- Heroicons Solid -->
<livewire:dynamic-icon-island pack="heroicons-solid" name="user" class="w-6 h-6" />

<!-- Font Awesome -->
<livewire:dynamic-icon-island pack="fontawesome" name="user" class="w-6 h-6" />

<!-- Bootstrap Icons -->
<livewire:dynamic-icon-island pack="bootstrap" name="person" class="w-6 h-6" />

<!-- Feather Icons -->
<livewire:dynamic-icon-island pack="feather" name="user" class="w-6 h-6" />
```
