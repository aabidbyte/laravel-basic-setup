## Important Patterns

### Livewire 4 Single-File Component Pattern

**CRITICAL RULE**: **ALL Livewire components MUST use Single File Component (SFC) format** - Never create class-based components in `app/Livewire/`. All Livewire components must be single-file components with PHP class and Blade template in the same `.blade.php` file using anonymous class syntax (`new class extends Component { }`). This is the Livewire 4 standard and ensures consistency across the application.

**File Location**: `resources/views/pages/example.blade.php` (full-page) or `resources/views/components/example/⚡component.blade.php` (reusable) - must use `.blade.php` extension

**Full-Page Component Example**:

```php
<?php

use App\Livewire\BasePageComponent;

new class extends BasePageComponent {
    public ?string $pageTitle = 'ui.pages.example';

    public string $pageSubtitle = 'ui.pages.example.description'; // Optional

    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
};
?>

<div>
    <h1>Count: {{ $count }}</h1>
    <button wire:click="increment" class="rounded-lg bg-zinc-900 px-4 py-2 text-white">+</button>
</div>
```

**Reusable Component Example** (not a full page):

```php
<?php

use Livewire\Component;

new class extends Component {
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
};
?>

<div>
    <h1>Count: {{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>
```

**Route Registration**:

```php
// Full-page component
Route::livewire('/example', 'pages::example')->name('example');

// Reusable component (embedded in Blade view)
<livewire:users.table lazy />
```

**Important Notes**:

-   **ALL Livewire components MUST use SFC format** - Never create class-based components in `app/Livewire/`
-   **ALL full-page Livewire components MUST extend `App\Livewire\BasePageComponent`** (not `Livewire\Component`)
-   **Reusable components** extend `Livewire\Component` directly
-   Set `public ?string $pageTitle = 'ui.pages.example';` property for automatic title management (use translation keys)
-   **Optional**: Set `public string $pageSubtitle = 'ui.pages.example.description';` property for subtitle text (displayed below title in header)
-   **Translations**: Translation keys (containing dots) are automatically translated - use `'ui.pages.*'` format
-   **Plain Strings**: Can also use plain strings if translation is not needed
-   **No `parent::mount()` needed** - title and subtitle sharing happens automatically via `boot()` lifecycle hook
-   Single-file components must use `.blade.php` extension (not `.php`)
-   Full-page components go in `resources/views/pages/` and use `pages::` namespace
-   Nested/reusable Livewire components go in `resources/views/components/` and are referenced directly (e.g., `livewire:users.table`)
-   See `docs/livewire-4/index.md` for complete documentation

### Livewire Best Practices

-   Single root element required
-   Use `wire:loading` for loading states
-   Use `wire:key` in loops
-   Use `wire:model.live` for real-time updates
-   Prefer lifecycle hooks (`mount()`, `updatedFoo()`)
-   Always validate form data in Livewire actions
-   Always run authorization checks in Livewire actions
-   **Avoid `@entangle` directive**: **See Frontend Reactivity Rule above** - The `@entangle` directive is INVALID and MUST NOT be used. Always use `$wire.$entangle()` in Alpine.js `x-data` instead. UI-only state MUST remain Alpine-local and MUST NOT be entangled.
-   **Avoid `@php` directives in Blade templates**: All PHP logic should be included in the Livewire component class. Use computed properties, methods, or properties instead of `@php` blocks in templates. This keeps logic centralized in the component class and improves maintainability.
-   **Avoid conditional wrapper patterns with duplicated content**: When you have conditional wrapper elements (e.g., `<a>` vs `<div>`) that wrap the same content, extract the repeated content into a separate Blade component and call it twice—once inside each wrapper. This improves readability and maintainability. Example: Instead of duplicating content inside `@if ($hasLink) <a>...</a> @else <div>...</div> @endif`, create a component like `<x-notifications.notification-item />` and use it in both branches.
-   **Blade Control Directives in HTML Attributes**: **CRITICAL RULE** - The agent MUST NOT emit Blade control directives (`@if`, `@foreach`, `@isset`, etc.) inside HTML tags, attributes, Alpine expressions, or Livewire directives. Inside tags and SFC `<template>` blocks, ONLY the following Blade helpers are allowed: `@class`, `@style`, `@js`, and Blade attribute helpers (`@disabled`, `@checked`, etc.). **NEVER use directives like `@if` inside component opening tags as it causes syntax errors.** For dynamic attributes in components, use conditional attribute binding: `:attribute="$condition ? $value : null"`. Any violation risks Livewire 4 hydration failure or syntax errors and MUST be rewritten.

### UI Components

-   Use standard HTML elements with Tailwind CSS classes
-   Check existing components before creating custom
-   Components are built using Tailwind CSS utility classes

