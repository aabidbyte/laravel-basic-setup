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
| `confirmVariant` | `string` | `'solid'`         | Button visual style for the confirm button          |
| `confirmColor`   | `string` | `'error'`         | Button semantic color for the confirm button        |
| `cancelVariant`  | `string` | `'ghost'`         | Button visual style for the cancel button           |
| `cancelColor`   | `string` | `null`            | Button semantic color for the cancel button         |
| `maxWidth`       | `string` | `'md'`            | Maximum width of the modal (DaisyUI sizes: `xs`, `sm`, `md`, `lg`, `xl`, etc.) |
| `placement`      | `string \| null` | `null`     | Placement passthrough to `<x-ui.base-modal>` (see Base Modal placement options) |
| `openState`      | `string \| null` | `null`     | External Alpine state variable name to control open/close (when provided)       |
| `closeOnOutsideClick` | `bool` | `true`    | Close when clicking the backdrop                                               |
| `closeOnEscape`  | `bool`   | `true`            | Close on ESC                                                                    |
| `backdropTransition` | `bool` | `true`      | Enable backdrop fade transition                                                 |

### Usage
**⚠️ CRITICAL: CSP & Global Inclusion**
1.  **Do NOT include this component manually.** It is globally included in the application layout (`app.blade.php`). Adding it to your page will cause double modals.
2.  **Do NOT use inline callbacks.** Passing arrow functions (e.g., `confirmAction: () => ...`) in Blade templates violates Content Security Policy (CSP). You **MUST** use the event-based pattern.

### Recommended Pattern: Event Dispatch

The correct way to use the modal is to dispatch a `confirm-modal` event from your button, and listen for a specific confirmation event on your page root.

#### 1. The Trigger Button
Inside your component (e.g., inside a table loop or card):

```blade
<x-ui.button @click="$dispatch('confirm-modal', {
                 title: '{{ __('actions.delete') }}',
                 message: '{{ __('actions.confirm_delete') }}',
                 confirmEvent: 'confirm-delete-user',
                 confirmData: { id: {{ $user->id }} }
             })"
             color="error">
    {{ __('actions.delete') }}
</x-ui.button>
```

#### 2. The Event Listener
At the top of your Blade file (or on the root definition):

```blade
<section class="max-w-7xl mx-auto"
         @confirm-delete-user.window="$wire.deleteUser($event.detail.id)">
    {{-- Page Content --}}
</section>
```

#### 3. The Livewire Method
In your Livewire component:

```php
public function deleteUser($id)
{
    $user = User::findOrFail($id);
    $user->delete();
}
```

### Configuration Object

| Property        | Type     | Default             | Description                                                                 |
| --------------- | -------- | ------------------- | --------------------------------------------------------------------------- |
| `title`         | `string` | 'Confirm Action'    | Title displayed in the modal header                                         |
| `message`       | `string` | 'Are you sure...?'  | Message displayed in the modal body                                         |
| `confirmEvent`  | `string` | `null`              | **REQUIRED**. The custom window event to dispatch when confirmed.           |
| `confirmData`   | `object` | `null`              | Data object to pass to the event (e.g., `{ id: 1 }`).                       |
| `confirmAction` | `fn`     | `null`              | **FORBIDDEN IN BLADE**. Only use inside pure JS files safely.               |
| `confirmColor`  | `string` | 'error'             | Semantic color: `primary`, `secondary`, `error`, `success`, `info`, `warning` |
| `confirmLabel`  | `string` | 'Confirm'           | Button label.                                                               |
| `cancelLabel`   | `string` | 'Cancel'            | Cancel button label.                                                        |

---

## History

### Universal Confirmation Modal (2025-01-XX)
- **Refactor**: Enforced event-based pattern for CSP compliance.
- **Global**: Included via `layout/app.blade.php`.


