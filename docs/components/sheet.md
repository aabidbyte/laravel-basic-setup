# Sheet Component (`x-ui.sheet`)

A highly flexible, accessible, and theme-aware "drawer" or "sheet" component for the TALL stack. It uses Alpine.js for behavior and DaisyUI for styling.

## Features

-   **Positions:** `right`, `left`, `bottom`, `top`.
-   **Accessibility:** Focus trapping, `aria-modal`, `role="dialog"`, and keyboard navigation (ESC to close).
-   **Scroll Locking:** Prevents the background document from scrolling when the sheet is open.
-   **Teleport:** Renders the modal at the end of the `<body>` tag to avoid z-index or overflow issues.
-   **Sticky Actions:** Specialized slot for action buttons that remain visible while content scrolls.
-   **Themes:** Automatically adapts to light/dark modes using standard DaisyUI tokens (`bg-base-100`, `base-content`).

## Props

| Prop | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `position` | `string` | `'right'` | Position of the sheet (`right`, `left`, `bottom`, `top`). |
| `title` | `string` | `null` | Optional header title. |
| `width` | `string` | `'w-full max-w-md'` | CSS classes for width (used for `left`/`right` sheets). |
| `height` | `string` | `'h-[50vh]'` | CSS classes for height (used for `top`/`bottom` sheets). |
| `closeOnBackdrop` | `bool` | `true` | Whether clicking the backdrop closes the sheet. |
| `closeOnEscape` | `bool` | `true` | Whether pressing ESC closes the sheet. |

## Slots

-   `trigger`: Optional. Clickable element that opens the sheet.
-   `default` (content): The main scrollable content of the sheet.
-   `actions`: Optional. Fixed footer for action buttons.

## Usage Examples

### 1. Standard Right Sheet (Default)

Ideal for details views, edit forms, or settings.

```blade
<x-ui.sheet title="Edit User">
    <x-slot:trigger>
        <button class="btn btn-primary">Open Settings</button>
    </x-slot:trigger>

    <div class="space-y-4">
        <p>User settings form goes here...</p>
        <!-- Form inputs -->
    </div>

    <x-slot:actions>
        <div class="flex justify-end gap-2">
            <button class="btn btn-ghost" @click="close">Cancel</button>
            <button class="btn btn-primary">Save Changes</button>
        </div>
    </x-slot:actions>
</x-ui.sheet>
```

### 2. Bottom Sheet (Mobile First)

Great for menus or quick actions on mobile devices.

```blade
<x-ui.sheet position="bottom" height="h-[40vh]" title="Options">
    <x-slot:trigger>
        <button class="btn btn-sm">More Options</button>
    </x-slot:trigger>

    <ul class="menu w-full bg-base-100">
        <li><a><x-icon name="o-share" /> Share</a></li>
        <li><a><x-icon name="o-trash" /> Delete</a></li>
    </ul>
</x-ui.sheet>
```

### 3. Left Navigation Drawer

```blade
<x-ui.sheet position="left" width="w-80" title="Navigation">
    <x-slot:trigger>
        <button class="btn btn-square btn-ghost">
            <x-icon name="o-bars-3" class="w-6 h-6" />
        </button>
    </x-slot:trigger>

    <ul class="menu p-0">
        <li><a>Dashboard</a></li>
        <li><a>Profile</a></li>
    </ul>
</x-ui.sheet>
```

### 4. Controlling from Parent (Livewire/Alpine)

If you need to open the sheet programmatically (e.g., from a Livewire event), you can wrap it or dispatch events if you extend the logic, but the simplest way is to use `x-data` around the triggering context if not using the internal trigger slot.

However, the component receives the `trigger` slot which automatically binds the click handler.

To open from an external button via Livewire:

```blade
<!-- In your Livewire View -->
<div x-data="{ showSheet: false }">
    <button @click="$dispatch('open-user-edit')" class="btn">Edit</button>

    <!-- You might need to adapt the component to listen to window events if strictly decoupled, 
         but typically you use the Trigger slot. -->
</div>
```

**Note:** The current implementation relies on the internal `open` state. If you need external control, you can pass a variable to `x-model` if you refactor the component to support `x-modelable`.

## Architecture Details

-   **Alpine.js**: Manages the `open` state, transition logic, and event handling.
-   **CSS Locking**: When open, `overflow-hidden` is added to the `<body>` tag via a watcher in `init()`.
-   **Focus Trap**: Uses `x-trap.noscroll` (requires Alpine Focus plugin) to keep focus within the modal for accessibility.
-   **CSP Compliance**: The script logic is colocated using `@assets` and the `document.addEventListener('alpine:init')` pattern to ensure strict CSP compliance without `unsafe-eval`.
-   **Dynamic Z-Index**: Uses a global `window.uiZIndexStack` to automatically increment specific sheet z-indexes when opened. This ensures that the most recently opened sheet (or nested sheet) always appears on top, resolving stacking context issues.

## Controlling from Parent (x-model)

The component supports `x-model` (via `x-modelable`) to control the open state from a parent scope.

```blade
<div x-data="{ isSettingsOpen: false }">
    <button @click="isSettingsOpen = true">Open Settings</button>
    
    <x-ui.sheet x-model="isSettingsOpen" title="Settings">
        Content...
    </x-ui.sheet>
</div>
```

**Note on Nesting:** When using `x-model` in nested components (e.g., Sheet inside another Component), ensure variable names are distinct (e.g., `isSettingsOpen` vs generic `open`) to avoid Alpine scope shadowing.
