# DataTable Authorization & Policies

This guide details how to implement secure, high-performance authorization in DataTables using Laravel Policies and the `spatie/laravel-permission` package.

## Core Philosophy

1.  **Strict Policy Usage**: Always use Laravel Policies (`UserPolicy`, `PostPolicy`) for authorization.
2.  **No Render = No Permission**: Components that a user is not authorized to see or interact with must **not be rendered** in the frontend.
3.  **Performance First**: Authorization checks are cached per request to prevent N+1 query issues.

---

## Implementing Actions

Actions support two distinct visibility mechanisms that work together:

### 1. Policy-Based Authorization (`can`)

Use the `can()` method to check Laravel Policies. This is the **primary** security mechanism.

```php
use App\Constants\Auth\PolicyAbilities;

Action::make('edit', __('Edit'))
    ->icon('pencil')
    ->route(fn (User $user) => route('users.edit', $user->uuid))
    // Checks UserPolicy@update($authUser, $rowModel)
    ->can(PolicyAbilities::UPDATE);
```

The policy method receives the authenticated user and the row model:

```php
// app/Policies/UserPolicy.php
public function update(User $user, User $model): bool
{
    if ($user->id === $model->id) {
        return false; // Cannot edit self
    }
    return $user->can(Permissions::EDIT_USERS);
}
```

For class-level checks (like 'create'), pass `false` as the second argument:

```php
Action::make('create', __('Create'))
    ->route(route('users.create'))
    // Checks UserPolicy@create($authUser)
    ->can(PolicyAbilities::CREATE, false);
```

### 2. Conditional Visibility (`show`)

Use the `show()` method for non-permission conditions, such as the state of the row.

```php
Action::make('activate', __('Activate'))
    ->execute(fn ($user) => $user->update(['is_active' => true]))
    // Only show if user is currently inactive
    ->show(fn ($user) => !$user->is_active);
```

### Combining Both

When both are used, **`can()` is checked first**. The `show()` condition is only evaluated if `can()` passes.

```php
Action::make('deactivate', __('Deactivate'))
    ->execute(fn ($user) => $user->update(['is_active' => false]))
    // 1. MUST have permission
    ->can(PolicyAbilities::UPDATE)
    // 2. AND must be currently active
    ->show(fn ($user) => $user->is_active);
```

---

## Bulk Actions

Bulk actions work similarly but check authorization against the model class (not specific instances), as permissions apply to the batch operation.

```php
BulkAction::make('delete', __('Delete Selected'))
    ->execute(fn ($users) => $users->each->delete())
    // Checks UserPolicy@delete($authUser)
    ->can(PolicyAbilities::DELETE);
```

---

## Performance Best Practices

To avoid N+1 queries when checking permissions for 50+ rows:

1.  **Memoization**: The DataTable component uses a `HasDatatableLivewireMemoization` trait to cache the authenticated user and their permissions once per request.
2.  **Eager Loading**: The trait automatically loads `permissions` and `roles.permissions` relationships.

### Do NOT do this:
```php
// ❌ BAD: Triggers a DB query for every single row
->show(fn ($row) => Auth::user()->can('update', $row))
```

### DO this instead:
```php
// ✅ GOOD: Uses optimized policy check
->can(PolicyAbilities::UPDATE)
```

---

## Advanced Abilities

For custom abilities not mapped to standard CRUD operations, define them in `PolicyAbilities` and your Policy class.

```php
// App\Constants\Auth\PolicyAbilities.php
public const IMPERSONATE = 'impersonate';

// App\Policies\UserPolicy.php
public function impersonate(User $user, User $model): bool
{
    return $user->can(Permissions::IMPERSONATE_USERS) && $model->canBeImpersonated();
}

// In DataTable
Action::make('impersonate', __('Impersonate'))
    ->can(PolicyAbilities::IMPERSONATE);
```
