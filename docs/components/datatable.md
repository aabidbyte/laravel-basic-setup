# DataTable Component

## Overview

The DataTable component provides a powerful, flexible way to display tabular data with built-in support for:
- ✅ Sorting and searching with highlighting
- ✅ Filtering with relationships and active filter badges
- ✅ Row and bulk actions with confirmation modals
- ✅ Pagination with per-page selector
- ✅ Automatic relationship joins
- ✅ Alpine.js-driven UI state
- ✅ Livewire 4 features
- ✅ Reactive selection management

## Architecture

The DataTable system uses a self-rendering component-based approach with Alpine.js for frontend reactivity:

```
Livewire Component (extends DataTableComponent)
    ↓ (only contains configuration)
Shared Template (resources/views/components/datatable.blade.php)
    ↓
DataTableQueryBuilder (auto-joins, search, filter, sort)
    ↓
Alpine.js Component (UI state management)
```

**Key Benefits:**
- ✅ **Separation of Concerns**: Configuration separate from presentation
- ✅ **DRY**: One template for all datatables
- ✅ **Maintainability**: Fix bugs in one place
- ✅ **Consistency**: All datatables look and behave the same
- ✅ **Reusability**: Easy to add new datatables

## Quick Start

### 1. Create a DataTable Component

**File:** `resources/views/components/users/table.blade.php`

```php
<?php

declare(strict_types=1);

use App\Livewire\DataTableComponent;
use App\Services\DataTable\Builders\{Column, Filter, Action, BulkAction};
use Illuminate\Database\Eloquent\Builder;

new class extends DataTableComponent
{
    protected function baseQuery(): Builder
    {
        return User::query()
            ->with('roles')
            ->select('users.*');
    }

    protected function columns(): array
    {
        return [
            Column::make(__('Name'), 'name')
                ->sortable()
                ->searchable(),

            Column::make(__('Email'), 'email')
                ->sortable()
                ->searchable(),
        ];
    }
};
```

**That's it!** No HTML needed - the shared template handles all rendering.

### 2. Use in a Page

**File:** `resources/views/pages/users/index.blade.php`

```blade
<x-layouts.app>
    <div class="container mx-auto px-4 py-8">
        <livewire:users.table />
    </div>
</x-layouts.app>
```

### 3. Required Methods

| Method | Required | Returns | Description |
|--------|----------|---------|-------------|
| `baseQuery()` | ✅ Yes | `Builder` | Base Eloquent query |
| `columns()` | ✅ Yes | `array<Column>` | Column definitions |
| `filters()` | ❌ No | `array<Filter>` | Filter definitions |
| `rowActions()` | ❌ No | `array<Action>` | Row-level actions |
| `bulkActions()` | ❌ No | `array<BulkAction>` | Bulk actions |

### 4. Available Properties

The `DataTableComponent` base class provides these Livewire properties:

```php
#[Url] public string $search = '';              // Search term
#[Url] public string $sortBy = '';              // Sort column
#[Url] public string $sortDirection = 'asc';    // Sort direction
#[Url] public int $perPage = 15;                // Items per page
public array $filters = [];                     // Filter values
public array $selected = [];                    // Selected UUIDs
public bool $selectPage = false;                // Page selection state
```

## Column API

### Basic Column

```php
Column::make(__('Name'), 'name')
```

The second parameter defaults to the snake_case of the label if omitted.

### Sortable Column

```php
Column::make(__('Email'), 'email')
    ->sortable()
```

**Custom sort logic:**

```php
Column::make(__('Name'), 'name')
    ->sortable(fn(Builder $query, string $direction) => 
        $query->orderBy('first_name', $direction)
              ->orderBy('last_name', $direction)
    )
```

### Searchable Column

```php
Column::make(__('Name'), 'name')
    ->searchable()
```

**Custom search logic:**

```php
Column::make(__('Name'), 'name')
    ->searchable(fn(Builder $query, string $search) => 
        $query->orWhere('first_name', 'LIKE', "%{$search}%")
              ->orWhere('last_name', 'LIKE', "%{$search}%")
    )
```

