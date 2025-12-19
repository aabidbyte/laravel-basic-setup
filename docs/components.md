# UI Components Documentation

This document provides comprehensive documentation for all reusable UI components in the application.

## Table of Contents

-   [Modal](#modal)
-   [Confirm Modal](#confirm-modal)
-   [Button](#button)
-   [Input](#input)
-   [Password](#password)
-   [Form](#form)
-   [Icon](#icon)
-   [Dropdown](#dropdown)
-   [Badge](#badge)
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

## Confirm Modal

**Location:** `resources/views/components/ui/confirm-modal.blade.php`

**Component Name:** `<x-ui.confirm-modal>`

### Description

A reusable confirmation modal component that provides a consistent way to handle user confirmations throughout the application. Uses Alpine.js for state management and can be triggered via Alpine.js events. Supports custom titles, messages, button labels, and callback functions for Livewire actions.

### Props

| Prop             | Type     | Default           | Description                                                                    |
| ---------------- | -------- | ----------------- | ------------------------------------------------------------------------------ |
| `id`             | `string` | `'confirm-modal'` | Unique ID for the modal (defaults to 'confirm-modal' for global usage)         |
| `confirmVariant` | `string` | `'error'`         | Button variant for the confirm button (uses `<x-ui.button>` variants)          |
| `cancelVariant`  | `string` | `'ghost'`         | Button variant for the cancel button (uses `<x-ui.button>` variants)           |
| `maxWidth`       | `string` | `'md'`            | Maximum width of the modal (DaisyUI sizes: `xs`, `sm`, `md`, `lg`, `xl`, etc.) |
| `placement`      | `string` | `'middle'`        | Modal placement: `top`, `middle`, `bottom`, `start`, `end`                     |

### Usage Examples

#### Basic Confirmation (Livewire)

```blade
{{-- Include the modal once in your layout --}}
<x-ui.confirm-modal />

{{-- Trigger from a button --}}
<button @click="$dispatch('confirm-modal', {
    title: 'Delete Item',
    message: 'Are you sure you want to delete this item?',
    confirmAction: () => $wire.delete(123)
})" class="btn btn-error">
    Delete
</button>
```

#### Confirmation with Custom Labels

```blade
<button @click="$dispatch('confirm-modal', {
    title: 'Clear All',
    message: 'This will permanently delete all items.',
    confirmLabel: 'Yes, Clear All',
    cancelLabel: 'Cancel',
    confirmAction: () => $wire.clearAll()
})" class="btn btn-error">
    Clear All
</button>
```

#### Confirmation with Custom Message

```blade
<button @click="$dispatch('confirm-modal', {
    title: 'Confirm Action',
    message: 'This action cannot be undone. Are you absolutely sure?',
    confirmAction: () => $wire.performAction()
})" class="btn btn-warning">
    Perform Action
</button>
```

#### Confirmation with JavaScript Callback

```blade
<button @click="$dispatch('confirm-modal', {
    title: 'Save Changes',
    message: 'Do you want to save your changes?',
    confirmAction: () => {
        // Custom JavaScript code
        console.log('Changes saved');
        // Or call a function
        saveChanges();
    }
})" class="btn btn-primary">
    Save
</button>
```

### Configuration Object

When dispatching the `confirm-modal` event, you can pass a configuration object with the following properties:

| Property        | Type       | Default                 | Description                                                        |
| --------------- | ---------- | ----------------------- | ------------------------------------------------------------------ |
| `title`         | `string`   | Translation key default | Title displayed in the modal header                                |
| `message`       | `string`   | Translation key default | Message displayed in the modal body                                |
| `confirmLabel`  | `string`   | Translation key default | Label for the confirm button                                       |
| `cancelLabel`   | `string`   | Translation key default | Label for the cancel button                                        |
| `confirmAction` | `function` | `null`                  | Callback function to execute when confirmed (required for actions) |

### Implementation Details

-   Uses Alpine.js for state management and event handling
-   Listens for `confirm-modal` Alpine.js events
-   Supports callback functions for both Livewire actions and custom JavaScript
-   Uses the existing `<x-ui.modal>` structure with HTML `<dialog>` element
-   Automatically closes after confirmation or cancellation
-   Includes proper event propagation handling (`@click.stop` for nested buttons)
-   Uses translation keys for default labels (can be overridden)

### Translation Keys

The component uses the following translation keys (can be overridden via config object):

-   `ui.modals.confirm.title` - Default title ("Confirm Action")
-   `ui.modals.confirm.message` - Default message ("Are you sure you want to proceed?")
-   `ui.actions.confirm` - Confirm button label ("Confirm")
-   `ui.actions.cancel` - Cancel button label ("Cancel")

### Current Usage in Project

1. **Notification Center** (`resources/views/pages/notifications/⚡index.blade.php`)
    - Delete individual notifications
    - Clear all notifications
    - Uses Livewire actions via callback functions

### Best Practices

1. **Include Once:** Include `<x-ui.confirm-modal />` once in your app layout (already included in `app.blade.php`)
2. **Use Callbacks:** Always provide a `confirmAction` callback function for Livewire actions
3. **Event Propagation:** Use `@click.stop` when triggering from nested elements to prevent event bubbling
4. **Custom Messages:** Provide clear, descriptive messages for destructive actions
5. **Button Variants:** Use `error` variant for destructive actions, `primary` for regular confirmations

### Example: Full Implementation

```blade
{{-- In your layout (already included in app.blade.php) --}}
<x-ui.confirm-modal />

{{-- In your component --}}
<div>
    <button @click="$dispatch('confirm-modal', {
        title: 'Delete Notification',
        message: 'This notification will be permanently deleted.',
        confirmAction: () => $wire.delete('{{ $notification->id }}')
    })" class="btn btn-sm btn-ghost btn-error">
        <x-ui.icon name="trash" class="h-4 w-4" />
    </button>
</div>
```

---

## Button

**Location:** `resources/views/components/ui/button.blade.php`

**Component Name:** `<x-ui.button>`

### Description

A centralized button component that provides consistent styling using DaisyUI button classes. Supports combining style variants (solid, outline, ghost, etc.) with colors (primary, error, success, etc.) for flexible button styling.

### Props

| Prop      | Type     | Default     | Description                                                                                      |
| --------- | -------- | ----------- | ------------------------------------------------------------------------------------------------ |
| `style`   | `string` | `'solid'`   | Button style: `solid`, `outline`, `ghost`, `link`, `soft`, `dash`                                |
| `color`   | `string` | `'primary'` | Button color: `primary`, `secondary`, `accent`, `neutral`, `info`, `success`, `warning`, `error` |
| `variant` | `string` | `null`      | **Deprecated**: Use `style` and `color` instead. Legacy prop for backward compatibility.         |
| `size`    | `string` | `'md'`      | Button size: `xs`, `sm`, `md`, `lg`, `xl`                                                        |
| `type`    | `string` | `null`      | HTML button type: `button`, `submit`, `reset` (defaults to `button` if not specified)            |

### Usage Examples

#### Basic Button

```blade
<x-ui.button>Click Me</x-ui.button>
```

#### Button Styles and Colors

```blade
{{-- Solid buttons (default style) --}}
<x-ui.button color="primary">Primary</x-ui.button>
<x-ui.button color="secondary">Secondary</x-ui.button>
<x-ui.button color="error">Delete</x-ui.button>
<x-ui.button color="success">Save</x-ui.button>

{{-- Outline buttons --}}
<x-ui.button style="outline" color="primary">Outline Primary</x-ui.button>
<x-ui.button style="outline" color="error">Outline Error</x-ui.button>

{{-- Ghost buttons --}}
<x-ui.button style="ghost" color="primary">Ghost Primary</x-ui.button>
<x-ui.button style="ghost" color="error">Ghost Error</x-ui.button>

{{-- Link buttons --}}
<x-ui.button style="link" color="primary">Link Button</x-ui.button>
```

#### Combining Style and Color

```blade
{{-- Ghost button with error color (btn-ghost btn-error) --}}
<x-ui.button style="ghost" color="error">Delete</x-ui.button>

{{-- Outline button with success color (btn-outline btn-success) --}}
<x-ui.button style="outline" color="success">Save</x-ui.button>

{{-- Soft button with warning color (btn-soft btn-warning) --}}
<x-ui.button style="soft" color="warning">Warning</x-ui.button>
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
<x-ui.button type="submit" color="primary">Submit Form</x-ui.button>
```

#### Button with Livewire

```blade
<x-ui.button wire:click="save" color="primary">Save</x-ui.button>
```

#### Button with Additional Attributes

```blade
<x-ui.button style="ghost" color="error" class="w-full" data-test="delete-button">
    Delete
</x-ui.button>
```

#### Backward Compatibility (Deprecated)

The `variant` prop is still supported for backward compatibility but is deprecated. It maps to appropriate `style` and `color` combinations:

```blade
{{-- Old way (still works) --}}
<x-ui.button variant="primary">Primary</x-ui.button>
<x-ui.button variant="ghost">Ghost</x-ui.button>
<x-ui.button variant="error">Error</x-ui.button>

{{-- New way (recommended) --}}
<x-ui.button color="primary">Primary</x-ui.button>
<x-ui.button style="ghost" color="primary">Ghost</x-ui.button>
<x-ui.button color="error">Error</x-ui.button>
```

### Implementation Details

-   Separates style variants (`btn-outline`, `btn-ghost`, etc.) from colors (`btn-primary`, `btn-error`, etc.)
-   Allows combining style and color for flexible button styling (e.g., `btn-ghost btn-error`)
-   Maps sizes to DaisyUI size classes (`btn-xs`, `btn-sm`, etc.)
-   Merges additional attributes (like `wire:click`, `class`, `data-*`) using Laravel's attribute merging
-   Defaults to `btn-primary` (solid primary) if no style/color is specified
-   Maintains backward compatibility with the deprecated `variant` prop

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

## Password

**Location:** `resources/views/components/ui/password.blade.php`

**Component Name:** `<x-ui.password>`

### Description

A specialized password input component with built-in show/hide toggle functionality. Includes an eye icon button that toggles password visibility. Uses Alpine.js for state management and follows the same patterns as the input component.

### Props

| Prop       | Type           | Default | Description                                                             |
| ---------- | -------------- | ------- | ----------------------------------------------------------------------- |
| `label`    | `string\|null` | `null`  | Optional label text displayed above the input                           |
| `error`    | `string\|null` | `null`  | Optional error message to display (overrides automatic error detection) |
| `required` | `bool`         | `false` | Whether to show a red asterisk (\*) indicating the field is required    |

### Additional Attributes

All standard HTML input attributes are supported (e.g., `name`, `id`, `placeholder`, `wire:model`, etc.). The `type` attribute is automatically set to `password` and should not be overridden.

### Usage Examples

#### Basic Password Input

```blade
<x-ui.password name="password" label="Password" />
```

#### Required Password Input

```blade
<x-ui.password name="password" label="Password" :required="true" />
```

#### Password Input with Livewire

```blade
<x-ui.password wire:model="password" name="password" label="Password" />
```

#### Password Input with Manual Error

```blade
<x-ui.password name="password" label="Password" error="Password is required" />
```

#### Password Input with Automatic Error Detection

The component automatically detects errors from Laravel's `$errors` bag based on the input's `name` attribute:

```blade
{{-- If validation fails for 'password' field, error will be shown automatically --}}
<x-ui.password name="password" label="Password" />
```

#### Password Input with Label Append (e.g., Forgot Password Link)

```blade
<x-ui.password name="password" label="Password">
    <x-slot:label-append>
        <a href="{{ route('password.request') }}" class="label-text-alt link">
            Forgot password?
        </a>
    </x-slot:label-append>
</x-ui.password>
```

#### Password Confirmation

```blade
<x-ui.password name="password" label="Password" />
<x-ui.password name="password_confirmation" label="Confirm Password" />
```

### Slots

-   **Default slot:** Not used (input is self-closing)
-   **`label-append` slot:** Optional content to display on the right side of the label (e.g., "Forgot password?" link)

### Implementation Details

-   Uses Alpine.js for show/hide toggle functionality (`x-data`, `x-bind:type`, `x-show`)
-   Automatically generates a unique ID if not provided
-   Automatically detects validation errors from Laravel's `$errors` bag
-   Applies `input-error` class when errors are present
-   Uses DaisyUI form control and label classes
-   Icon button positioned absolutely on the right side of the input
-   Eye icon (`eye`) shown when password is hidden
-   Eye-slash icon (`eye-slash`) shown when password is visible
-   Icon button includes proper ARIA labels for accessibility
-   Supports all HTML input attributes except `type` (automatically set to `password`)
-   Supports `label-append` slot for additional label content
-   Input has `pr-10` padding to accommodate the icon button

### Accessibility

-   Icon button includes `aria-label` that changes based on visibility state
-   Icon button is keyboard accessible (tabindex="0")
-   Uses semantic HTML structure
-   Icons use `x-cloak` to prevent flash of unstyled content

### Current Usage in Project

The password component can be used anywhere a password input is needed, including:

-   Login forms
-   Registration forms
-   Password reset forms
-   Password update forms
-   Any form requiring password input

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

## Dropdown

**Location:** `resources/views/components/ui/dropdown.blade.php`

**Component Name:** `<x-ui.dropdown>`

### Description

A centralized, flexible dropdown component that provides consistent dropdown functionality across the application. Uses CSS focus pattern by default for better accessibility and keyboard navigation. Supports multiple placement options, menu styling, hover behavior, and custom content.

### Props

| Prop           | Type           | Default         | Description                                                                    |
| -------------- | -------------- | --------------- | ------------------------------------------------------------------------------ |
| `placement`    | `string`       | `'end'`         | Dropdown placement: `start`, `center`, `end`, `top`, `bottom`, `left`, `right` |
| `hover`        | `bool`         | `false`         | Enable hover to open dropdown (adds `dropdown-hover` class)                    |
| `contentClass` | `string`       | `''`            | Additional CSS classes for dropdown content                                    |
| `bgClass`      | `string`       | `'bg-base-100'` | Background color class for dropdown content (default: bg-base-100)             |
| `menu`         | `bool`         | `false`         | Enable menu styling (adds `menu` class to dropdown content)                    |
| `menuSize`     | `string`       | `'md'`          | Menu size: `xs`, `sm`, `md`, `lg`, `xl` (only applies when `menu="true"`)      |
| `id`           | `string\|null` | `null`          | Optional ID for accessibility (auto-generated if not provided)                 |

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

    <li><a>Item 1</a></li>
    <li><a>Item 2</a></li>
    <li><a>Item 3</a></li>
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

    <li>
        <form method="POST" action="{{ route('preferences.locale') }}">
            @csrf
            <input type="hidden" name="locale" value="en_US">
            <button type="submit" class="btn btn-ghost btn-sm justify-start w-full">
                English
            </button>
        </form>
    </li>
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
    <li><a>Item</a></li>
</x-ui.dropdown>

{{-- Dropdown on the top --}}
<x-ui.dropdown placement="top" menu>
    <x-slot:trigger>
        <button class="btn">Top</button>
    </x-slot:trigger>
    <li><a>Item</a></li>
</x-ui.dropdown>
```

#### Dropdown with Avatar Trigger

```blade
<x-ui.dropdown placement="end" menu menuSize="sm">
    <x-slot:trigger>
        <div class="btn btn-ghost btn-circle avatar">
            <div class="w-10 rounded-full bg-base-300 text-base-content">
                <span class="text-xs">{{ Auth::user()->initials() }}</span>
            </div>
        </div>
    </x-slot:trigger>

    <div class="menu-title">
        <span>{{ Auth::user()->name }}</span>
    </div>
    <li><a>Profile</a></li>
    <li><a>Settings</a></li>
</x-ui.dropdown>
```

### Alpine.js Integration

The dropdown component supports Alpine.js `x-bind:class` for reactive class management. This allows dynamic classes to be applied based on Alpine state:

```blade
<x-ui.dropdown x-bind:class="{ 'dropdown-open': isOpen }" menu>
    <x-slot:trigger>
        <button @click="isOpen = true">Open</button>
    </x-slot:trigger>
    <li><a>Item</a></li>
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
-   Auto-generates unique IDs if not provided
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

## Badge

**Location:** `resources/views/components/ui/badge.blade.php`

**Component Name:** `<x-ui.badge>`

### Description

A centralized, flexible badge component that provides consistent badge functionality across the application. Supports all DaisyUI badge styles, colors, and sizes. Badges are used to inform users about the status of specific data, display counts, or provide visual indicators.

### Props

| Prop    | Type           | Default | Description                                                                                                              |
| ------- | -------------- | ------- | ------------------------------------------------------------------------------------------------------------------------ |
| `style` | `string\|null` | `null`  | Badge style: `outline`, `dash`, `soft`, `ghost` (default: solid badge)                                                   |
| `color` | `string\|null` | `null`  | Badge color: `neutral`, `primary`, `secondary`, `accent`, `info`, `success`, `warning`, `error` (default: theme default) |
| `size`  | `string`       | `'md'`  | Badge size: `xs`, `sm`, `md`, `lg`, `xl`                                                                                 |
| `class` | `string`       | `''`    | Additional CSS classes for the badge                                                                                     |

### Usage Examples

#### Basic Badge

```blade
<x-ui.badge>Default Badge</x-ui.badge>
```

#### Colored Badges

```blade
<x-ui.badge color="success">Success</x-ui.badge>
<x-ui.badge color="error">Error</x-ui.badge>
<x-ui.badge color="warning">Warning</x-ui.badge>
<x-ui.badge color="info">Info</x-ui.badge>
<x-ui.badge color="primary">Primary</x-ui.badge>
<x-ui.badge color="secondary">Secondary</x-ui.badge>
<x-ui.badge color="accent">Accent</x-ui.badge>
<x-ui.badge color="neutral">Neutral</x-ui.badge>
```

#### Sized Badges

```blade
<x-ui.badge size="xs">Extra Small</x-ui.badge>
<x-ui.badge size="sm">Small</x-ui.badge>
<x-ui.badge size="md">Medium</x-ui.badge>
<x-ui.badge size="lg">Large</x-ui.badge>
<x-ui.badge size="xl">Extra Large</x-ui.badge>
```

#### Styled Badges

```blade
<x-ui.badge style="outline" color="primary">Outline</x-ui.badge>
<x-ui.badge style="dash" color="success">Dash</x-ui.badge>
<x-ui.badge style="soft" color="warning">Soft</x-ui.badge>
<x-ui.badge style="ghost" color="error">Ghost</x-ui.badge>
```

#### Combined Props

```blade
<x-ui.badge color="success" size="lg">Enabled</x-ui.badge>
<x-ui.badge color="error" size="lg">Disabled</x-ui.badge>
<x-ui.badge color="primary" size="sm" style="outline">Small Outline</x-ui.badge>
```

#### Empty Badge (Dot Indicator)

```blade
<x-ui.badge color="error" size="sm"></x-ui.badge>
```

#### Badge in Button

```blade
<button class="btn">
    Notifications
    <x-ui.badge color="error" size="sm">3</x-ui.badge>
</button>
```

#### Badge in Navigation

```blade
<a href="/notifications" class="menu-item">
    Notifications
    <x-ui.badge size="sm">5</x-ui.badge>
</a>
```

### Implementation Details

-   Uses DaisyUI badge classes consistently
-   Supports all DaisyUI badge variants (styles, colors, sizes)
-   Can be used inside text, buttons, or standalone
-   Supports empty badges for dot indicators
-   Flexible prop-based API for easy customization
-   Maintains backward compatibility with DaisyUI classes

### Current Usage in Project

1. **Two-Factor Settings Page** (`resources/views/pages/settings/⚡two-factor.blade.php`)

    - Uses `color="success" size="lg"` for enabled status
    - Uses `color="error" size="lg"` for disabled status
    - Status indicators with color coding

2. **Navigation Items** (`resources/views/components/navigation/item.blade.php`)
    - Uses `size="sm"` for navigation item badges
    - Used in multiple places (summary, external links, internal links)
    - Small size badges for counts/notifications

### Migration Notes

-   All existing badges have been migrated to use this component
-   Previous inline badge classes (e.g., `badge badge-success badge-lg`) have been replaced with component props
-   The component is fully compatible with DaisyUI's badge classes and behavior
-   Badge content is passed via the default slot

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
-   **Confirm Modal** (`confirm-modal.blade.php`) - Reusable confirmation modal with Alpine.js event handling
-   **Button** (`button.blade.php`) - Styled buttons with variants and sizes
-   **Input** (`input.blade.php`) - Form inputs with labels and error handling
-   **Password** (`password.blade.php`) - Password input with show/hide toggle functionality
-   **Form** (`form.blade.php`) - Form wrapper with automatic CSRF and method spoofing
-   **Icon** (`icon.blade.php`) - Dynamic icon component with multiple icon pack support and security validation
-   **Dropdown** (`dropdown.blade.php`) - Flexible dropdown component with CSS focus pattern, multiple placements, and menu support
-   **Dropdown Item** (`dropdown-item.blade.php`) - Helper component for dropdown menu items
-   **Badge** (`badge.blade.php`) - Flexible badge component with support for all DaisyUI styles, colors, and sizes
-   **Icon Placeholder** (`icon-placeholder.blade.php`) - Placeholder for icons
-   **Placeholder** (`placeholder.blade.php`) - Generic placeholder component

### Settings Components (`resources/views/components/settings/`)

-   **Delete User Form** (`⚡delete-user-form.blade.php`) - User account deletion with confirmation modal
-   **Two-Factor Setup Modal** (`two-factor/⚡setup-modal.blade.php`) - 2FA setup and verification modal
-   **Two-Factor Recovery Codes** (`two-factor/⚡recovery-codes.blade.php`) - 2FA recovery codes display

---

## Changelog

### 2025-01-XX

-   **Confirm Modal Component:** Created reusable confirmation modal component
    -   **New Component**: Created `<x-ui.confirm-modal>` component for consistent confirmation dialogs
    -   **Alpine.js Integration**: Uses Alpine.js events (`confirm-modal`) for triggering confirmations
    -   **Livewire Support**: Supports Livewire actions via callback functions
    -   **Customizable**: Supports custom titles, messages, and button labels
    -   **Global Usage**: Included in app layout for global availability
    -   **Translation Support**: Uses translation keys with override capability
    -   **Migration**: Replaced browser `confirm()` dialogs in notification center with modal component
    -   **Documentation**: Added comprehensive documentation with usage examples

### 2025-01-XX

-   **Badge Component:** Created centralized badge component for consistent badge functionality
    -   **New Component**: Created `<x-ui.badge>` component with support for all DaisyUI badge variants
    -   **Style Support**: Supports outline, dash, soft, and ghost styles
    -   **Color Support**: Supports all DaisyUI colors (neutral, primary, secondary, accent, info, success, warning, error)
    -   **Size Support**: Supports all DaisyUI sizes (xs, sm, md, lg, xl)
    -   **Migration**: Migrated two-factor settings page and navigation items to use new badge component
    -   **Flexibility**: Supports empty badges for dot indicators and custom classes
    -   **Documentation**: Added comprehensive documentation with usage examples

### 2025-12-19

-   **Dropdown Component:** Enhanced dropdown component with Alpine.js class binding support
    -   **Alpine.js Integration**: Added support for `x-bind:class` to enable reactive class management
    -   **Class Merging**: Updated component to use `$attributes->merge(['class' => $dropdownClasses, 'id' => $dropdownId])` to properly merge static classes with Alpine-bound classes
    -   **State Management**: Enables use cases like conditionally applying `dropdown-open` class based on Alpine state
    -   **Usage Example**: Notification dropdown uses this feature to maintain open state during Livewire updates via `x-bind:class="{ 'dropdown-open': isOpen }"`

### 2025-01-XX

-   **Dropdown Component:** Created centralized dropdown component for consistent dropdown functionality
    -   **New Component**: Created `<x-ui.dropdown>` component with CSS focus pattern for better accessibility
    -   **Placement Support**: Supports all DaisyUI placements (start, center, end, top, bottom, left, right)
    -   **Menu Support**: Optional menu styling with size variants (xs, sm, md, lg, xl)
    -   **Hover Support**: Optional hover-to-open behavior
    -   **Migration**: Migrated locale-switcher and user menu to use new dropdown component
    -   **Accessibility**: Built-in ARIA attributes and keyboard navigation support
    -   **Documentation**: Added comprehensive documentation with usage examples

### 2025-01-XX

-   **Icon Component Refactoring:** Converted icon component from static SVG wrapper to dynamic Blade Icons component
    -   **Converted to Dynamic Component**: Changed from static SVG wrapper to dynamic icon component using Blade Icons
    -   **Multiple Icon Pack Support**: Added support for heroicons, fontawesome, bootstrap, and feather icon packs
    -   **Security Enhancements**: Implemented comprehensive input validation and sanitization for icon names, pack names, and CSS classes
    -   **Fallback Handling**: Automatically falls back to question mark icon if requested icon doesn't exist
    -   **Performance**: Uses `@inject` directive for dependency injection (no Livewire overhead)
    -   **Updated Usage**: All references changed from static SVG slots to dynamic icon names (e.g., `<x-ui.icon name="home" />`)
    -   **Size Support**: Maintains backward compatibility with predefined sizes (xs, sm, md, lg, xl) and custom Tailwind classes

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

**Last Updated:** 2025-12-19
