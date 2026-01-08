# Advanced Features

## Automatic Relationship Joins

The system automatically detects relationships from dot notation:

```php
// User model
Column::make(__('City'), 'address.city.name')

// Results in automatic joins:
// users LEFT JOIN addresses ON users.id = addresses.user_id
// addresses LEFT JOIN cities ON addresses.city_id = cities.id
```

**Supported relationship types:**
- `BelongsTo`
- `HasOne`
- `HasMany`
- `BelongsToMany` (includes pivot table joins)

## Custom Query Building

Override the base query for complex scenarios:

```php
protected function baseQuery(): Builder
{
    return User::query()
        ->with(['roles', 'teams'])
        ->select('users.*')
        ->when(Auth::user()->isNotAdmin(), fn($q) => 
            $q->where('team_id', Auth::user()->team_id)
        );
}
```

## Row Click Behavior

Row clicks can be handled by returning an `Action` from the `rowClick()` method. This enables modal dialogs, route navigation, or custom execution using the same fluent API as row actions.

> [!TIP]
> For row-click actions, `Action::make()` can be called without arguments since the action key and label aren't displayed.

**Navigate to Route on Row Click (Recommended):**

```php
use App\Services\DataTable\Builders\Action;

public function rowClick(string $uuid): ?Action
{
    return Action::make()
        ->route('users.show', $uuid);
}
```

**Open Modal on Row Click:**

```php
public function rowClick(string $uuid): ?Action
{
    return Action::make()
        ->bladeModal('components.users.view-modal', fn (User $user) => ['userUuid' => $user->uuid]);
}
```

**Execute Custom Action on Row Click:**

```php
public function rowClick(string $uuid): ?Action
{
    return Action::make()
        ->execute(fn (User $user) => $user->update(['is_active' => !$user->is_active]));
}
```

**Route Method Signatures:**

The `route()` method supports multiple signatures for flexibility:

```php
// Direct URL
->route('/users/123')

// Route name with parameters (Laravel style)
->route('users.show', $uuid)
->route('users.show', ['user' => $uuid])

// Closure for dynamic routes (receives model)
->route(fn (User $user) => route('users.show', $user))
```

**How it Works:**
- The `rowClick()` method receives the row's UUID and returns an `Action` instance (or `null` to disable row click)
- The base `handleRowClick()` method processes the action: opening modals, redirecting to routes, or executing closures
- The model is automatically resolved from the UUID using `findModelByUuid()` (checks current page first)
- Rows are automatically styled as clickable when `rowClick()` is overridden

## State Management

Search, sort, filters, and pagination state is maintained in Livewire component state (not in URL query strings):

- ✅ **Clean URLs** - Browser URL stays clean (e.g., `/users`) without query strings
- ✅ **State Persistence** - All state maintained server-side during component lifecycle
- ✅ **Share Functionality** - Use the share button to generate URLs with query strings for sharing
- ✅ **Better Performance** - No URL manipulation overhead

**Share URLs** are generated using `getShareUrl()` method:
```
/users?search=john&sort=name&direction=asc&per_page=25&filters[role]=admin&page=2
```

This allows users to:
- Share filtered/sorted views via share button
- Copy URLs with all current state
- Restore exact view when visiting shared URL

## Preferences Integration

The DataTable component automatically integrates with the FrontendPreferences system to persist user preferences for each datatable entity. Preferences are stored per datatable (identified by the component's full class name) and include:

- **Sorting** (`sortBy`, `sortDirection`) - Remembers the last sort column and direction
- **Per Page** (`perPage`) - Remembers the selected items per page
- **Filters** (`filters`) - Remembers all active filter values

**Note:** Search term is intentionally NOT stored as a preference to allow fresh searches on each visit.

**How it works:**
1. When a datatable component mounts, it automatically loads saved preferences from the FrontendPreferences system
2. When users change sorting, per page, or filters, preferences are automatically saved
3. Preferences are stored in the user's database (for authenticated users) or session (for guests)
4. Each datatable maintains separate preferences (e.g., `UserTable` and `ProductTable` have independent preferences)

**Storage Structure:**
```php
[
    'datatables' => [
        'App\Livewire\Tables\UserTable' => [
            'sortBy' => 'name',
            'sortDirection' => 'asc',
            'perPage' => 25,
            'filters' => ['role' => 'admin', 'is_active' => true],
        ],
        // ... other datatables
    ],
]
```

**Automatic Behavior:**
- Preferences load automatically on component mount
- Preferences save automatically when state changes (sort, filters, per page)
- No additional configuration needed - works out of the box for all datatables
- Backward compatible - existing datatables continue to work without changes
