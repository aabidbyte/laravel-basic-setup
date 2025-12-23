# UI Components Documentation

This document provides comprehensive documentation for all reusable UI components in the application.

## Table of Contents

-   [Base Modal](#base-modal)
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

## Base Modal

**Location:** `resources/views/components/ui/base-modal.blade.php`

**Component Name:** `<x-ui.base-modal>`

### Description

A comprehensive, flexible modal component built with Alpine.js following Penguin UI patterns. This is the foundation for all modals in the project, providing extensive customization options including transitions, variants, focus trapping, and accessibility features. The base modal uses pure Alpine.js for state management and does not rely on the HTML `<dialog>` element, making it more flexible for complex use cases.

**Note:** This is the primary modal component used throughout the project. All modals should use `<x-ui.base-modal>` directly with Alpine.js state management.

### Props

#### Modal Identification

| Prop      | Type     | Default | Description                                                      |
| --------- | -------- | ------- | ---------------------------------------------------------------- |
| `id`      | `string` | `null`  | Unique ID for the modal (auto-generated if not provided)        |
| `modalId` | `string` | `null`  | Alternative prop name for modal ID                               |

#### State Management

| Prop        | Type     | Default         | Description                                                      |
| ----------- | -------- | --------------- | ---------------------------------------------------------------- |
| `open`      | `bool`   | `false`         | Initial open state                                               |
| `openState` | `string` | `'modalIsOpen'` | Name of the Alpine.js state variable for modal open/close state |

#### Content

| Prop            | Type     | Default | Description                                                      |
| --------------- | -------- | ------- | ---------------------------------------------------------------- |
| `title`         | `string` | `null`  | Modal title displayed in header                                  |
| `titleId`       | `string` | `null`  | ARIA labelledby ID (auto-generated if not provided)             |
| `description`   | `string` | `null`  | Modal description (displayed below title)                       |
| `descriptionId` | `string` | `null`  | ARIA describedby ID (auto-generated if not provided)            |

#### Visual Appearance

| Prop          | Type     | Default   | Description                                                                                                 |
| ------------- | -------- | --------- | ----------------------------------------------------------------------------------------------------------- |
| `variant`     | `string` | `'default'` | Modal variant: `'default'`, `'success'`, `'info'`, `'warning'`, `'danger'` (adds border color)            |
| `maxWidth`    | `string` | `'md'`    | Maximum width: `'xs'`, `'sm'`, `'md'`, `'lg'`, `'xl'`, `'2xl'`, `'3xl'`, `'4xl'`, `'5xl'`, `'6xl'`, `'7xl'`, or custom Tailwind class |
| `placement`   | `string` | `'middle'` | Modal placement: `'top'`, `'middle'`, `'bottom'`, `'start'`, `'end'`                                      |
| `class`       | `string` | `''`      | Additional classes for modal container                                                                      |
| `dialogClass` | `string` | `''`      | Additional classes for modal dialog box                                                                     |
| `headerClass` | `string` | `''`      | Additional classes for header section                                                                       |
| `bodyClass`   | `string` | `''`      | Additional classes for body section                                                                         |
| `footerClass` | `string` | `''`      | Additional classes for footer section                                                                        |

#### Behavior

| Prop                | Type | Default | Description                                                      |
| ------------------- | ---- | ------- | ---------------------------------------------------------------- |
| `closeOnOutsideClick` | `bool` | `true`  | Close modal when clicking backdrop                              |
| `closeOnEscape`     | `bool` | `true`  | Close modal on ESC key press                                    |
| `trapFocus`         | `bool` | `true`  | Trap focus inside modal (requires Alpine Focus plugin)          |
| `preventScroll`     | `bool` | `true`  | Prevent body scroll when modal is open                          |
| `autoOpen`          | `bool` | `false` | Automatically open modal when rendered                          |

#### Transitions

| Prop                 | Type     | Default     | Description                                                                                                 |
| -------------------- | -------- | ----------- | ----------------------------------------------------------------------------------------------------------- |
| `transition`         | `string` | `'scale-up'` | Transition type: `'fade-in'`, `'scale-up'`, `'scale-down'`, `'slide-up'`, `'slide-down'`, `'unfold'`, `'none'` |
| `transitionDuration` | `int`    | `200`       | Transition duration in milliseconds                                                                        |
| `transitionDelay`    | `int`    | `100`       | Transition delay in milliseconds                                                                           |
| `backdropTransition` | `bool`   | `true`      | Enable backdrop fade transition                                                                             |

#### Close Button

| Prop                | Type     | Default         | Description                                    |
| ------------------- | -------- | --------------- | ---------------------------------------------- |
| `showCloseButton`   | `bool`   | `true`          | Show close button (✕) in header                |
| `closeButtonLabel`  | `string` | `'Close modal'` | ARIA label for close button                    |
| `closeButtonClass`  | `string` | `''`            | Additional classes for close button            |

#### Footer Actions

| Prop         | Type | Default | Description                                    |
| ------------ | ---- | ------- | ---------------------------------------------- |
| `showFooter` | `bool` | `true` | Show footer section                            |

#### Accessibility

| Prop        | Type  | Default      | Description                      |
| ----------- | ----- | ------------ | -------------------------------- |
| `role`      | `string` | `'dialog'` | ARIA role                        |
| `ariaModal` | `bool`   | `true`       | Set `aria-modal="true"` attribute |

#### Advanced

| Prop         | Type     | Default | Description                                                      |
| ------------ | -------- | ------- | ---------------------------------------------------------------- |
| `onOpen`     | `string` | `null`  | Alpine.js expression to execute when modal opens                 |
| `onClose`    | `string` | `null`  | Alpine.js expression to execute when modal closes                |
| `persistent` | `bool`   | `false` | Prevent modal from closing (useful for important modals)        |

### Slots

-   **Default slot:** Main content of the modal (body)
-   **`actions` slot:** Footer actions (buttons, links, etc.)
-   **`footerActions` slot:** Alternative slot name for footer actions

### Usage Examples

#### Basic Base Modal

```blade
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
```

#### Modal with Different Variants

```blade
{{-- Success Modal --}}
<x-ui.base-modal
    open-state="successModalIsOpen"
    title="Success!"
    variant="success"
>
    <p>Your action was completed successfully.</p>
</x-ui.base-modal>

{{-- Warning Modal --}}
<x-ui.base-modal
    open-state="warningModalIsOpen"
    title="Warning"
    variant="warning"
>
    <p>Please review your changes before proceeding.</p>
</x-ui.base-modal>

{{-- Danger Modal --}}
<x-ui.base-modal
    open-state="dangerModalIsOpen"
    title="Danger"
    variant="danger"
>
    <p>This action cannot be undone.</p>
</x-ui.base-modal>
```

#### Modal with Different Transitions

```blade
{{-- Fade In --}}
<x-ui.base-modal
    open-state="fadeModalIsOpen"
    title="Fade In"
    transition="fade-in"
>
    <p>This modal fades in smoothly.</p>
</x-ui.base-modal>

{{-- Slide Up --}}
<x-ui.base-modal
    open-state="slideModalIsOpen"
    title="Slide Up"
    transition="slide-up"
>
    <p>This modal slides up from the bottom.</p>
</x-ui.base-modal>

{{-- Unfold --}}
<x-ui.base-modal
    open-state="unfoldModalIsOpen"
    title="Unfold"
    transition="unfold"
>
    <p>This modal unfolds from the top.</p>
</x-ui.base-modal>
```

#### Modal with Custom Placement

```blade
{{-- Top Placement --}}
<x-ui.base-modal
    open-state="topModalIsOpen"
    title="Top Modal"
    placement="top"
>
    <p>This modal appears at the top.</p>
</x-ui.base-modal>

{{-- Bottom Placement (Mobile-friendly) --}}
<x-ui.base-modal
    open-state="bottomModalIsOpen"
    title="Bottom Modal"
    placement="bottom"
>
    <p>This modal appears at the bottom (great for mobile).</p>
</x-ui.base-modal>
```

#### Modal with Callbacks

```blade
<div x-data="{ 
    modalIsOpen: false,
    onModalOpen() {
        console.log('Modal opened');
        // Perform actions when modal opens
    },
    onModalClose() {
        console.log('Modal closed');
        // Perform cleanup when modal closes
    }
}">
    <button @click="modalIsOpen = true" class="btn">Open Modal</button>
    
    <x-ui.base-modal
        open-state="modalIsOpen"
        title="Modal with Callbacks"
        on-open="onModalOpen()"
        on-close="onModalClose()"
    >
        <p>This modal has open and close callbacks.</p>
    </x-ui.base-modal>
</div>
```

#### Persistent Modal (Cannot be Closed)

```blade
<x-ui.base-modal
    open-state="persistentModalIsOpen"
    title="Important Notice"
    persistent
>
    <p>You must complete this action before proceeding.</p>
    
    <x-slot:actions>
        <button @click="completeAction()" class="btn btn-primary">Complete Action</button>
    </x-slot:actions>
</x-ui.base-modal>
```

#### Modal without Close Button

```blade
<x-ui.base-modal
    open-state="noCloseModalIsOpen"
    title="No Close Button"
    :show-close-button="false"
>
    <p>This modal cannot be closed with the X button.</p>
</x-ui.base-modal>
```

#### Modal with Custom Styling

```blade
<x-ui.base-modal
    open-state="customModalIsOpen"
    title="Custom Styled Modal"
    variant="info"
    max-width="3xl"
    dialog-class="bg-base-200"
    header-class="border-b border-base-300"
    body-class="py-6"
    footer-class="border-t border-base-300"
>
    <p>This modal has custom styling applied to different sections.</p>
</x-ui.base-modal>
```

### Transition Types

The component supports the following transition types:

1. **`fade-in`**: Simple opacity fade
2. **`scale-up`**: Scale from 50% to 100% (default)
3. **`scale-down`**: Scale from 100% to 50%
4. **`slide-up`**: Slide up from bottom
5. **`slide-down`**: Slide down from top
6. **`unfold`**: Unfold from top (scale-y animation)
7. **`none`**: No transition

### Keyboard Navigation

The modal supports full keyboard navigation:

-   **Tab**: Move focus to next focusable element
-   **Shift + Tab**: Move focus to previous focusable element
-   **Enter/Space**: Activate focused element
-   **ESC**: Close modal (if `closeOnEscape` is `true`)

### Focus Trapping

When `trapFocus` is `true` (default), the modal uses Alpine.js Focus plugin to trap focus inside the modal. This ensures:

-   Focus stays within the modal
-   Tab navigation cycles through modal elements only
-   Background content is not accessible via keyboard

**Note:** Focus trapping requires the Alpine.js Focus plugin. If you don't have it installed, set `trapFocus` to `false`.

### Accessibility Features

-   **ARIA attributes**: Automatically sets `role="dialog"`, `aria-modal="true"`, `aria-labelledby`, and `aria-describedby`
-   **Focus management**: Traps focus inside modal when open
-   **Keyboard support**: Full keyboard navigation and ESC key support
-   **Screen reader support**: Proper ARIA labels and descriptions

### Implementation Details

-   Uses pure Alpine.js for state management (no `<dialog>` element)
-   Follows Penguin UI modal patterns and best practices
-   Supports all transition types from Penguin UI documentation
-   Fully accessible with ARIA attributes and keyboard navigation
-   Compatible with Livewire components
-   Uses `x-cloak` to prevent flash of unstyled content

### Usage Pattern

All modals in the project use `<x-ui.base-modal>` directly with Alpine.js state management. The component uses Alpine.js `x-data` and `x-show` directives for state management, making it fully reactive and compatible with Livewire components.

#### Basic Modal with Alpine.js State

```blade
<div x-data="{ modalIsOpen: false }">
    <button @click="modalIsOpen = true" class="btn">Open Modal</button>
    
    <x-ui.base-modal id="my-modal" open-state="modalIsOpen" title="Hello!">
        <p>Press ESC key or click outside to close</p>
    </x-ui.base-modal>
</div>
```

#### Modal with External State Control

```blade
<div x-data="{ deleteAccountModalOpen: false }">
    <button @click="deleteAccountModalOpen = true" class="btn">Delete Account</button>
    
    <x-ui.base-modal id="delete-modal" open-state="deleteAccountModalOpen" title="Confirm Deletion">
        <p>Are you sure you want to delete your account?</p>
        
        <x-slot:actions>
            <button @click="deleteAccountModalOpen = false" class="btn btn-ghost">Cancel</button>
            <button @click="deleteAccount()" class="btn btn-error">Delete</button>
        </x-slot:actions>
    </x-ui.base-modal>
</div>
```

#### Auto-Open Modal

```blade
@if ($showModal)
    <x-ui.base-modal id="auto-modal" :auto-open="true" title="Auto Opened">
        <p>This modal opens automatically when rendered</p>
    </x-ui.base-modal>
@endif
```

### Current Usage in Project

1. **Two-Factor Setup Modal** (`resources/views/components/settings/two-factor/⚡setup-modal.blade.php`)
    - Uses `autoOpen="true"` to automatically open when component renders
    - Includes form for OTP verification
    - Uses `actions` slot for Continue button

2. **Delete User Confirmation Modal** (`resources/views/components/settings/⚡delete-user-form.blade.php`)
    - Uses Alpine.js state (`deleteAccountModalOpen`) to control visibility
    - Contains password confirmation form
    - Uses `actions` slot for Cancel and Delete buttons

3. **Confirm Modal** (`resources/views/components/ui/confirm-modal.blade.php`)
    - Wraps `<x-ui.base-modal>` for confirmation dialogs
    - Supports external state control via `open-state` prop
    - Can be triggered via Alpine.js events or direct state management

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
-   Uses `<x-ui.base-modal>` for the underlying modal structure
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

**Smart Color Defaults**: When using the deprecated `variant` prop, the component automatically sets the appropriate color. For example, `variant="error"` will default to `color="error"` unless an explicit color is provided.

### Props

| Prop      | Type     | Default   | Description                                                                                                                                                                          |
| --------- | -------- | --------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `style`   | `string` | `'solid'` | Button style: `solid`, `outline`, `ghost`, `link`, `soft`, `dash`                                                                                                                    |
| `color`   | `string` | `null`    | Button color: `primary`, `secondary`, `accent`, `neutral`, `info`, `success`, `warning`, `error`. Defaults to `'primary'` if not set.                                                |
| `variant` | `string` | `null`    | **Deprecated**: Use `style` and `color` instead. Legacy prop for backward compatibility. Automatically sets appropriate color (e.g., `variant="error"` defaults to `color="error"`). |
| `size`    | `string` | `'md'`    | Button size: `xs`, `sm`, `md`, `lg`, `xl`                                                                                                                                            |
| `type`    | `string` | `null`    | HTML button type: `button`, `submit`, `reset` (defaults to `button` if not specified)                                                                                                |

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

The `variant` prop is still supported for backward compatibility but is deprecated. It maps to appropriate `style` and `color` combinations. **Important**: When using `variant`, the component automatically sets the appropriate color unless you explicitly provide one.

```blade
{{-- Old way (still works) --}}
{{-- variant="error" automatically defaults to color="error" --}}
<x-ui.button variant="error">Delete</x-ui.button>
<x-ui.button variant="primary">Primary</x-ui.button>
<x-ui.button variant="ghost">Ghost</x-ui.button>
<x-ui.button variant="success">Success</x-ui.button>

{{-- You can still override the color if needed --}}
<x-ui.button variant="error" color="primary">Error variant, primary color</x-ui.button>

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
-   **Smart defaults**: When using `variant`, automatically sets the appropriate color (e.g., `variant="error"` → `color="error"`) unless an explicit color is provided

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

## Alpine.js Integration & Best Practices

All components in this application use Alpine.js for interactivity. When working with components that use Alpine.js, follow these guidelines:

### Alpine.js Best Practices

1. **Use `Alpine.data()` Pattern**: Components should use `Alpine.data()` for reusable data objects instead of global functions.

2. **Avoid `@entangle` Directive**: In Livewire v3/v4, refrain from using the `@entangle` directive. Use `$wire.$entangle()` instead:
   ```blade
   <!-- ❌ Avoid -->
   <div x-data="{ open: @entangle('isOpen').live }">
   
   <!-- ✅ Preferred -->
   <div x-data="{ open: $wire.$entangle('isOpen') }">
   ```

3. **Prefer Alpine.js Over Plain JavaScript**: Always use Alpine.js directives instead of plain JavaScript:
   ```blade
   <!-- ❌ Avoid -->
   <button onclick="document.getElementById('id').showModal()">
   
   <!-- ✅ Preferred -->
   <button @click="$el.closest('section').querySelector('#id')?.showModal()">
   ```

4. **Use `$el` and `$refs`**: Reference elements using Alpine.js utilities:
   ```blade
   <!-- ✅ Preferred -->
   <button @click="$refs.modal.showModal()">
   <div x-ref="modal">
   ```

5. **Proper Cleanup**: Always implement `destroy()` methods in Alpine components to clean up subscriptions, intervals, and event listeners.

For comprehensive Alpine.js documentation, see [docs/alpinejs.md](./alpinejs.md).

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

-   Use kebab-case for component files: `button.blade.php`, `base-modal.blade.php`
-   Use dot notation for component names: `<x-ui.button>`, `<x-ui.base-modal>`
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

## Table System

**Location:** `resources/views/components/table/`

**Component Names:** `<x-table>`, `<x-table.header>`, `<x-table.body>`, `<x-table.row>`, `<x-table.cell>`, `<x-table.actions>`, `<x-table.bulk>`, `<x-table.pagination>`, `<x-table.empty>`

### Description

A comprehensive, Blade-first data table system designed for Livewire 4. The table system follows a strict separation of concerns:

-   **Livewire Component**: Handles all data, state, queries, and business logic
-   **Blade Components**: Pure presentational components that render UI only

This architecture ensures:
-   Single Livewire component per table (island architecture)
-   Reusable table components across different data sources
-   No business logic in Blade templates
-   Easy to test and maintain

### Architecture

The table system uses a **single Livewire component** (e.g., `UsersTable`) that:
-   Manages all state (search, filters, sorting, pagination, bulk selection)
-   Handles all queries and data fetching
-   Provides computed properties for columns, actions, and rows
-   Emits events for row actions and bulk actions

The **Blade components** are pure presentational:
-   Accept data via props
-   Render HTML markup only
-   Use `wire:*` attributes to communicate with Livewire
-   No database queries or business logic

### Component Structure

```
<x-table>
    <x-table.header :columns="$columns" :sort-by="$sortBy" :sort-direction="$sortDirection" />
    <x-table.body>
        @foreach ($rows as $row)
            <x-table.row wire:key="row-{{ $row->id }}">
                <x-table.cell>Content</x-table.cell>
                <x-table.cell>
                    <x-table.actions :actions="$actions" :item-uuid="$row->uuid" />
                </x-table.cell>
            </x-table.row>
        @endforeach
    </x-table.body>
</x-table>
```

### Props

#### `<x-table>`

| Prop  | Type     | Default | Description                    |
| ----- | -------- | ------- | ------------------------------ |
| `class` | `string` | `''`    | Additional CSS classes         |

#### `<x-table.header>`

| Prop             | Type     | Default | Description                                    |
| ---------------- | -------- | -------- | ---------------------------------------------- |
| `columns`        | `array`  | `[]`     | Column configuration array                     |
| `sortBy`         | `string` | `null`   | Current sort column key                        |
| `sortDirection`  | `string` | `'asc'`  | Current sort direction (`asc` or `desc`)       |
| `showBulk`       | `bool`   | `false`  | Show bulk selection checkbox                   |
| `selectPage`     | `bool`   | `false`  | Whether current page is selected               |
| `selectAll`      | `bool`   | `false`  | Whether all items are selected                 |

#### `<x-table.row>`

| Prop      | Type   | Default | Description                          |
| --------- | ------ | ------- | ------------------------------------ |
| `selected` | `bool` | `false` | Whether the row is selected          |

**Note**: The row component accepts `wire:click` via `$attributes` for row click behavior.

#### `<x-table.cell>`

Accepts all standard HTML attributes via `$attributes`.

#### `<x-table.actions>`

| Prop       | Type   | Default | Description                                    |
| ---------- | ------ | ------- | ---------------------------------------------- |
| `actions`  | `array` | `[]`    | Array of action configurations                 |
| `itemUuid` | `string` | `''`   | UUID of the item for action callbacks          |

#### `<x-table.bulk>`

| Prop          | Type   | Default | Description                          |
| ------------- | ------ | ------- | ------------------------------------ |
| `selectedCount` | `int` | `0`     | Number of selected items             |
| `bulkActions` | `array` | `[]`    | Array of bulk action configurations  |

#### `<x-table.pagination>`

| Prop      | Type              | Default | Description                    |
| --------- | ----------------- | ------- | ------------------------------ |
| `paginator` | `LengthAwarePaginator` | **Required** | Laravel paginator instance |

#### `<x-table.empty>`

| Prop         | Type  | Default | Description                          |
| ------------ | ----- | ------- | ------------------------------------ |
| `columnsCount` | `int` | `1`     | Number of columns for colspan        |

### Usage Example

**Livewire Component** (`resources/views/components/users/⚡table.blade.php`):

```php
<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';

    public function getColumns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
        ];
    }

    public function getRowActions(): array
    {
        return [
            ['key' => 'view', 'label' => 'View', 'variant' => 'ghost', 'icon' => 'eye'],
            ['key' => 'delete', 'label' => 'Delete', 'variant' => 'ghost', 'color' => 'error', 'icon' => 'trash'],
        ];
    }

    #[Computed]
    public function rows()
    {
        return User::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    public function sortBy(string $key): void
    {
        if ($this->sortBy === $key) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $key;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function handleRowAction(string $action, string $userUuid): void
    {
        match ($action) {
            'view' => $this->dispatch('user-view', userUuid: $userUuid),
            'delete' => $this->deleteUser($userUuid),
            default => null,
        };
    }
}; ?>

<div>

```blade
    <x-ui.input
        type="text"
        wire:model.live.debounce.300ms="search"
        placeholder="Search..."
    />

    <x-table>
        <x-table.header
            :columns="$this->getColumns()"
            :sort-by="$sortBy"
            :sort-direction="$sortDirection"
        />

        <x-table.body>
            @forelse ($this->rows as $user)
                <x-table.row wire:key="user-{{ $user->uuid }}" wire:click="rowClicked('{{ $user->uuid }}')">
                    <x-table.cell>{{ $user->name }}</x-table.cell>
                    <x-table.cell>{{ $user->email }}</x-table.cell>
                    <x-table.cell>
                        <x-table.actions
                            :actions="$this->getRowActions()"
                            :item-uuid="$user->uuid"
                        />
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty :columns-count="count($this->getColumns()) + 1" />
            @endforelse
        </x-table.body>
    </x-table>

    @if ($this->rows->hasPages())
        <div class="mt-6">
            <x-table.pagination :paginator="$this->rows" />
        </div>
    @endif
</div>
```

### Features

-   **Sortable Columns**: Click column headers to sort (toggles direction)
-   **Search**: Global search with debounced input
-   **Pagination**: Full pagination support with page numbers
-   **Row Actions**: Action buttons per row (view, edit, delete, etc.)
-   **Bulk Actions**: Select multiple rows and perform bulk operations
-   **Row Click**: Optional row click behavior (navigate, view details, etc.)
-   **Empty State**: Automatic empty state when no results
-   **Responsive**: Table scrolls horizontally on small screens

### Best Practices

1. **Single Livewire Component**: One Livewire component per table (island architecture)
2. **SFC Format**: All Livewire components must use Single File Component (SFC) format with anonymous class syntax
3. **Pure Blade Components**: Table components should only render, never query or mutate
4. **Computed Properties**: Use `#[Computed]` for expensive queries
5. **URL Syncing**: Use `#[Url]` attributes for state that should sync with URL
6. **Authorization**: Check permissions in `mount()` method
7. **Event Dispatching**: Use `dispatch()` for actions that need to be handled elsewhere
8. **Wire Keys**: Always use `wire:key` in loops for proper Livewire tracking

### Current Usage in Project

1. **Users Table** (`resources/views/components/users/⚡table.blade.php`)
    - Lists all users with search, sorting, and pagination
    - Supports bulk selection and actions
    - Row click navigates to user details
    - Protected by `Permissions::VIEW_USERS` permission
    - Uses SFC format (Single File Component) with anonymous class syntax
    - **Now uses the DataTable System** (see below)

---

## DataTable System

**Location:** `app/Services/DataTable/`, `app/Livewire/DataTable/`

**Component Name:** `BaseDataTableComponent` (abstract base class)

### Description

A comprehensive, service-based DataTable system that provides a reusable architecture for building data tables with advanced search, filtering, sorting, pagination, and statistics. The system follows a strict separation of concerns with a service layer handling all business logic and a base Livewire component providing the integration point.

### Architecture

The DataTable System consists of several layers:

1. **Service Layer** (`app/Services/DataTable/`):
   - `DataTableBuilder`: Orchestrates building the DataTable response
   - `SearchService`: Applies global search using search macro
   - `FilterService`: Applies filters based on request parameters
   - `SortService`: Applies sorting to queries (supports relation fields)
   - `StatsService`: Calculates statistics (optional)
   - `SessionService`: Manages session state for filters (uses DataTablePreferencesService)
   - `DataTablePreferencesService`: Manages DataTable preferences (filters, per_page, sort, search) with persistence

2. **Configuration Layer** (`app/Services/DataTable/Configs/`):
   - `DataTableConfigInterface`: Contract for DataTable configuration
   - Entity-specific configs (e.g., `UsersDataTableConfig`)

3. **Transformation Layer** (`app/Services/DataTable/Transformers/`):
   - `TransformerInterface`: Contract for transforming models
   - Entity-specific transformers (e.g., `UserDataTableTransformer`)

4. **Options Provider Layer** (`app/Services/DataTable/OptionsProviders/`):
   - `OptionsProviderInterface`: Contract for filter options
   - Entity-specific providers (e.g., `RoleOptionsProvider`)

5. **Livewire Integration** (`app/Livewire/DataTable/`):
   - `BaseDataTableComponent`: Abstract base class for DataTable components
   - Entity-specific components extend this base class

### Key Features

- **Global Search**: Search across multiple fields using the search macro
- **Advanced Filtering**: Support for select, multiselect, boolean, relationship, date range filters
- **Smart Sorting**: Optimized sorting with support for relation fields
- **Statistics**: Optional entity-specific statistics
- **Preferences Persistence**: All preferences (filters, per_page, sort, search) persisted in session and user's `frontend_preferences` JSON column (for authenticated users)
- **Bulk Actions**: Support for bulk operations
- **URL Synchronization**: Search, filters, and sorting sync with URL via `#[Url]` attributes
- **Computed Properties**: Uses `#[Computed]` for efficient data loading
- **Automatic Preference Loading**: Preferences are automatically loaded on component mount and saved when changed

### Usage Example

**Create a DataTable Config** (`app/Services/DataTable/Configs/UsersDataTableConfig.php`):

```php
<?php

use App\Services\DataTable\Contracts\DataTableConfigInterface;

class UsersDataTableConfig implements DataTableConfigInterface
{
    public function getSearchableFields(): array
    {
        return ['name', 'email', 'username'];
    }

    public function getFilterableFields(): array
    {
        return [
            'role' => [
                'type' => 'select',
                'label' => __('ui.table.users.filters.role'),
                'options_provider' => RoleOptionsProvider::class,
                'relationship' => [
                    'name' => 'roles',
                    'column' => 'name',
                ],
            ],
        ];
    }

    public function getSortableFields(): array
    {
        return [
            'name' => ['label' => __('ui.table.users.name')],
            'email' => ['label' => __('ui.table.users.email')],
        ];
    }

    public function getDefaultSort(): ?array
    {
        return ['column' => 'created_at', 'direction' => 'desc'];
    }

    // ... other required methods
}
```

**Create a Transformer** (`app/Services/DataTable/Transformers/UserDataTableTransformer.php`):

```php
<?php

use App\Services\DataTable\Contracts\TransformerInterface;

class UserDataTableTransformer implements TransformerInterface
{
    public function transform($user): array
    {
        return [
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            // ... other fields
        ];
    }
}
```

**Create a Livewire Component** (`resources/views/components/users/⚡table.blade.php`):

```php
<?php

use App\Livewire\DataTable\BaseDataTableComponent;
use App\Models\User;
use App\Services\DataTable\Configs\UsersDataTableConfig;
use App\Services\DataTable\Transformers\UserDataTableTransformer;

new class extends BaseDataTableComponent
{
    protected function getConfig(): DataTableConfigInterface
    {
        return app(UsersDataTableConfig::class);
    }

    protected function getBaseQuery(): Builder
    {
        return User::query();
    }

    protected function getTransformer(): TransformerInterface
    {
        return app(UserDataTableTransformer::class);
    }

    /**
     * Get headers configuration (for table header row)
     */
    public function getHeaders(): array
    {
        return [
            ['key' => 'name', 'label' => __('ui.table.users.name'), 'sortable' => true],
            ['key' => 'email', 'label' => __('ui.table.users.email'), 'sortable' => true],
        ];
    }

    /**
     * Get columns configuration (for table body cells)
     */
    public function getColumns(): array
    {
        return [
            ['key' => 'name', 'type' => 'text', 'bold' => true],
            ['key' => 'email', 'type' => 'text', 'muted' => true],
        ];
    }

    /**
     * Get row actions configuration
     */
    public function getRowActions(): array
    {
        return [
            ['key' => 'view', 'label' => __('ui.actions.view'), 'variant' => 'ghost', 'icon' => 'eye'],
            ['key' => 'edit', 'label' => __('ui.actions.edit'), 'variant' => 'ghost', 'icon' => 'pencil'],
        ];
    }
}; ?>

<x-datatable
    :rows="$this->rows->items()"
    :headers="$this->getHeaders()"
    :columns="$this->getColumns()"
    :actions-per-row="$this->getRowActions()"
    :bulk-actions="$this->getBulkActions()"
    row-click="rowClicked"
    :selected="$selected"
    :sort-by="$sortBy ?: null"
    :sort-direction="$sortDirection"
    :paginator="$this->rows"
/>
```

### DataTable DSL (Domain-Specific Language)

The DataTable System includes a fluent DSL for defining table structure, similar to the Navigation and Notification builders. This DSL provides type-safe, autocomplete-friendly definitions with no hardcoded strings.

**Key Principles:**
- **No hardcoded strings**: All action keys, column types, filter types, icons, and component names must use constants/enums
- **Typed closures**: Action `execute()` closures are fully typed for autocomplete
- **Transformer-only values**: All cell values come from the transformer array (no model access in Blade)
- **Component registry**: All cell and filter components are allowlisted via registries for security

**Location**: `app/Services/DataTable/Dsl/`, `app/Enums/DataTableColumnType.php`, `app/Enums/DataTableFilterType.php`, `app/Constants/DataTableUi.php`

#### DSL Classes

- **`DataTableDefinition`**: Main builder for table definitions
- **`HeaderItem`**: Fluent builder for table headers (with sorting, visibility, viewport-only)
- **`ColumnItem`**: Fluent builder for table columns (with type, custom render, viewport-only)
- **`RowActionItem`**: Fluent builder for row actions (with execute closure, modal support)
- **`BulkActionItem`**: Fluent builder for bulk actions (with execute closure, modal support)
- **`FilterItem`**: Fluent builder for filters (with type, options provider, relationship)

#### Usage Example (Model Definition)

```php
// In User model (using HasDataTable trait)
use App\Constants\DataTableUi;
use App\Enums\DataTableColumnType;
use App\Enums\DataTableFilterType;
use App\Services\DataTable\Dsl\BulkActionItem;
use App\Services\DataTable\Dsl\ColumnItem;
use App\Services\DataTable\Dsl\FilterItem;
use App\Services\DataTable\Dsl\HeaderItem;
use App\Services\DataTable\Dsl\RowActionItem;

public static function datatable(): DataTableDefinition
{
    return DataTableDefinition::make()
        ->headers(
            HeaderItem::make()
                ->label(__('ui.table.users.name'))
                ->sortable('name')
                ->column(
                    ColumnItem::make()
                        ->name('name')
                        ->type(DataTableColumnType::TEXT)
                        ->props(['bold' => true])
                ),
            HeaderItem::make()
                ->label(__('ui.table.users.email'))
                ->sortable('email')
                ->column(
                    ColumnItem::make()
                        ->name('email')
                        ->type(DataTableColumnType::TEXT)
                        ->props(['muted' => true])
                )
        )
        ->actions(
            RowActionItem::make()
                ->key(DataTableUi::ACTION_VIEW)
                ->label(__('ui.actions.view'))
                ->icon(DataTableUi::ICON_EYE)
                ->variant(DataTableUi::VARIANT_GHOST),
            RowActionItem::make()
                ->key(DataTableUi::ACTION_DELETE)
                ->label(__('ui.actions.delete'))
                ->icon(DataTableUi::ICON_TRASH)
                ->variant(DataTableUi::VARIANT_GHOST)
                ->color(DataTableUi::COLOR_ERROR)
                ->showModal(DataTableUi::MODAL_TYPE_CONFIRM)
                ->execute(function (User $user) {
                    $user->delete();
                })
        )
        ->bulkActions(
            BulkActionItem::make()
                ->key(DataTableUi::BULK_ACTION_DELETE)
                ->label(__('ui.actions.delete_selected'))
                ->icon(DataTableUi::ICON_TRASH)
                ->variant(DataTableUi::VARIANT_GHOST)
                ->color(DataTableUi::COLOR_ERROR)
                ->showModal(DataTableUi::MODAL_TYPE_CONFIRM)
                ->execute(function (Collection $users) {
                    User::whereIn('uuid', $users->pluck('uuid'))->delete();
                })
        )
        ->filters(
            FilterItem::make()
                ->key('role')
                ->label(__('ui.table.users.filters.role'))
                ->placeholder(__('ui.table.users.filters.all_roles'))
                ->type(DataTableFilterType::SELECT)
                ->optionsProvider(RoleOptionsProvider::class)
                ->relationship(['name' => 'roles', 'column' => 'name'])
        );
}
```

#### Row Actions UX

- **Always rendered as kebab dropdown**: Row actions are always shown in a 3-dots (kebab) dropdown menu in the actions column
- **Modal support**: Actions can have `showModal()` configured to open a modal before execution
- **Modal types**: `'blade'`, `'livewire'`, `'html'`, or `'confirm'` (uses confirm-modal component)
- **Execute closures**: Typed closures receive the model instance and execute server-side

#### Bulk Actions UX

- **Buttons if ≤3**: If 3 or fewer bulk actions, they render as separate buttons
- **Dropdown if >3**: If more than 3 bulk actions, they render in a dropdown labeled `__('ui.table.bulk_actions')`
- **Modal support**: Same modal support as row actions

#### Viewport-Only Visibility

Headers and columns support `showInViewPortsOnly(['sm', 'lg'])` which means "show ONLY on these viewports":

```php
HeaderItem::make()
    ->label('Name')
    ->showInViewPortsOnly(['sm', 'lg'])  // Hidden by default, visible only on sm and lg
```

This generates Tailwind classes: `hidden sm:table-cell lg:table-cell`

#### Component Registry System

All cell and filter components are registered via allowlisted registries:

- **`DataTableComponentRegistry`**: Maps `DataTableColumnType` enum → Blade component names
- **`DataTableFilterComponentRegistry`**: Maps `DataTableFilterType` enum → Blade/Livewire component names

**Security**: Only registered components can be rendered, preventing XSS via component injection.

#### Column Types

Available column types (via `DataTableColumnType` enum):
- `TEXT` → `datatable.cells.text`
- `BADGE` → `datatable.cells.badge`
- `BOOLEAN` → `datatable.cells.boolean`
- `DATE` → `datatable.cells.date`
- `DATETIME` → `datatable.cells.datetime`
- `CURRENCY` → `datatable.cells.currency`
- `NUMBER` → `datatable.cells.number`
- `LINK` → `datatable.cells.link`
- `AVATAR` → `datatable.cells.avatar`
- `SAFE_HTML` → `datatable.cells.safe-html` (sanitized via HtmlSanitizer)

#### Filter Types

Available filter types (via `DataTableFilterType` enum):
- `SELECT` → `datatable.filters.select`
- `MULTISELECT` → `datatable.filters.multiselect`
- `BOOLEAN` → `datatable.filters.boolean`
- `DATE_RANGE` → `datatable.filters.date-range`
- `RELATIONSHIP` → `datatable.filters.relationship`

#### Security Rules

1. **Transformer-only values**: All cell values must come from the transformer array. No model access in Blade templates.
2. **SafeHtml sanitization**: When using `DataTableColumnType::SAFE_HTML`, content is sanitized via `HtmlSanitizer` service before rendering.
3. **Component allowlisting**: Only components registered in `DataTableComponentRegistry` or `DataTableFilterComponentRegistry` can be rendered.
4. **No hardcoded strings**: Action keys, types, icons, and component names must use constants/enums from `DataTableUi`, `DataTableColumnType`, or `DataTableFilterType`.

#### Integration with BaseDataTableComponent

The `BaseDataTableComponent` automatically uses the DSL definition if `getDefinition()` returns a `DataTableDefinition`:

```php
// In your Livewire component
protected function getDefinition(): ?DataTableDefinition
{
    return User::datatable();  // Uses HasDataTable trait
}

protected function getModelClass(): string
{
    return User::class;  // Required for action execution
}
```

The component automatically:
- Extracts headers, columns, actions, bulk actions, and filters from the definition
- Executes action closures when actions are clicked
- Opens modals when actions have `showModal()` configured
- Handles modal confirmations and executes closures after confirmation

### View Data Architecture

The DataTable System uses a **View Data class** to separate business logic from presentation, following the separation of concerns principle. All PHP logic is extracted from Blade templates into a dedicated class, leaving templates clean and focused on HTML structure.

**Class**: `DataTableViewData`

**Location**: `app/Services/DataTable/View/DataTableViewData.php`

#### Purpose

The `DataTableViewData` class:
- Accepts all component props via constructor
- Initializes service registries (DataTableComponentRegistry, DataTableFilterComponentRegistry)
- Provides computed properties and methods for all logic
- Processes filters, rows, columns, and headers
- Handles modal configuration and action lookup
- Returns prepared data structures that Blade templates can use directly

#### Key Methods

**Computed Values:**
- `getColumnsCount()` - Calculate total columns (bulk checkbox + data columns + actions)
- `hasActionsPerRow()` - Check if row actions exist
- `getBulkActionsCount()` - Get bulk actions count
- `showBulkActionsDropdown()` - Check if bulk actions should be in dropdown (>3)
- `hasFilters()` - Check if filters exist
- `hasSelected()` - Check if any rows are selected
- `showBulkBar()` - Check if bulk actions bar should be shown
- `hasPaginator()` - Check if paginator has pages

**Processing Methods:**
- `processFilter(array $filter)` - Filter component resolution and safe attributes extraction
- `processRow(array $row, int $index)` - Row UUID validation, selection state, row classes, click attributes
- `processColumn(array $column, array $row)` - Column component resolution, viewport classes, custom render detection
- `processHeaderColumn(array $column)` - Header column processing (hidden, responsive, sortable logic)

**Modal Methods:**
- `getModalStateId(string $actionKey, ?string $rowUuid = null, string $type = 'row')` - Generate Alpine.js modal state ID
- `findActionByKey(string $key, string $type = 'row')` - Find action by key
- `getRowActionModalConfig()` - Get row action modal configuration
- `getBulkActionModalConfig()` - Get bulk action modal configuration

#### Benefits

1. **Separation of Concerns**: Logic separated from presentation
2. **Testability**: View data class can be unit tested independently
3. **Reusability**: Logic can be reused across different contexts
4. **Maintainability**: Easier to modify logic without touching Blade templates
5. **Clean Templates**: Blade files focus only on HTML structure and data display

#### Usage in Components

The `DataTableViewData` class is automatically instantiated in the `<x-datatable>` component and passed to child components:

```blade
{{-- In datatable.blade.php --}}
@php
    $viewData = new DataTableViewData(
        rows: $rows,
        headers: $headers,
        columns: $columns,
        // ... all props
    );
@endphp

<x-table :view-data="$viewData"></x-table>
```

Child components (`<x-table>`, `<x-table.header>`) accept the `viewData` prop and use its methods:

```blade
{{-- In table.blade.php --}}
@forelse ($viewData->getRows() as $row)
    @php
        $rowData = $viewData->processRow($row, $loop->index);
    @endphp
    <tr {!! $rowData['rowClickAttr'] !!} {!! $rowData['rowClassAttr'] !!}>
        {{-- Use processed row data --}}
    </tr>
@endforelse
```

#### Backward Compatibility

Components still accept individual props if `viewData` is not provided, maintaining backward compatibility with existing code.

### Unified Table Component

The DataTable System includes a unified `<x-datatable>` component that handles all rendering logic, making it easy to create consistent tables across the application.

**Component**: `<x-datatable>`

**Location**: `resources/views/components/datatable.blade.php`

**Note**: The component now uses `DataTableViewData` internally to process all data. All PHP logic has been extracted to the view data class, leaving the Blade template clean and focused on presentation.

**Props**:

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `rows` | `array` | `[]` | Array of row data (from transformer) |
| `headers` | `array` | `[]` | Header configuration with labels, sortable flags |
| `columns` | `array` | `[]` | Column configuration with types, render options |
| `actionsPerRow` | `array` | `[]` | Row action buttons configuration |
| `bulkActions` | `array` | `[]` | Bulk action buttons configuration |
| `rowClick` | `string\|null` | `null` | Livewire method name for row click handler |
| `filters` | `array` | `[]` | Applied filters (for future use) |
| `showSearch` | `bool` | `true` | Show search bar (by default) |
| `searchPlaceholder` | `string\|null` | `null` | Custom search placeholder |
| `selected` | `array` | `[]` | Selected row UUIDs |
| `selectPage` | `bool` | `false` | Select all on current page state |
| `selectAll` | `bool` | `false` | Select all across pages state |
| `showBulk` | `bool` | `true` | Show bulk selection checkbox |
| `sortBy` | `string\|null` | `null` | Current sort column |
| `sortDirection` | `string` | `'asc'` | Current sort direction |
| `paginator` | `LengthAwarePaginator\|null` | `null` | Paginator instance |
| `emptyMessage` | `string\|null` | `null` | Custom empty state message |
| `emptyIcon` | `string` | `'user-group'` | Icon for empty state |

**Column Types**:

The unified component supports multiple column types for automatic rendering:

- **`text`**: Plain text (default) - supports `bold` and `muted` options
- **`badge`**: Badge component with `badgeColor` and `badgeSize` options
- **`boolean`**: Boolean values with `trueLabel` and `falseLabel` options
- **`date`**: Date formatting with `format` option (default: 'Y-m-d')
- **`datetime`**: DateTime formatting with `format` option (default: 'Y-m-d H:i')
- **`currency`**: Currency formatting using `formatCurrency()` helper with `currency` option
- **`number`**: Number formatting with `decimals`, `decimalSeparator`, `thousandsSeparator` options
- **`link`**: Link with `href` and optional `external` target
- **`avatar`**: Avatar image with fallback to `defaultAvatar` or generated avatar

**Column Configuration Options**:

```php
[
    'key' => 'name',                    // Required: field key from row data
    'type' => 'text',                   // Optional: column type (default: 'text')
    'hidden' => false,                  // Optional: hide column
    'responsive' => 'md',               // Optional: show only on 'md' and up (e.g., 'md', 'lg')
    'bold' => true,                     // Optional: bold text (for text type)
    'muted' => true,                    // Optional: muted text color (for text type)
    'class' => 'custom-class',          // Optional: custom CSS classes
    'render' => '<span>Custom</span>',  // Optional: custom HTML string (overrides type)
    // Type-specific options:
    'format' => 'Y-m-d',                // For date/datetime types
    'badgeColor' => 'success',           // For badge type
    'badgeSize' => 'sm',                // For badge type
    'trueLabel' => 'Yes',               // For boolean type
    'falseLabel' => 'No',               // For boolean type
    'currency' => 'USD',                 // For currency type
    'decimals' => 2,                    // For number type
    'href' => '/users/{uuid}',          // For link type (supports {uuid} placeholder)
    'external' => false,                // For link type
]
```

**Header Configuration**:

```php
[
    'key' => 'name',                    // Required: column key
    'label' => 'Name',                  // Required: header label
    'sortable' => true,                 // Optional: enable sorting
    'hidden' => false,                  // Optional: hide header
    'responsive' => 'md',               // Optional: show only on 'md' and up
]
```

**Features**:

- **Automatic Search Bar**: Search bar is shown by default (can be disabled with `:show-search="false"`)
- **Reactive Selection**: Selected items are properly reactive using `wire:model.live` with proper `wire:key` handling
- **Column Types**: Automatic rendering based on column type
- **Custom Render**: Support for custom HTML via `render` option
- **Responsive Columns**: Hide/show columns based on viewport using `responsive` option
- **Row Click**: Optional row click handler via `rowClick` prop
- **Bulk Actions**: Automatic bulk action bar when items are selected
- **Pagination**: Automatic pagination display
- **Empty State**: Customizable empty state with icon and message

**Example Usage**:

```blade
<x-datatable
    :rows="$this->rows->items()"
    :headers="$this->getHeaders()"
    :columns="$this->getColumns()"
    :actions-per-row="$this->getRowActions()"
    :bulk-actions="$this->getBulkActions()"
    row-click="rowClicked"
    :selected="$selected"
    :sort-by="$sortBy ?: null"
    :sort-direction="$sortDirection"
    :paginator="$this->rows"
    :show-search="true"
/>
```

### Filter Types

The DataTable System supports multiple filter types:

1. **Select**: Single value selection
2. **Multiselect**: Multiple value selection
3. **Boolean**: True/false filter
4. **Relationship**: Filter by related model
5. **Has Relationship**: Filter by presence/absence of relationship
6. **Date Range**: Filter by date range (from/to)

### Preferences System

The DataTable System includes a comprehensive preferences system that follows the same pattern as `FrontendPreferencesService`:

**Storage:**
- **Guests**: Preferences stored in session only
- **Authenticated Users**: Preferences stored in `users.frontend_preferences` JSON column under keys like `datatable_preferences.users`, synced to session

**Preferences Stored:**
- `search`: Global search query
- `per_page`: Items per page
- `sort`: Sort column and direction
- `filters`: Applied filter values

**Architecture:**
- `DataTablePreferencesService`: Main service (same pattern as `FrontendPreferencesService`)
- `SessionDataTablePreferencesStore`: Session-based storage
- `UserJsonDataTablePreferencesStore`: User JSON column storage
- `DataTablePreferencesStore` interface: Contract for storage implementations

**Behavior:**
- Preferences are automatically loaded on component mount
- Preferences are automatically saved when search, filters, per_page, or sort change
- On login, all DataTable preferences are synced from database to session
- Session is the single source of truth for reads (with automatic DB sync for authenticated users)

**Example Storage Structure:**

```json
{
  "locale": "en_US",
  "theme": "light",
  "datatable_preferences.users": {
    "search": "john",
    "per_page": 25,
    "sort": {
      "column": "name",
      "direction": "asc"
    },
    "filters": {
      "is_active": true,
      "email_verified_at": true
    }
  }
}
```

### Service Registration

All services are registered in `DataTableServiceProvider`:

```php
$this->app->bind(DataTableBuilderInterface::class, DataTableBuilder::class);
$this->app->singleton(DataTablePreferencesService::class);
$this->app->singleton(SearchService::class);
$this->app->singleton(FilterService::class);
// ... etc
```

### Best Practices

1. **Extend BaseDataTableComponent**: Always extend `BaseDataTableComponent` for new DataTable components
2. **Use Unified Component**: Always use `<x-datatable>` component for table rendering - it handles all UI logic automatically
3. **Separate Headers and Columns**: Define `getHeaders()` for table headers and `getColumns()` for body cells with types
4. **Use Column Types**: Leverage built-in column types (badge, boolean, date, etc.) for consistent rendering instead of custom HTML
5. **Use Configs**: Define entity-specific configurations in `Configs/` directory
6. **Use Transformers**: Transform models to arrays in `Transformers/` directory
7. **Use Options Providers**: Provide filter options in `OptionsProviders/` directory
8. **URL Syncing**: Use `#[Url]` attributes for state that should sync with URL
9. **Computed Properties**: Use `#[Computed]` for expensive queries
10. **Authorization**: Override `authorizeAccess()` method in child classes
11. **Reactive Selection**: Selected items are automatically reactive - use `wire:model.live` in the unified component
12. **Row Click Handler**: Define `rowClicked()` method in your component and pass `row-click="rowClicked"` to the unified component

### Current Usage in Project

1. **Users Table** (`resources/views/components/users/⚡table.blade.php`)
    - Extends `BaseDataTableComponent`
    - Uses `UsersDataTableConfig` for configuration
    - Uses `UserDataTableTransformer` for data transformation
    - Supports search, filtering by role and verification status, sorting, and pagination

---

## Component Index

### UI Components (`resources/views/components/ui/`)

-   **Base Modal** (`base-modal.blade.php`) - Flexible modal component with Alpine.js state management
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

### Table Components (`resources/views/components/table/`)

-   **Table** (`table.blade.php`) - Root table wrapper with overflow handling
-   **Header** (`header.blade.php`) - Table header with sortable columns and bulk selection
-   **Body** (`body.blade.php`) - Table body wrapper
-   **Row** (`row.blade.php`) - Table row with click support
-   **Cell** (`cell.blade.php`) - Table cell
-   **Actions** (`actions.blade.php`) - Row action buttons
-   **Bulk** (`bulk.blade.php`) - Bulk actions bar
-   **Pagination** (`pagination.blade.php`) - Pagination controls
-   **Empty** (`empty.blade.php`) - Empty state row

### Settings Components (`resources/views/components/settings/`)

-   **Delete User Form** (`⚡delete-user-form.blade.php`) - User account deletion with confirmation modal
-   **Two-Factor Setup Modal** (`two-factor/⚡setup-modal.blade.php`) - 2FA setup and verification modal
-   **Two-Factor Recovery Codes** (`two-factor/⚡recovery-codes.blade.php`) - 2FA recovery codes display

---

## Changelog

### 2025-01-XX

-   **Table System**: Created comprehensive Blade-first data table system
    -   **New Components**: Created 9 table components (`<x-table>`, `<x-table.header>`, `<x-table.body>`, `<x-table.row>`, `<x-table.cell>`, `<x-table.actions>`, `<x-table.bulk>`, `<x-table.pagination>`, `<x-table.empty>`)
    -   **Architecture**: Strict separation of concerns - Livewire handles data/state, Blade handles rendering
    -   **Features**: Sortable columns, search, pagination, row actions, bulk actions, row click, empty state
    -   **Users Table**: Implemented `UsersTable` Livewire component with full CRUD capabilities
    -   **Authorization**: Protected by `Permissions::VIEW_USERS` permission
    -   **Documentation**: Added comprehensive table system documentation with usage examples
    -   **Tests**: Added authorization tests and core table behavior tests

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
    -   **Modal:** All modals now use `<x-ui.base-modal>` component with Alpine.js state management
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