### Formatting

**Simple formatting:**

```php
Column::make(__('Status'), 'is_active')
    ->format(fn($value) => $value ? '✓ Active' : '✗ Inactive')
```

**With HTML:**

```php
Column::make(__('Name'), 'name')
    ->format(fn($value) => "<strong>{$value}</strong>")
    ->html()
```

**Accessing the row:**

```php
Column::make(__('Status'), 'is_active')
    ->format(fn($value, $row) => $value 
        ? '<span class="badge badge-success">'.__('Active').'</span>'
        : '<span class="badge badge-ghost">'.__('Inactive').'</span>')
    ->html()
```

### Custom View

```php
Column::make(__('Avatar'), 'avatar_url')
    ->view('components.users.avatar')
```

The view receives `$value`, `$row`, and `$column` variables.

### Non-Database Column (Label Callback)

For computed values not directly from the database:

```php
Column::make(__('Full Name'))
    ->label(fn($row) => $row->first_name . ' ' . $row->last_name)
```

### Relationship Column (Auto-Join)

The system automatically detects and joins relationships:

```php
Column::make(__('City'), 'address.city.name')
// Automatically joins: users -> address -> city
```

**How it works:**
1. Parses dot notation to detect relationships
2. Automatically joins related tables
3. Handles `BelongsTo`, `HasOne`, `HasMany`, and `BelongsToMany`

### Conditional Visibility

```php
Column::make(__('Admin Notes'), 'admin_notes')
    ->hidden(fn($row) => !Auth::user()?->isAdmin())
```

### CSS Classes

```php
Column::make(__('Email'), 'email')
    ->class('text-base-content/70 text-sm')
```

## Filter API

### Select Filter

```php
Filter::make('is_active', __('Status'))
    ->type('select')
    ->placeholder(__('All Statuses'))
    ->options([
        ['value' => '1', 'label' => __('Active')],
        ['value' => '0', 'label' => __('Inactive')],
    ])
```

### Value Mapping

Transform filter values before querying:

```php
Filter::make('is_active', __('Status'))
    ->type('select')
    ->options([...])
    ->valueMapping(['1' => true, '0' => false])
```

**Special mappings:**
- `'not_null'` - `whereNotNull(field)`
- `'null'` - `whereNull(field)`

### Relationship Filter

```php
Filter::make('role', __('Role'))
    ->type('select')
    ->relationship('roles', 'name')
    ->optionsCallback(fn() => Role::pluck('name', 'name')->map(fn($name, $key) => [
        'value' => $key,
        'label' => $name,
    ])->values()->toArray())
```

### Field Mapping

Use a different field name in the query:

```php
Filter::make('status', __('Status'))
    ->fieldMapping('is_active')
    ->options([...])
```

### Custom Filter Logic

```php
Filter::make('created_at', __('Created'))
    ->type('date_range')
    ->execute(fn($query, $value) => 
        $query->whereBetween('created_at', [$value['from'], $value['to']])
    )
```

### Conditional Visibility

```php
Filter::make('admin_only', __('Admin Filter'))
    ->show(fn() => Auth::user()?->isAdmin() ?? false)
    ->options([...])
```

## Action API

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

Simple confirmation message:

```php
Action::make('delete', __('Delete'))
    ->icon('trash')
    ->confirm(__('Are you sure you want to delete this user?'))
    ->execute(fn($user) => $user->delete())
```

Advanced confirmation with closure (returns config array):

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

Custom confirmation view:

```php
Action::make('delete', __('Delete'))
    ->icon('trash')
    ->confirmView('modals.confirm-delete', ['message' => 'Custom message'])
    ->execute(fn($user) => $user->delete())
```

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

## Bulk Action API

### Basic Bulk Action

```php
BulkAction::make('activate', __('Activate Selected'))
    ->icon('check')
    ->execute(fn($users) => $users->each->update(['is_active' => true]))
```

