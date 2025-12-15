# UI Components Documentation

This document provides comprehensive documentation for all reusable UI components in the application.

## Table of Contents

-   [Modal](#modal)
-   [Button](#button)
-   [Input](#input)
-   [Form](#form)
-   [Icon](#icon)
-   [Component Usage Guidelines](#component-usage-guidelines)

---

## Modal

**Location:** `resources/views/components/ui/modal.blade.php`

**Component Name:** `<x-ui.modal>`

### Description

A centralized modal component built on the HTML `<dialog>` element following DaisyUI patterns. Supports automatic opening, custom widths, placement options, and close behaviors.

### Props

| Prop                  | Type           | Default      | Description                                                                                                                                                          |
| --------------------- | -------------- | ------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `id`                  | `string`       | **Required** | Unique ID for the modal (used for `showModal()` and `close()` methods)                                                                                               |
| `title`               | `string\|null` | `null`       | Optional title displayed at the top of the modal                                                                                                                     |
| `closeOnOutsideClick` | `bool`         | `true`       | Whether clicking outside the modal closes it                                                                                                                         |
| `showCloseButton`     | `bool`         | `false`      | Whether to show a close button (✕) at the top-right corner                                                                                                           |
| `closeBtn`            | `bool`         | `true`       | Whether to show a cancel/close button in the actions area                                                                                                            |
| `closeBtnLabel`       | `string`       | `'Cancel'`   | Label text for the cancel/close button                                                                                                                               |
| `maxWidth`            | `string\|null` | `null`       | Maximum width: DaisyUI sizes (`xs`, `sm`, `md`, `lg`, `xl`, `2xl`, `3xl`, `4xl`, `5xl`, `6xl`, `7xl`), fraction classes (`11/12`, `3/4`), or custom Tailwind classes |
| `placement`           | `string\|null` | `null`       | Placement: `modal-top`, `modal-middle`, `modal-bottom`, `modal-start`, `modal-end`, or responsive like `modal-bottom sm:modal-middle`                                |
| `class`               | `string`       | `''`         | Additional CSS classes for the modal-box                                                                                                                             |
| `autoOpen`            | `bool`         | `false`      | Automatically open the modal when rendered (uses Alpine.js `x-init`)                                                                                                 |

### Slots

-   **Default slot:** Main content of the modal
-   **`actions` slot:** Optional slot for action buttons (typically wrapped in `modal-action` div)

### Usage Examples

#### Basic Modal

```blade
<button class="btn" onclick="my_modal.showModal()">Open Modal</button>

<x-ui.modal id="my_modal" title="Hello!">
    <p>Press ESC key or click outside to close</p>
</x-ui.modal>
```

#### Modal with Actions

```blade
<x-ui.modal id="confirm_modal" title="Confirm Action">
    <p>Are you sure you want to proceed?</p>

    <x-slot:actions>
        <button class="btn btn-primary">Confirm</button>
    </x-slot:actions>
</x-ui.modal>
```

**Note:** The cancel button is included by default. It appears before any custom actions in the `actions` slot.

#### Modal with Close Button

```blade
<x-ui.modal id="info_modal" title="Information" :show-close-button="true">
    <p>Click ✕ to close</p>
</x-ui.modal>
```

#### Modal with Custom Width

```blade
<x-ui.modal id="large_modal" max-width="5xl" title="Large Modal">
    <p>This modal has a custom maximum width</p>
</x-ui.modal>
```

#### Responsive Modal

```blade
<x-ui.modal id="responsive_modal" placement="modal-bottom sm:modal-middle" title="Responsive">
    <p>Bottom on mobile, middle on larger screens</p>
</x-ui.modal>
```

#### Auto-Open Modal (with Livewire)

```blade
@if ($showModal)
    <x-ui.modal id="auto_modal" :auto-open="true" title="Auto Opened">
        <p>This modal opens automatically when rendered</p>
    </x-ui.modal>
@endif
```

#### Modal with Form (Livewire)

```blade
<x-ui.modal id="form_modal" title="Submit Form">
    <form id="my-form" wire:submit="submit">
        <x-ui.input wire:model="name" name="name" label="Name" />
    </form>

    <x-slot:actions>
        <button type="submit" form="my-form" class="btn btn-primary">Submit</button>
    </x-slot:actions>
</x-ui.modal>
```

**Note:** The cancel button is included by default, so you don't need to add it manually.

#### Disable Default Cancel Button

```blade
<x-ui.modal id="no_cancel_modal" title="No Cancel" :close-btn="false">
    <p>This modal doesn't have a cancel button</p>

    <x-slot:actions>
        <button class="btn btn-primary">OK</button>
    </x-slot:actions>
</x-ui.modal>
```

#### Custom Cancel Button Label

```blade
<x-ui.modal id="custom_label_modal" title="Custom Label" close-btn-label="Close">
    <p>This modal has a custom cancel button label</p>
</x-ui.modal>
```

### Opening and Closing Modals

#### JavaScript Methods

```javascript
// Open modal
document.getElementById("my_modal").showModal();

// Close modal
document.getElementById("my_modal").close();
```

#### HTML Button (Recommended)

```blade
<button class="btn" onclick="my_modal.showModal()">Open Modal</button>
```

#### Alpine.js (for auto-open)

The `autoOpen` prop uses Alpine.js `x-init` to automatically open the modal when it's rendered. This is useful for Livewire components that conditionally render modals.

### Implementation Details

-   Uses native HTML `<dialog>` element for accessibility and ESC key support
-   Follows DaisyUI modal patterns and classes
-   Supports all DaisyUI modal modifiers and placements
-   Compatible with Livewire forms and actions
-   Uses Alpine.js for automatic opening when `autoOpen="true"`

### Current Usage in Project

1. **Two-Factor Setup Modal** (`resources/views/components/settings/two-factor/⚡setup-modal.blade.php`)

    - Uses `autoOpen="true"` to automatically open when component renders
    - Includes form for OTP verification
    - Uses `actions` slot for Continue button

2. **Delete User Confirmation Modal** (`resources/views/components/settings/⚡delete-user-form.blade.php`)
    - Opens via `onclick="confirm_user_deletion_modal.showModal()"`
    - Contains password confirmation form
    - Uses `actions` slot for Cancel and Delete buttons

---

## Button

**Location:** `resources/views/components/ui/button.blade.php`

**Component Name:** `<x-ui.button>`

### Description

A centralized button component that provides consistent styling using DaisyUI button classes.

### Props

| Prop      | Type     | Default     | Description                                                                                                                    |
| --------- | -------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------ |
| `variant` | `string` | `'primary'` | Button variant: `primary`, `secondary`, `accent`, `neutral`, `ghost`, `link`, `outline`, `error`, `success`, `warning`, `info` |
| `size`    | `string` | `'md'`      | Button size: `xs`, `sm`, `md`, `lg`, `xl`                                                                                      |
| `type`    | `string` | `'button'`  | HTML button type: `button`, `submit`, `reset`                                                                                  |

### Usage Examples

#### Basic Button

```blade
<x-ui.button>Click Me</x-ui.button>
```

#### Button Variants

```blade
<x-ui.button variant="primary">Primary</x-ui.button>
<x-ui.button variant="secondary">Secondary</x-ui.button>
<x-ui.button variant="error">Delete</x-ui.button>
<x-ui.button variant="success">Save</x-ui.button>
<x-ui.button variant="outline">Outline</x-ui.button>
<x-ui.button variant="ghost">Ghost</x-ui.button>
```

#### Button Sizes

```blade
<x-ui.button size="xs">Extra Small</x-ui.button>
<x-ui.button size="sm">Small</x-ui.button>
<x-ui.button size="md">Medium</x-ui.button>
<x-ui.button size="lg">Large</x-ui.button>
<x-ui.button size="xl">Extra Large</x-ui.button>
```

#### Submit Button

```blade
<x-ui.button type="submit" variant="primary">Submit Form</x-ui.button>
```

#### Button with Livewire

```blade
<x-ui.button wire:click="save" variant="primary">Save</x-ui.button>
```

#### Button with Additional Attributes

```blade
<x-ui.button variant="error" class="w-full" data-test="delete-button">
    Delete
</x-ui.button>
```

### Implementation Details

-   Maps variants to DaisyUI button classes (`btn-primary`, `btn-error`, etc.)
-   Maps sizes to DaisyUI size classes (`btn-xs`, `btn-sm`, etc.)
-   Merges additional attributes (like `wire:click`, `class`, `data-*`) using Laravel's attribute merging
-   Defaults to `btn-primary` if variant is not recognized

---

## Input

**Location:** `resources/views/components/ui/input.blade.php`

**Component Name:** `<x-ui.input>`

### Description

A centralized input component with built-in label, error handling, and DaisyUI styling.

### Props

| Prop       | Type           | Default | Description                                                             |
| ---------- | -------------- | ------- | ----------------------------------------------------------------------- |
| `label`    | `string\|null` | `null`  | Optional label text displayed above the input                           |
| `error`    | `string\|null` | `null`  | Optional error message to display (overrides automatic error detection) |
| `required` | `bool`         | `false` | Whether to show a red asterisk (\*) indicating the field is required    |

### Additional Attributes

All standard HTML input attributes are supported (e.g., `type`, `name`, `id`, `placeholder`, `wire:model`, etc.).

### Usage Examples

#### Basic Input

```blade
<x-ui.input type="text" name="name" label="Name" />
```

#### Required Input

```blade
<x-ui.input type="email" name="email" label="Email" :required="true" />
```

#### Input with Livewire

```blade
<x-ui.input type="text" wire:model="name" name="name" label="Name" />
```

#### Input with Manual Error

```blade
<x-ui.input type="password" name="password" label="Password" error="Password is required" />
```

#### Input with Automatic Error Detection

The component automatically detects errors from Laravel's `$errors` bag based on the input's `name` attribute:

```blade
{{-- If validation fails for 'email' field, error will be shown automatically --}}
<x-ui.input type="email" name="email" label="Email" />
```

#### Input with Label Append (e.g., Forgot Password Link)

```blade
<x-ui.input type="password" name="password" label="Password">
    <x-slot:label-append>
        <a href="{{ route('password.request') }}" class="label-text-alt link">
            Forgot password?
        </a>
    </x-slot:label-append>
</x-ui.input>
```

#### Password Input

```blade
<x-ui.input type="password" wire:model="password" name="password" label="Password" />
```

#### Input with Placeholder

```blade
<x-ui.input type="text" name="search" label="Search" placeholder="Enter search term..." />
```

### Slots

-   **Default slot:** Not used (input is self-closing)
-   **`label-append` slot:** Optional content to display on the right side of the label (e.g., "Forgot password?" link)

### Implementation Details

-   Automatically generates a unique ID if not provided
-   Automatically detects validation errors from Laravel's `$errors` bag
-   Applies `input-error` class when errors are present
-   Uses DaisyUI form control and label classes
-   Supports all HTML input types and attributes
-   Supports `label-append` slot for additional label content

---

## Form

**Location:** `resources/views/components/ui/form.blade.php`

**Component Name:** `<x-ui.form>`

### Description

A centralized form component that automatically handles CSRF tokens, method spoofing, and form attributes.

### Props

| Prop     | Type           | Default  | Description                                                                  |
| -------- | -------------- | -------- | ---------------------------------------------------------------------------- |
| `method` | `string`       | `'POST'` | HTTP method: `GET`, `POST`, `PUT`, `PATCH`, `DELETE` (automatically spoofed) |
| `action` | `string\|null` | `null`   | Form action URL (optional, defaults to current URL)                          |
| `class`  | `string`       | `''`     | Additional CSS classes for the form                                          |

### Usage Examples

#### Basic Form

```blade
<x-ui.form method="POST" action="{{ route('login.store') }}">
    <x-ui.input type="email" name="email" label="Email" />
    <x-ui.button type="submit">Submit</x-ui.button>
</x-ui.form>
```

#### Livewire Form

```blade
<x-ui.form wire:submit="updateProfile">
    <x-ui.input wire:model="name" name="name" label="Name" />
    <x-ui.button type="submit">Save</x-ui.button>
</x-ui.form>
```

#### Form with Custom Method

```blade
{{-- Automatically adds @method('PUT') and @csrf --}}
<x-ui.form method="PUT" action="{{ route('users.update', $user) }}">
    <x-ui.input wire:model="name" name="name" label="Name" />
    <x-ui.button type="submit">Update</x-ui.button>
</x-ui.form>
```

#### Form with Additional Classes

```blade
{{-- Default spacing (space-y-6) is applied automatically --}}
<x-ui.form method="POST" class="flex flex-col">
    {{-- Form content --}}
</x-ui.form>

{{-- Override spacing if needed --}}
<x-ui.form method="POST" class="space-y-4">
    {{-- Form content with custom spacing --}}
</x-ui.form>
```

### Implementation Details

-   Automatically adds `@csrf` token for non-GET requests
-   Automatically adds `@method()` directive for PUT, PATCH, DELETE requests
-   **Centralized spacing:** Automatically applies `space-y-6` for consistent vertical spacing between form elements
-   Spacing can be overridden by providing custom spacing classes in the `class` prop
-   Merges additional attributes (like `wire:submit`, `class`, `data-*`) using Laravel's attribute merging
-   Supports all standard HTML form attributes

---

## Icon

**Location:** `resources/views/components/ui/icon.blade.php`

**Component Name:** `<x-ui.icon>`

### Description

A centralized SVG icon wrapper component that provides consistent sizing and styling for inline SVG icons.

### Props

| Prop    | Type     | Default | Description                                                                         |
| ------- | -------- | ------- | ----------------------------------------------------------------------------------- |
| `size`  | `string` | `'sm'`  | Icon size: `xs`, `sm`, `md`, `lg`, `xl`, or custom Tailwind class (e.g., `w-6 h-6`) |
| `class` | `string` | `''`    | Additional CSS classes                                                              |

### Slots

-   **Default slot:** SVG path elements (the actual icon paths)

### Usage Examples

#### Basic Icon

```blade
<x-ui.icon>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
</x-ui.icon>
```

#### Icon Sizes

```blade
<x-ui.icon size="xs">...</x-ui.icon> {{-- w-4 h-4 --}}
<x-ui.icon size="sm">...</x-ui.icon> {{-- w-5 h-5 --}}
<x-ui.icon size="md">...</x-ui.icon> {{-- w-6 h-6 --}}
<x-ui.icon size="lg">...</x-ui.icon> {{-- w-8 h-8 --}}
<x-ui.icon size="xl">...</x-ui.icon> {{-- w-10 h-10 --}}
```

#### Custom Size

```blade
<x-ui.icon size="w-12 h-12">
    <path d="..." />
</x-ui.icon>
```

#### Icon with Additional Classes

```blade
<x-ui.icon size="md" class="text-primary">
    <path d="..." />
</x-ui.icon>
```

#### Icon in Button

```blade
<x-ui.button variant="primary">
    <x-ui.icon size="sm">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </x-ui.icon>
    Add Item
</x-ui.button>
```

### Dynamic Icons

For dynamic icon loading (e.g., from icon packs), use the `<livewire:dynamic-icon-island>` component:

```blade
<livewire:dynamic-icon-island name="heroicon-o-home" pack="heroicons" class="w-6 h-6" />
```

### Implementation Details

-   Wraps SVG content in a standardized `<svg>` element
-   Provides consistent sizing through predefined size classes
-   Supports custom sizes via Tailwind classes
-   Uses `currentColor` for stroke fill (inherits text color)
-   Default viewBox is `0 0 24 24` (standard for most icon sets)

---

## Component Usage Guidelines

### When to Use Centralized Components

1. **Consistency:** Always use centralized components for UI elements that appear multiple times
2. **Maintainability:** Changes to styling or behavior can be made in one place
3. **Accessibility:** Centralized components ensure consistent accessibility features
4. **Documentation:** Centralized components are documented and follow best practices

### Creating New Components

When creating a new reusable UI component:

1. Place it in `resources/views/components/ui/`
2. Use the `@props` directive for component properties
3. Follow DaisyUI patterns and classes
4. Support Livewire attributes (`wire:model`, `wire:click`, etc.)
5. Add comprehensive documentation to this file
6. Include usage examples

### Component Naming

-   Use kebab-case for component files: `button.blade.php`, `modal.blade.php`
-   Use dot notation for component names: `<x-ui.button>`, `<x-ui.modal>`
-   Use descriptive, semantic names that indicate purpose

### Best Practices

1. **Props:** Always provide sensible defaults for optional props
2. **Slots:** Use named slots for distinct content areas (e.g., `actions`, `header`, `footer`)
3. **Attributes:** Use Laravel's `$attributes` merging to allow additional HTML attributes
4. **Error Handling:** Include error states and validation feedback where applicable
5. **Accessibility:** Ensure components are keyboard navigable and screen-reader friendly
6. **Responsive:** Make components responsive using Tailwind's responsive utilities

### Migration from Custom Implementations

When migrating existing custom implementations to centralized components:

1. Identify the component pattern being used
2. Check if a centralized component exists
3. If not, create one following the guidelines above
4. Update all usages to use the centralized component
5. Test thoroughly to ensure functionality is preserved
6. Update this documentation

---

## Component Index

### UI Components (`resources/views/components/ui/`)

-   **Modal** (`modal.blade.php`) - Dialog modals using HTML `<dialog>` element
-   **Button** (`button.blade.php`) - Styled buttons with variants and sizes
-   **Input** (`input.blade.php`) - Form inputs with labels and error handling
-   **Form** (`form.blade.php`) - Form wrapper with automatic CSRF and method spoofing
-   **Icon** (`icon.blade.php`) - SVG icon wrapper with consistent sizing
-   **Icon Placeholder** (`icon-placeholder.blade.php`) - Placeholder for icons
-   **Placeholder** (`placeholder.blade.php`) - Generic placeholder component

### Settings Components (`resources/views/components/settings/`)

-   **Delete User Form** (`⚡delete-user-form.blade.php`) - User account deletion with confirmation modal
-   **Two-Factor Setup Modal** (`two-factor/⚡setup-modal.blade.php`) - 2FA setup and verification modal
-   **Two-Factor Recovery Codes** (`two-factor/⚡recovery-codes.blade.php`) - 2FA recovery codes display

---

## Changelog

### 2025-01-XX

-   **Component Centralization:** Migrated all UI components to use centralized components
    -   **Modal:** All modals now use `<x-ui.modal>` component
    -   **Button:** All buttons now use `<x-ui.button>` component
    -   **Input:** All inputs now use `<x-ui.input>` component (with `label-append` slot support)
    -   **Form:** All forms now use `<x-ui.form>` component with automatic CSRF and method spoofing
    -   **Icon:** Created `<x-ui.icon>` component for consistent SVG icon rendering
    -   Updated all authentication forms (login, register, password reset, etc.)
    -   Updated all settings forms (profile, password, two-factor)
    -   Updated layout components (header, sidebar)
    -   Added comprehensive documentation for all components

---

**Last Updated:** 2025-01-XX
