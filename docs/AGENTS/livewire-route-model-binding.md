# Livewire Route Model Binding Convention

## Critical Rule: Use Model Name as Route Parameter

When creating Livewire routes that need to work with a specific model instance, **always use the model name (singular, lowercase) as the route parameter**, not `{uuid}` or `{id}`.

### ✅ Correct Pattern

```php
// routes/web/auth/users.php
Route::livewire('/users/{user}', 'pages::users.show')
    ->name('users.show');

Route::livewire('/users/{user}/edit', 'pages::users.edit')
    ->name('users.edit');

// routes/web/auth/emailTemplates.php
Route::livewire('/{template}', 'pages::emailTemplates.show')
    ->name('emailTemplates.show');

Route::livewire('/{template}/edit', 'pages::emailTemplates.edit')
    ->name('emailTemplates.edit');
```

### ❌ Incorrect Pattern

```php
// DON'T DO THIS - breaks automatic model binding
Route::livewire('/{uuid}', 'pages::emailTemplates.show')
    ->name('emailTemplates.show');

Route::livewire('/{id}/edit', 'pages::emailTemplates.edit')
    ->name('emailTemplates.edit');
```

## Why This Matters

**Livewire's automatic route model binding only works when the route parameter name matches the model name.**

When you use `{user}`, `{template}`, `{role}`, etc., Livewire automatically:
1. Resolves the model from the database using the UUID (via the `HasUuid` trait)
2. Injects it into your `mount()` method
3. Handles all the binding logic for you

When you use `{uuid}` or `{id}`, Livewire treats it as a plain string parameter and you must manually fetch the model.

## Implementation Pattern

### Component Mount Method

```php
// ✅ CORRECT - Automatic binding works
public function mount(User $user): void
{
    $this->authorize(Permissions::VIEW_USERS);
    $this->user = $user->load(['roles', 'teams']);
}

// ❌ WRONG - Manual fetching required
public function mount(string $uuid): void
{
    $this->authorize(Permissions::VIEW_USERS);
    $this->user = User::where('uuid', $uuid)->firstOrFail();
}
```

### Generating Routes

When using the correct pattern, you can pass the model directly to `route()`:

```php
// In views
<a href="{{ route('users.show', $user) }}">View User</a>
<a href="{{ route('emailTemplates.edit', $template) }}">Edit Template</a>

// In controllers/actions
return redirect()->route('users.show', $user);
route('emailTemplates.edit', $template)
```

Laravel automatically uses the UUID because of the `HasUuid` trait's `getRouteKeyName()` method.

## Benefits

1. **Less Code**: No manual model fetching
2. **Consistency**: Same pattern across all resources
3. **Type Safety**: Type-hinted parameters in mount()
4. **Laravel Standard**: Follows Laravel's conventions
5. **Cleaner URLs**: Still uses UUIDs in the actual URL (e.g., `/users/abc-123-def`)

## Summary

- ✅ Route parameter: `{modelName}` (e.g., `{user}`, `{template}`, `{role}`)
- ✅ Mount signature: `mount(ModelClass $modelName)`
- ✅ Route generation: `route('name', $model)`
- ❌ Don't use: `{uuid}`, `{id}` as route parameter names for Livewire routes

This pattern is **mandatory for all Livewire routes** that work with specific model instances.
