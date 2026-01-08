# Action API

## Row Actions

### Navigate to Route

```php
Action::make('view', __('View'))
    ->icon('eye')
    ->route(fn($user) => route('users.show', $user))
```

### Execute Server-Side

```php
Action::make('activate', __('Activate'))
    ->icon('check')
    ->execute(fn($user) => $user->update(['is_active' => true]))
```

### Show Modal

```php
Action::make('delete', __('Delete'))
    ->icon('trash')
    ->modal('modals.confirm-delete')
    ->execute(fn($user) => $user->delete())
```

### With Confirmation

Actions with confirmation automatically show a modal before execution. Three confirmation types are supported:

**1. Simple confirmation message:**

```php
Action::make('delete', __('Delete'))
    ->icon('trash')
    ->confirm(__('Are you sure you want to delete this user?'))
    ->execute(fn($user) => $user->delete())
```

**2. Advanced confirmation with closure (returns config array):**

```php
Action::make('delete', __('Delete'))
    ->icon('trash')
    ->confirm(fn($user) => [
        'title' => __('Delete User'),
        'content' => __('Are you sure you want to delete :name?', ['name' => $user->name]),
        'confirmText' => __('Yes, Delete'),
        'cancelText' => __('Cancel'),
    ])
    ->execute(fn($user) => $user->delete())
```

**3. Custom confirmation view:**

```php
Action::make('delete', __('Delete'))
    ->icon('trash')
    ->confirmView('modals.confirm-delete', ['message' => 'Custom message'])
    ->execute(fn($user) => $user->delete())
```

**Note:** Confirmation modals work for both row actions and bulk actions. The modal is automatically displayed when an action with `confirm()` is triggered, and the action only executes after the user confirms.

### Conditional Visibility

```php
Action::make('edit', __('Edit'))
    ->icon('pencil')
    ->route(fn($user) => route('users.edit', $user))
    ->show(fn($user) => Auth::user()?->can('update', $user) ?? false)
```

### Styling

```php
Action::make('delete', __('Delete'))
    ->icon('trash')
    ->variant('ghost')     // ghost, primary, secondary, etc.
    ->color('error')       // error, warning, success, etc.
```

## Modal Actions

DataTable supports two types of dynamic modal actions that can render either a Blade view or a Livewire component. Modals use a **global Livewire SFC component** for centralized state management.

**1. Blade Modal:**

```php
Action::make('view', __('View Details'))
    ->icon('eye')
    ->bladeModal('components.users.view-modal', fn (User $user) => ['userUuid' => $user->uuid])
```

**2. Livewire Modal:**

```php
Action::make('edit', __('Fast Edit'))
    ->icon('pencil')
    ->livewireModal('modals.user-edit-form', fn (User $user) => ['userUuid' => $user->uuid])
```

### Global Modal Architecture

The modal system uses a global Livewire Class component (`App\Livewire\DataTable\ActionModal`) that:
- Uses `<x-ui.base-modal>` for consistent styling
- Exposes `modalIsOpen` to child views via Alpine.js
- Provides immediate loading UX with `<x-ui.loading>` component

```
User clicks action → datatable-modal-loading event → Modal shows spinner
                  → Livewire request → Server sets modalView
                  → Livewire.hook('morph.updated') → Spinner hidden, content shown
```

### UUID Pattern for Props

> [!IMPORTANT]
> **Always pass UUIDs, not model instances**, in modal props. Models lose Eloquent methods after serialization.

```php
// ❌ Bad - model loses methods after serialization
->bladeModal('view-modal', fn (User $user) => ['user' => $user])

// ✅ Good - re-fetch in Blade view
->bladeModal('view-modal', fn (User $user) => ['userUuid' => $user->uuid])
```

In your Blade view, re-fetch the model:

```blade
@php
    $user = $user ?? \App\Models\User::where('uuid', $userUuid)->first();
@endphp

<div>
    <p>{{ $user->name }}</p>
    <p>Created {{ $user->created_at->diffForHumans() }}</p>
    
    <button @click="modalIsOpen = false">Close</button>
</div>
```

### Loading UX

Modals show a loading spinner immediately when triggered, before the server response arrives. This is achieved by:

1. Action buttons dispatch `datatable-modal-loading` event before Livewire request
2. Alpine listener shows modal with spinner immediately
3. `Livewire.hook('morph.updated')` hides spinner when content is ready

For row clicks, the loading event is only dispatched if `rowClickOpensModal()` returns true.

> [!TIP]
> **Action Feedback**: Use `NotificationBuilder` within the `execute()` closure to provide visual feedback to the user after an action completes.
>
> ```php
> use App\Services\Notifications\NotificationBuilder;
> 
> ->execute(function (User $user) {
>     $user->delete();
>     NotificationBuilder::make()
>         ->title(__('User deleted successfully'))
>         ->success()
>         ->send();
> })
> ```

### Unified Modal Architecture

All DataTable modals, whether for custom actions (view/edit) or simple confirmations (delete), are handled by the single `ActionModal` component. This ensures consistent styling, behavior, and loading states across the entire system.

## Bulk Action API

### Basic Bulk Action

```php
BulkAction::make('activate', __('Activate Selected'))
    ->icon('check')
    ->execute(fn($users) => $users->each->update(['is_active' => true]))
```

**Note:** The closure receives a `Collection` of models, not individual models.

### With Confirmation

Bulk actions support the same confirmation options as row actions:

**Simple confirmation:**

```php
BulkAction::make('delete', __('Delete Selected'))
    ->icon('trash')
    ->color('error')
    ->confirm(__('Delete all selected users?'))
    ->execute(fn($users) => $users->each->delete())
```

**Advanced confirmation with closure:**

```php
BulkAction::make('delete', __('Delete Selected'))
    ->icon('trash')
    ->color('error')
    ->confirm(fn($users) => [
        'title' => __('Delete :count Users', ['count' => $users->count()]),
        'content' => __('This action cannot be undone.'),
        'confirmText' => __('Delete All'),
        'cancelText' => __('Cancel'),
    ])
    ->execute(fn($users) => $users->each->delete())
```

**Custom confirmation view:**

```php
BulkAction::make('delete', __('Delete Selected'))
    ->icon('trash')
    ->color('error')
    ->confirmView('modals.confirm-bulk-delete', ['count' => $users->count()])
    ->execute(fn($users) => $users->each->delete())
```

### Conditional Visibility

```php
BulkAction::make('admin_action', __('Admin Action'))
    ->show(fn() => Auth::user()?->isAdmin() ?? false)
    ->execute(fn($users) => /* ... */)
```