#### Base Modal Component (`<x-ui.base-modal>`)

The application includes a comprehensive base modal component located at `resources/views/components/ui/base-modal.blade.php` that serves as the foundation for all modals in the project. Built with Alpine.js following Penguin UI patterns, it provides extensive customization options.

**Features:**

-   **Pure Alpine.js**: Uses Alpine.js for state management (no `<dialog>` element dependency)
-   **Multiple Transitions**: Supports fade-in, scale-up, scale-down, slide-up, slide-down, unfold, and none
-   **Variants**: Supports default, success, info, warning, and danger variants with border colors
-   **Focus Trapping**: Built-in focus trapping with Alpine Focus plugin support
-   **Accessibility**: Full ARIA support with proper labels, descriptions, and keyboard navigation
-   **Flexible Placement**: Top, middle, bottom, start, end placement options
-   **Customizable**: Extensive prop system for styling, behavior, and callbacks
-   **Persistent Modals**: Support for modals that cannot be closed (useful for important notices)

**Key Props:**

-   `openState` (default: `'modalIsOpen'`): Alpine.js state variable name for modal open/close
-   `variant` (default: `'default'`): Modal variant - `'default'`, `'success'`, `'info'`, `'warning'`, `'danger'`
-   `transition` (default: `'scale-up'`): Transition type - `'fade-in'`, `'scale-up'`, `'scale-down'`, `'slide-up'`, `'slide-down'`, `'unfold'`, `'none'`
-   **DaisyUI `modal-box`**: Do **not** use DaisyUI’s `modal-box` class inside `<x-ui.base-modal>` — it’s meant for DaisyUI’s `.modal` wrapper and can unexpectedly affect opacity/animation in our custom Alpine modal. Use the component’s built-in classes/props instead.
-   `placement` (default: `'middle'`): Modal placement - `'top'`, `'middle'`, `'bottom'`, `'start'`, `'end'`
-   `trapFocus` (default: `true`): Trap focus inside modal (requires Alpine Focus plugin)
-   `persistent` (default: `false`): Prevent modal from closing
-   `onOpen` / `onClose`: Alpine.js expressions to execute on open/close

**Usage:**

```blade
{{-- Basic base modal --}}
<div x-data="{ modalIsOpen: false }">
    <button @click="modalIsOpen = true" class="btn">Open Modal</button>

    <x-ui.base-modal
        open-state="modalIsOpen"
        title="Special Offer"
        description="This is a special offer just for you!"
    >
        <p>Upgrade your account now to unlock premium features.</p>

        <x-slot:actions>
            <button @click="modalIsOpen = false" class="btn btn-ghost">Remind me later</button>
            <button @click="modalIsOpen = false" class="btn btn-primary">Upgrade Now</button>
        </x-slot:actions>
    </x-ui.base-modal>
</div>

{{-- Modal with variant and transition --}}
<x-ui.base-modal
    open-state="successModalIsOpen"
    title="Success!"
    variant="success"
    transition="slide-up"
>
    <p>Your action was completed successfully.</p>
</x-ui.base-modal>
```

**Component Location:** `resources/views/components/ui/base-modal.blade.php`

**Documentation:** See `docs/components/index.md` for complete documentation with all props, usage examples, and best practices.

**Note:** This is the primary modal component used throughout the project. All modals should use `<x-ui.base-modal>` directly with Alpine.js state management for full control and reactivity.

#### Icon Component (`<x-ui.icon>`)

The application includes a dynamic icon component located at `resources/views/components/ui/icon.blade.php` that provides secure, flexible icon rendering using Blade Icons.

**Features:**

-   **Multiple Icon Packs**: Supports heroicons (default), fontawesome, bootstrap, and feather
-   **Security**: Input validation and sanitization for icon names, pack names, and CSS classes
-   **Size Support**: Predefined sizes (xs, sm, md, lg, xl) or custom Tailwind classes
-   **Fallback Handling**: Automatically falls back to a question mark icon if the requested icon doesn't exist
-   **Blade Component**: Uses `@inject` for dependency injection (no Livewire overhead)

**Usage:**

```blade
{{-- Basic usage --}}
<x-ui.icon name="home" />

{{-- With size --}}
<x-ui.icon name="user" size="md" />

{{-- With custom class --}}
<x-ui.icon name="settings" class="h-5 w-5 text-primary" />

{{-- With different icon pack --}}
<x-ui.icon name="star" pack="fontawesome" size="lg" />
```

**Security Measures:**

-   Icon names are sanitized to only allow alphanumeric characters, dashes, and underscores
-   Pack names are validated against supported packs (falls back to 'heroicons' if invalid)
-   CSS class attributes are sanitized to prevent XSS attacks
-   Blade Icons handles SVG content sanitization internally

