## Confirm Modal

**Location:** `resources/views/components/ui/confirm-modal.blade.php`

**Class:** `app/View/Components/Ui/ConfirmModal.php`

**Component Name:** `<x-ui.confirm-modal>`

### Description

A reusable confirmation modal component that provides a consistent way to handle user confirmations throughout the application. It uses a **flattened Alpine.js structure** for maximum reliability and can be triggered via global window events. Supports custom titles, messages, and two methods of callbacks: direct closures or event-driven back-events.

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

#### Confirmation with Event-Based Callback (Recommended)

For more robust communication between components (like the Datatable), use `confirmEvent`. This dispatches a window event when confirmed, avoiding scope issues with stale closures.

```blade
<button @click="$dispatch('confirm-modal', {
    title: 'Delete User',
    message: 'Are you sure?',
    confirmEvent: 'user-deletion-confirmed',
    confirmData: { id: 123 }
})" class="btn btn-error">
    Delete
</button>

{{-- Listen for the event elsewhere --}}
<div x-data="{}" @user-deletion-confirmed.window="console.log('User deleted:', $event.detail.id)">
</div>
```

#### Confirmation with JavaScript Closure (⚠️ CSP Warning)

**Note:** Passing arrow functions (`() => {}`) directly in Blade templates is **blocked** by strict CSP. This pattern is only safe if the dispatch happens entirely within a JavaScript file (e.g., inside an Alpine component's method).

**❌ UNSAFE in Blade:**
```blade
<button @click="$dispatch('confirm-modal', {
    confirmAction: () => { ... } // CSP Error!
})">
```

**✅ SAFE approach:**
Use the `confirmEvent` pattern (see above) or dispatch from a registered JS component.

### Configuration Object

When dispatching the `confirm-modal` event, you can pass a configuration object with the following properties:

| Property        | Type       | Default                 | Description                                                                 |
| --------------- | ---------- | ----------------------- | --------------------------------------------------------------------------- |
| `title`         | `string`   | Translation key default | Title displayed in the modal header                                         |
| `message`       | `string`   | Translation key default | Message displayed in the modal body                                         |
| `confirmLabel`  | `string`   | Translation key default | Label for the confirm button                                                |
| `cancelLabel`   | `string`   | Translation key default | Label for the cancel button                                                 |
| `confirmEvent`  | `string`   | `null`                  | Global event name to dispatch on confirmation (Superior reliability)        |
| `confirmData`   | `any`      | `null`                  | Data to include in the `$event.detail` of the `confirmEvent`                |
| `confirmAction` | `function` | `null`                  | Callback function to execute when confirmed (Legacy/Simple fallback)        |

### Implementation Details

-   **Single-Scope Architecture**: The component uses a flattened `x-data` structure, eliminating `$parent` scope errors common in nested Alpine components.
-   **Event-Driven Communication**: Listens for `confirm-modal` on the `window` level.
-   **Execution Priority**: If `confirmEvent` is provided, it dispatches the event first; then it executes `confirmAction` if provided.
-   **Translation Integration**: Uses standard translation keys for all default UI text.

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

1. **Include Once:** Include `<x-ui.confirm-modal />` once in your app layout (already included in `app.blade.php`).
2. **Prefer Events:** Use `confirmEvent` rather than `confirmAction` closures for better reliability in complex components.
3. **Event Propagation:** Use `@click.stop` when triggering from nested elements to prevent accidental double-triggers.
4. **Contextual Messaging:** Always override the `message` to be specific to the action (e.g., "Are you sure you want to delete the user 'John Doe'?").
5. **Color Semantics:** Use `error` (default) for destructive actions (Delete, Remove) and `primary` or `success` for constructive ones.

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

## History

### Universal Confirmation Modal (2025-01-XX)
- **Inception**: Created `<x-ui.confirm-modal>` as a reusable component for all confirmation dialogs.
- **Alpine.js Integration**: Orchestrated to listen for global `confirm-modal` events for easy triggering from any component.
- **Migration**: Replaced native browser `confirm()` calls in the Notification Center with this standardized modal.

