## Confirm Modal

**Location:** `resources/views/components/ui/confirm-modal.blade.php`

**Class:** `app/View/Components/Ui/ConfirmModal.php`

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
| `placement`      | `string \| null` | `null`     | Placement passthrough to `<x-ui.base-modal>` (see Base Modal placement options) |
| `openState`      | `string \| null` | `null`     | External Alpine state variable name to control open/close (when provided)       |
| `closeOnOutsideClick` | `bool` | `true`    | Close when clicking the backdrop                                               |
| `closeOnEscape`  | `bool`   | `true`            | Close on ESC                                                                    |
| `backdropTransition` | `bool` | `true`      | Enable backdrop fade transition                                                 |

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