**Note:** The closure receives a `Collection` of models, not individual models.

### With Confirmation

Simple confirmation:

```php
BulkAction::make('delete', __('Delete Selected'))
    ->icon('trash')
    ->color('error')
    ->confirm(__('Delete all selected users?'))
    ->execute(fn($users) => $users->each->delete())
```

Advanced confirmation with closure:

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

### Conditional Visibility

```php
BulkAction::make('admin_action', __('Admin Action'))
    ->show(fn() => Auth::user()?->isAdmin() ?? false)
    ->execute(fn($users) => /* ... */)
```

## Enhanced UI Features

### Active Filter Badges

Active filters are automatically displayed as badges above the table with:
- Filter label and selected value
- Individual remove buttons (×)
- "Clear All" button when multiple filters are active

No additional configuration needed - filters are automatically tracked and displayed.

### Search Highlighting

Search terms are automatically highlighted in searchable columns with a yellow background. The highlighting:
- Only applies to searchable columns
- Preserves HTML safety (escapes before highlighting)
- Uses case-insensitive matching
- Wraps matches in `<mark>` tags with custom styling

### Per-Page Selector

A dropdown selector is automatically included in the pagination area allowing users to choose:
- 10, 15, 25, 50, or 100 items per page
- Selection persists in URL parameters
- Automatically resets to page 1 when changed

### Reactive Selection

Selection state is fully reactive and automatically:
- Clears when searching, filtering, sorting, or changing pages
- Updates the "select all" checkbox based on current page
- Tracks only currently visible rows
- Syncs between Alpine.js and Livewire

## Alpine.js Integration

### Important Conventions

Following `docs/alpinejs/livewire-integration.md`:

⚠️ **DO NOT pass `$wire` as a parameter** - It's reactive and automatically available
✅ **Use `$wire.$entangle()` for bidirectional sync** - Initialize in `init()`
✅ **Validate `$wire` before calling methods** - Check existence and type

### Usage

```blade
{{-- Correct: $wire is automatically available --}}
<div x-data="dataTable(@js(['pageUuids' => $this->rows->pluck('uuid')->toArray()]))">
    {{-- Your table HTML --}}
</div>
```

### Available State

```javascript
{
    selected: [],        // Array of selected UUIDs (entangled with Livewire)
    selectPage: false,   // Boolean for current page selection
    openFilters: false,  // Boolean for filter panel visibility
    activeModal: null,   // String for active modal name
    hoveredRow: null,    // String for hovered row UUID
}
```

### Available Methods

```javascript
{
    toggleSelectPage()      // Toggle selection of all rows on current page
    isSelected(uuid)        // Check if a row is selected
    toggleRow(uuid)         // Toggle selection of a single row
    clearSelection()        // Clear all selections
    toggleFilters()         // Toggle filter panel
    closeFilters()          // Close filter panel
    openModal(name)         // Open a modal
    closeModal()            // Close active modal
    handleRowClick(uuid)    // Handle row click (calls $wire.rowClicked)
    setHoveredRow(uuid)     // Set hovered row
    isRowHovered(uuid)      // Check if a row is hovered
}
```

### Computed Properties

```javascript
{
    get selectedCount()  // Number of selected rows
    get hasSelection()   // Boolean - any rows selected
}
```

### Example HTML