**Component Location:** `resources/views/components/ui/icon.blade.php`

**Service Dependency:** Uses `App\Services\IconPackMapper` (injected via `@inject` directive)

#### Dropdown Component (`<x-ui.dropdown>`)

The application includes a centralized, flexible dropdown component located at `resources/views/components/ui/dropdown.blade.php` that provides consistent dropdown functionality across the application.

**Features:**

-   **Multiple Placement Options**: Supports all DaisyUI placements (start, center, end, top, bottom, left, right)
-   **CSS Focus Pattern**: Uses CSS focus pattern by default for better accessibility and keyboard navigation
-   **Menu Support**: Optional menu styling with size variants (xs, sm, md, lg, xl)
-   **Hover Support**: Optional hover-to-open behavior
-   **Flexible Content**: Supports both menu items and custom content
-   **Accessibility**: Built-in ARIA attributes and keyboard navigation support

**Props:**

-   `placement` (default: `'end'`): Dropdown placement - `start`, `center`, `end`, `top`, `bottom`, `left`, `right`
-   `hover` (default: `false`): Enable hover to open dropdown
-   `contentClass` (default: `''`): Additional CSS classes for dropdown content
-   `bgClass` (default: `'bg-base-100'`): Background color class for dropdown content (default: bg-base-100)
-   `menu` (default: `false`): Enable menu styling (adds `menu` class)
-   `menuSize` (default: `'md'`): Menu size - `xs`, `sm`, `md`, `lg`, `xl`
-   `id` (default: `null`): Optional ID for accessibility (auto-generated if not provided)

**Usage:**

```blade
{{-- Basic dropdown with custom content --}}
<x-ui.dropdown>
    <x-slot:trigger>
        <button class="btn">Click me</button>
    </x-slot:trigger>

    <div class="p-4">
        Custom content here
    </div>
</x-ui.dropdown>

{{-- Menu dropdown --}}
<x-ui.dropdown placement="end" menu menuSize="sm">
    <x-slot:trigger>
        <div class="btn btn-ghost">Menu</div>
    </x-slot:trigger>

    <li><a>Item 1</a></li>
    <li><a>Item 2</a></li>
</x-ui.dropdown>

{{-- Dropdown with custom styling --}}
<x-ui.dropdown placement="end" menu contentClass="rounded-box z-[1] w-48 p-2 shadow-lg border border-base-300">
    <x-slot:trigger>
        <button class="btn btn-ghost btn-sm">
            <x-ui.icon name="globe-alt" />
        </button>
    </x-slot:trigger>

    <li>
        <form method="POST" action="{{ route('preferences.locale') }}">
            @csrf
            <input type="hidden" name="locale" value="en_US">
            <button type="submit" class="btn btn-ghost btn-sm justify-start w-full">English</button>
        </form>
    </li>
</x-ui.dropdown>

{{-- Hover dropdown --}}
<x-ui.dropdown hover>
    <x-slot:trigger>
        <button class="btn">Hover me</button>
    </x-slot:trigger>

    <div>Content appears on hover</div>
</x-ui.dropdown>
```

**Component Location:** `resources/views/components/ui/dropdown.blade.php`

**Migration Notes:**

-   All existing dropdowns have been migrated to use this component
-   The component uses CSS focus pattern by default (better accessibility than Alpine.js pattern)
-   Previous Alpine.js-based dropdowns (like locale-switcher) have been migrated to CSS focus pattern
-   The component is fully compatible with DaisyUI's dropdown classes and behavior

### CSP-Safe Alpine.js Development (CRITICAL)

> **MANDATORY RULE**: All Alpine.js components with complex logic MUST be CSP-safe. This project uses Livewire's `csp_safe` mode which prohibits inline JavaScript evaluation.

#### What is NOT Allowed (CSP Violations)

```blade
{{-- ❌ FORBIDDEN: Inline functions in x-data --}}
<div x-data="{
    isOpen: false,
    toggle() { this.isOpen = !this.isOpen },
    handleClick(e) {
        console.log(e);
    }
}">
```

```blade
{{-- ❌ FORBIDDEN: Arrow functions --}}
<div x-data="{ items: [], add: () => { this.items.push('new') } }">
```

```blade
{{-- ❌ FORBIDDEN: Complex expressions with methods --}}
<button @click="items.filter(i => i.active).forEach(i => process(i))">
```

#### What IS Allowed (CSP-Safe)

**Simple objects without methods:**
```blade
{{-- ✅ ALLOWED: Simple state objects --}}
<div x-data="{ isOpen: false, count: 0, name: 'test' }">
    <button @click="isOpen = !isOpen">Toggle</button>
    <button @click="count++">Increment</button>
</div>
```

**Registered Alpine components:**
```blade
{{-- ✅ ALLOWED: Registered component --}}
<div x-data="confirmModal({ title: 'Delete?' })">
    <button @click="openModal()">Open</button>
</div>
```

#### How to Create CSP-Safe Components

1. **Create file** in `resources/js/alpine/data/my-component.js`:

```javascript
/**
 * My Component - CSP-Safe Alpine Data Component
 * Self-registers as 'myComponent'
 */
export function myComponent(config = {}) {
    return {
        isOpen: config.isOpen || false,
        title: config.title || 'Default Title',
        
        toggle() {
            this.isOpen = !this.isOpen;
        },
        
        handleEvent(event) {
            // Complex logic here
            console.log(event.detail);
        }
    };
}

// Self-register when Alpine initializes
document.addEventListener('alpine:init', () => {
    window.Alpine.data('myComponent', myComponent);
});
```

2. **Add to `resources/assets.json`** (in appropriate section):

```json
{
    "js": {
        "shared": ["resources/js/alpine/data/my-component.js"],
        "app": [],
        "auth": []
    }
}
```

3. **Use in Blade** with simple config object:

```blade
<div x-data="myComponent({ title: @js(__('ui.my_title')) })">
    <button @click="toggle()">Toggle</button>
</div>
```

#### CSP-Safe Component Locations

| Component | File | Purpose |
|-----------|------|---------|
| `dataTable` | [datatable.js](file:///Users/hop/Packages/laravel-basic-setup/resources/js/datatable.js) | DataTable functionality |
| `notificationCenter` | [notification-center.js](file:///Users/hop/Packages/laravel-basic-setup/resources/js/notification-center.js) | Notification system |
| `toastCenter` | [notification-center.js](file:///Users/hop/Packages/laravel-basic-setup/resources/js/notification-center.js) | Toast notifications |
| `confirmModal` | [confirm-modal.js](file:///Users/hop/Packages/laravel-basic-setup/resources/js/alpine/data/confirm-modal.js) | Confirmation dialogs |
| `actionModal` | [action-modal.js](file:///Users/hop/Packages/laravel-basic-setup/resources/js/alpine/data/action-modal.js) | DataTable action modals |
| `copyToClipboard` | [copy-to-clipboard.js](file:///Users/hop/Packages/laravel-basic-setup/resources/js/alpine/data/copy-to-clipboard.js) | Copy functionality |
| `shareButton` | [share-button.js](file:///Users/hop/Packages/laravel-basic-setup/resources/js/alpine/data/share-button.js) | Sharing functionality |
| `themeSwitcher` | [theme-switcher.js](file:///Users/hop/Packages/laravel-basic-setup/resources/js/alpine/data/theme-switcher.js) | Theme toggling |
| `passwordVisibility` | [password-visibility.js](file:///Users/hop/Packages/laravel-basic-setup/resources/js/alpine/data/password-visibility.js) | Password show/hide |
| `dropdown` | [dropdown.js](file:///Users/hop/Packages/laravel-basic-setup/resources/js/alpine/data/dropdown.js) | Dropdown menus |
| `twoFactorSetup` | [two-factor-setup.js](file:///Users/hop/Packages/laravel-basic-setup/resources/js/alpine/data/two-factor-setup.js) | 2FA setup process |

> [!TIP]
> Always check [csp-safety.md](file:///Users/hop/Packages/laravel-basic-setup/docs/AGENTS/csp-safety.md) for detailed implementation rules.

#### Quick Reference: When to Extract

| Inline x-data Content | CSP-Safe? | Action |
|-----------------------|-----------|--------|
| `{ open: false }` | ✅ Yes | Keep inline |
| `{ count: 0, name: '' }` | ✅ Yes | Keep inline |
| `{ toggle() { ... } }` | ❌ No | Extract to JS file |
| `{ async fetch() { ... } }` | ❌ No | Extract to JS file |
| Arrow functions `() => {}` | ❌ No | Extract to JS file |

### Alpine.js Component Nesting & Shadowing

**CRITICAL**: When nesting Alpine components (e.g., using a Sheet inside a Select component), **avoid using generic variable names like `open` for state in both scopes**.

If the parent component (`Select`) uses `open` and the child component (`Sheet`) uses `open` (bound via `x-model`), Alpine's scope resolution can fail or shadow the intended variable, breaking the binding.

**❌ BAD (Shadowing Risk):**
```javascript
// Select Component
{ open: false }
// Sheet Component (Child)
{ open: false } // x-model="open" -> Fails to bind to Parent.open
```

**✅ GOOD (Distinct Names):**
```javascript
// Select Component
{ selectOpen: false }
// Sheet Component (Child)
{ open: false } // x-model="selectOpen" -> Binds correctly
```