```blade
<div x-data="dataTable(@js(['pageUuids' => $this->rows->pluck('uuid')->toArray()]))">
    {{-- Bulk actions bar (Alpine controls visibility) --}}
    <div x-show="hasSelection" class="mb-4">
        <span x-text="`${selectedCount} selected`"></span>
        <button @click="clearSelection()">Clear</button>
    </div>

    {{-- Table --}}
    <table class="table">
        <thead>
            <tr>
                <th>
                    <input 
                        type="checkbox" 
                        @click="toggleSelectPage()" 
                        :checked="selectPage"
                        class="checkbox"
                    >
                </th>
                {{-- ... --}}
            </tr>
        </thead>
        <tbody>
            @foreach($this->rows as $row)
                <tr 
                    wire:key="row-{{ $row->uuid }}"
                    @click="handleRowClick('{{ $row->uuid }}')"
                    :class="{ 'bg-base-200': isSelected('{{ $row->uuid }}') }"
                    class="cursor-pointer hover:bg-base-200"
                >
                    <td @click.stop>
                        <input 
                            type="checkbox" 
                            :checked="isSelected('{{ $row->uuid }}')" 
                            @click="toggleRow('{{ $row->uuid }}')"
                            class="checkbox"
                        >
                    </td>
                    {{-- ... --}}
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

## Advanced Features

### Automatic Relationship Joins

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

### Custom Query Building

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

### Row Click Behavior

Handle row clicks in the component:

```php
public function rowClicked(string $uuid): void
{
    $user = User::where('uuid', $uuid)->first();
    if ($user !== null) {
        $this->redirect(route('users.show', $user));
    }
}
```

### URL State Persistence

Search, sort, filters, and pagination are automatically synced with URL query parameters using Livewire's `#[Url]` attribute:

```
/users?search=john&sort=name&direction=asc&per_page=25
```

This allows users to:
- Bookmark filtered views
- Share filtered URLs
- Use browser back/forward buttons

## Testing

Test DataTables with Livewire's testing helpers:

```php
use Livewire\Livewire;

test('search filters results', function () {
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    Livewire::actingAs($this->user)
        ->test('users.table')
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

test('sort toggles direction', function () {
    $component = Livewire::actingAs($this->user)
        ->test('users.table');

    $component->call('sortBy', 'name')
        ->assertSet('sortBy', 'name')
        ->assertSet('sortDirection', 'asc');

    $component->call('sortBy', 'name')
        ->assertSet('sortDirection', 'desc');
});

test('bulk select page', function () {
    User::factory()->count(5)->create();

    Livewire::actingAs($this->user)
        ->test('users.table')
        ->call('toggleSelectPage')
        ->assertSet('selectPage', true);
});
```

## Best Practices

### 1. Use Type Hints

Always use proper type hints for IDE support:

```php
protected function columns(): array
{
    return [
        Column::make(__('Name'), 'name'),
    ];
}
```

### 2. Eager Load Relationships

Load relationships in `baseQuery()` to avoid N+1 queries:

```php
protected function baseQuery(): Builder
{
    return User::query()
        ->with(['roles', 'teams'])
        ->select('users.*');
}
```

### 3. Use Column Classes

Add classes for consistent styling:

```php
Column::make(__('Email'), 'email')
    ->class('text-base-content/70')
```

### 4. Validate Actions

Always check permissions in action visibility:

```php
Action::make('edit', __('Edit'))
    ->show(fn($user) => Auth::user()?->can('update', $user) ?? false)
```

### 5. Optimize Queries

Use `select()` to only fetch needed columns:

```php
protected function baseQuery(): Builder
{
    return User::query()
        ->select(['users.id', 'users.name', 'users.email'])
        ->with('roles:id,name');
}
```

## Troubleshooting

### Routes Not Defined

If you see "Route [users.show] not defined":

```php
// Add conditional check
if (\Illuminate\Support\Facades\Route::has('users.show')) {
    $actions[] = Action::make('view', __('View'))
        ->route(fn($user) => route('users.show', $user));
}
```

### Alpine Component Not Working

Ensure `resources/js/app.js` registers the component:

```javascript
import { dataTable } from "./alpine-components/datatable.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("dataTable", dataTable);
});
```

### Tests Failing

Run specific DataTable tests:

```bash
php artisan test --filter=UsersTable
```

## See Also

- [Migration Guide](../datatable-migration-guide.md) - Migrating from legacy system
- [Alpine.js Documentation](../alpinejs/index.md) - Alpine.js conventions
- [Livewire 4 Documentation](../livewire-4/index.md) - Livewire 4 features

