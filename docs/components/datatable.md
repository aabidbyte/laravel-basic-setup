# DataTable Component

## Overview

The DataTable component provides a powerful, flexible way to display tabular data with built-in support for:
- ✅ Sorting and searching with highlighting
- ✅ Filtering with relationships and active filter badges
- ✅ Row and bulk actions with confirmation modals
- ✅ Pagination with per-page selector
- ✅ Automatic relationship joins
- ✅ Alpine.js-driven UI state (filters panel, modals)
- ✅ Livewire 4 features
- ✅ Server-side selection management (optimized queries)

## Architecture

The DataTable system uses a modular, trait-based architecture to adhere to SOLID principles and ensure maintainability:

```
Livewire Component (extends Datatable)
    ↓ (modular responsibilities via traits)
    - HasActions (row/bulk actions, modals)
    - HasFilters (filter state, rendering)
    - HasPagination (per-page, navigation)
    - HasPreferences (user preference persistence)
    - HasQueryParameters (URL state handling)
    - HasRendering (header, row, column rendering)
    - HasSelection (row selection management)
    - HasSorting (sort column/direction logic)
    ↓
Shared Template (resources/views/components/datatable.blade.php)
    ↓ (orchestrates sub-views via backend methods)
Modular Sub-views (resources/views/components/datatable/*.blade.php)
    ↓
DataTableQueryBuilder (auto-joins, search, filter, sort)
    ↓
Alpine.js Component (UI-only: filters panel, modals)
```

**Key Benefits:**
- ✅ **SOLID**: Clear separation of responsibilities into focused traits
- ✅ **Readability**: Smaller, more manageable code files
- ✅ **Testability**: Logic isolated in traits is easier to test
- ✅ **DRY**: Shared logic reused across all datatables
- ✅ **Consistency**: Unified behavior for sorting, filtering, and selection

**Note:** This component-based architecture replaced the previous trait-based approach (`WithDataTable` trait). All DataTable components now extend the `Datatable` base class, which provides all the functionality previously in the trait. Individual datatables only contain PHP configuration - no HTML is needed in component files.

## Quick Start

### 1. Create a DataTable Component

**File:** `app/Livewire/Tables/UserTable.php`

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Livewire\Datatable;
use App\Services\DataTable\Builders\{Column, Filter, Action, BulkAction};
use Illuminate\Database\Eloquent\Builder;

class UserTable extends Datatable
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
}
```

**That's it!** No HTML needed - the shared template handles all rendering.

### 2. Use in a Page

**File:** `resources/views/pages/users/index.blade.php`

```blade
<x-layouts.app>
    <div class="container mx-auto px-4 py-8">
        <livewire:tables.user-table />
    </div>
</x-layouts.app>
```

### 3. Required Methods

| Method | Required | Returns | Description |
|--------|----------|---------|-------------|
| `baseQuery()` | ✅ Yes | `Builder` | Base Eloquent query |
| `columns()` | ✅ Yes | `array<Column>` | Column definitions |
| `getFilterDefinitions()` | ❌ No | `array<Filter>` | Filter definitions |
| `rowActions()` | ❌ No | `array<Action>` | Row-level actions |
| `bulkActions()` | ❌ No | `array<BulkAction>` | Bulk actions |

### 4. Available Properties

The `Datatable` base class provides these Livewire properties:

```php
public string $search = '';              // Search term
public string $sortBy = '';              // Sort column
public string $sortDirection = 'asc';    // Sort direction
public int $perPage = 15;                // Items per page
public array $filters = [];                     // Filter values
public array $selected = [];                     // Selected UUIDs
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

**Secure by Default:** All column values are automatically escaped using `e()` to prevent XSS. You must explicitly call `->html()` if you intend to render HTML content within a column.

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

### Component Rendering (Badges, Buttons, etc.)

Render UI components (badges, buttons, etc.) directly in columns using the `content()` and `type()` methods:

```php
use App\Constants\DataTable\DataTableUi;

Column::make(__('Roles'), 'roles_for_datatable')
    ->content(fn (User $user) => $user->roles->pluck('name')->toArray())
    ->type(DataTableUi::BADGE, ['variant' => 'primary', 'size' => 'sm']),

Column::make(__('Teams'), 'teams_for_datatable')
    ->content(fn (User $user) => $user->teams->pluck('name')->toArray())
    ->type(DataTableUi::BADGE, ['variant' => 'secondary', 'size' => 'sm']),
```

**How it works:**
- `content()` accepts a closure that receives the row and returns a string or array
- `type()` specifies the component type (e.g., `DataTableUi::BADGE`) and optional attributes
- Arrays are automatically rendered as multiple component instances
- Components are rendered server-side with proper props and attributes

**Available component types:**
- `DataTableUi::BADGE` - Badge component
- `'button'` - Button component (and other UI components)

**Component attributes:**
All attributes passed to `type()` are forwarded to the component as props. For badges:
- `variant` - Color variant (`primary`, `secondary`, `success`, `error`, etc.)
- `size` - Size (`xs`, `sm`, `md`, `lg`, `xl`)
- `style` - Style (`outline`, `dash`, `soft`, `ghost`)

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

### Width and Truncation

Control how columns are sized and how they handle overflow:

**1. Fixed Width with Truncation:**
Forces a specific width and truncates content with ellipsis if it overflows.

```php
Column::make(__('Description'), 'description')
    ->width('200px')
```

**2. Auto Width with No Wrap (Default):**
Ensures the column stays on one line and is never cut or truncated. This is the **default behavior** for all columns.

```php
Column::make(__('Full Name'), 'name')
    // nowrap() is enabled by default
```

If you explicitly want to allow wrapping, you can use `->nowrap(false)`.

> [!NOTE]
> Setting `width()` automatically applies `nowrap` and `truncate`.

## Filter API

### Select Filter

```php
Filter::make('is_active', __('Status'))
    ->type('select')
    ->placeholder(__('All Statuses'))
    ->options([
        '1' => __('Active'),
        '0' => __('Inactive'),
    ])
```

**Important Notes:**
- Options must be provided as an **associative array** where keys are values and values are labels (`[value => label]`). This format is unified across the project and matches the `x-ui.select` component's expected format.
- **All filters automatically include an empty/null option as the first option** to allow users to clear the filter. The empty option uses the `placeholder` text as its label (or defaults to `__('ui.table.select_option')` if no placeholder is set).
- When the empty option is selected, the filter value becomes empty/null and is automatically excluded from active filters.
- The filter options are passed directly to the `x-ui.select` component via the `:options` prop.
- Active filter labels are automatically resolved from the options array using the filter value as the key (`$options[$value] ?? $value`).
- Active filter labels are automatically resolved from the options array using the filter value as the key (`$options[$value] ?? $value`).
- The filter options are passed directly to the `x-ui.select` component via the `:options` prop.

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
    ->placeholder(__('All Roles'))
    ->relationship('roles', 'name')
    ->optionsCallback(fn() => Role::pluck('name', 'name')->toArray())
```

**Note:** The `optionsCallback` must return an associative array (`[value => label]`), not an array of arrays. Use `pluck('column', 'key')` directly to create the associative array. The format matches the static `options()` method.

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

### Modal Actions

DataTable supports two types of dynamic modal actions that can render either a Blade view or a Livewire component.

**1. Blade Modal:**

```php
Action::make('view', __('View Details'))
    ->icon('eye')
    ->bladeModal('components.users.view-modal', fn (User $user) => ['user' => $user])
```

**2. Livewire Modal:**

```php
Action::make('edit', __('Fast Edit'))
    ->icon('pencil')
    ->livewireModal('modals.user-edit-form', fn (User $user) => ['user' => $user])
```

**Key Features:**
- **Dynamic Props**: Pass a closure to resolve properties based on the selected row.
- **Isolated State**: The modal state (`modalIsOpen`) is managed within the Datatable component.
- **Auto-Resolution**: Properties are automatically resolved and passed to the view/component.

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

## Enhanced UI Features

### Active Filter Badges

Active filters are automatically displayed as badges above the table with:
- Filter label and selected value
- Individual remove buttons (×)
- "Clear All" button when multiple filters are active

No additional configuration needed - filters are automatically tracked and displayed.

### Search Highlighting

Search terms are automatically highlighted in searchable columns with a yellow background. The highlighting is built with security and flexibility in mind:

- **HTML-Safe**: Highlighting uses a smart regex-based algorithm that only targets text content, avoiding matches within HTML tags or attributes. This prevents breaking tag structures or introducing security risks in HTML-enabled columns.
- **Secure-by-Default**: For non-HTML columns, highlighting is applied *after* escaping, ensuring that malicious content is neutralized first.
- **Support for Components**: Highlighting works seamlessly across components (like Badges), plain text, and custom HTML formatting.
- **Automatic Matching**: Uses case-insensitive matching and wraps matches in `<mark>` tags with premium styling.

### Per-Page Selector

A dropdown selector is automatically included in the pagination area allowing users to choose:
- 10, 15, 25, 50, or 100 items per page
- Selection persists in component state
- Automatically resets to page 1 when changed
- Displays current results info (showing X to Y of Z results)

### Reactive Selection

Selection state is fully managed by Livewire and automatically:
- Clears when searching, filtering, sorting, or changing pages
- Updates the "select all" checkbox based on current page (`isAllSelected` computed property)
- Tracks selected UUIDs across all pages
- Provides computed properties: `selectedCount()`, `hasSelection()`, `isAllSelected()`
- Optimized database queries by checking current page rows first before querying
- **New**: Uses `wire:model.live="selected"` on row checkboxes for seamless synchronization
- **New**: Uses dynamic `wire:key` on checkboxes and the select-all input to force re-render and prevent persistent visual "checked" states after clearing selection
- **New**: Bulk actions are now consolidated into a premium dropdown menu in the header, appearing only when items are selected
- **New**: Added a "Clear Selection" button in the header for quick reset


## Alpine.js Integration

The DataTable component uses Alpine.js for UI-only state management (filter panel, modals). All selection logic is handled by Livewire.

### Important Conventions

Following `docs/alpinejs/livewire-integration.md`:

⚠️ **DO NOT pass `$wire` as a parameter** - It's reactive and automatically available
✅ **Validate `$wire` before calling methods** - Check existence and type

### Usage

```blade
{{-- Correct: $wire is automatically available --}}
<div x-data="dataTable()">
    {{-- Your table HTML --}}
</div>
```

### Available State

```javascript
{
    openFilters: false,  // Boolean for filter panel visibility
    pendingAction: null,  // Stores action waiting for confirmation
}
```

**Note:** Selection state (`selected`, `selectedCount`, `hasSelection`, `isAllSelected`) is managed entirely by Livewire and accessed via `$wire` in Alpine.

### Available Methods

```javascript
{
    toggleFilters()         // Toggle filter panel
    closeFilters()          // Close filter panel
    executeActionWithConfirmation(actionKey, uuid, isBulk)  // Execute action with confirmation
    confirmAction(data)     // Confirm and execute pending action (triggered by event)
    cancelAction()          // Cancel pending action (triggered by event)
}
```

```blade
<div x-data="dataTable" 
    @datatable-action-confirmed.window="confirmAction($event.detail)"
    @datatable-action-cancelled.window="cancelAction()">

    {!! $this->renderFilters() !!}

    <div class="overflow-x-auto">
        <table class="table">
            {!! $this->renderTableHeader() !!}

            <tbody>
                @foreach($this->rows as $row)
                    {!! $this->renderTableRow($row) !!}
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $this->rows->links('components.datatable.pagination') }}
</div>
```

## Rendering API

The Datatable uses backend-driven rendering to keep the main template clean and allow for easy customization.

### Rendering Methods

These methods are defined in the `Datatable` base class and can be overridden in your component if you need custom UI for specific sections:

| Method | Description |
|--------|-------------|
| `renderFilters()` | Renders the search, bulk actions, and filter toggles. |
| `renderTableHeader()` | Renders the `<thead>` with column headers and sorting UI. |
| `renderTableRow($row)` | Renders a single `<tr>` with data cells and actions. |
| `renderRowActions($row)` | Renders the row actions dropdown for a specific row. |

### Modular Sub-views

The rendering methods use dedicated sub-views located in `resources/views/components/datatable/`:

- `filters.blade.php`
- `header.blade.php`
- `row.blade.php`
- `actions.blade.php`
- `pagination.blade.php`

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
    $user = $this->findModelByUuid($uuid); // Optimized: checks current page first
    if ($user !== null) {
        $this->redirect(route('users.show', $user));
    }
}
```

**Note:** The `findModelByUuid()` method is available in the base `Datatable` class and automatically checks the current page rows before querying the database, minimizing queries.

### State Management

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

### Preferences Integration

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

    $component->call('sort', 'name')
        ->assertSet('sortBy', 'name')
        ->assertSet('sortDirection', 'asc');

    $component->call('sort', 'name')
        ->assertSet('sortDirection', 'desc');
});

test('bulk select all', function () {
    User::factory()->count(5)->create();

    Livewire::actingAs($this->user)
        ->test('users.table')
        ->call('toggleSelectAll')
        ->assertTrue($this->isAllSelected);
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

**Query Optimization:**
The `Datatable` base class includes optimized methods that check the current page rows before querying the database:
- `findModelByUuid(string $uuid)` - Checks `$this->rows` first, then queries if not found
- `findModelsByUuids(array $uuids)` - Checks current page for each UUID, only queries missing ones

This dramatically reduces database queries when interacting with items visible on the current page.

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

## Translation Keys

The following translation keys are used by the DataTable component. Add them to your language files:

```php
// lang/en_US/ui.php
'actions' => [
    'clear_all' => 'Clear All',
    'confirm_action' => 'Are you sure you want to perform this action?',
],

'table' => [
    'active_filters' => 'Active filters',
    'per_page' => 'Per page',
    'showing_results' => 'Showing :from to :to of :total results',
],
```

## History

### Bulk Actions Refactor and Selection Fix (2025-12-30)
- **UI Enhancement**: Moved Bulk Action buttons into a dedicated `<x-ui.dropdown>` for a cleaner interface.
- **Improved Layout**: Placed "Clear Selection" button outside the dropdown with `text-error` styling for better visibility and accessibility.
- **State Synchronization**: Updated row checkboxes to use `wire:model.live="selected"` for automatic synchronization with Livewire state.
- **Visual State Fix**: Added dynamic `wire:key` to all checkboxes (rows and "select all") to force visual re-render after clearing selection, solving persistent "checked" states.
- **Test Coverage**: Refactored `UsersTableTest.php` to fix legacy typos and added explicit coverage for selection clearing.

### Table Actions Component (2025-01-XX)
- **Bug Fix**: Fixed row actions dropdown not showing by adding missing actions column rendering in table body rows.
- **Implementation**: Updated `<x-table.actions>` to render as dropdown menu with `ellipsis-vertical` icon.
- **Icon**: Uses `DataTableUi::ICON_THREE_DOTS` constant (`ellipsis-vertical`) for dropdown trigger.
- **Event Handling**: Added `wire:click.stop` to prevent row click events when interacting with actions.

### Component-Based Architecture (2025-01-XX)

The DataTable system was refactored from a service-based architecture to a Livewire component-based system:

- **Removed**: Old service layer (`DataTableBuilder`, `DataTablePreferencesService`, `SearchService`, `FilterService`, `SortService`, `SessionService`, `DataTableServiceProvider`)
- **Removed**: Old configuration system (`DataTableConfigInterface`, `UsersDataTableConfig`, `TransformerInterface`)
- **New Architecture**: Livewire component-based system (`App\Livewire\Datatable`) with direct state management
- **State Management**: All state (search, sort, filters, pagination) managed directly in Livewire component properties
- **Query Building**: Uses `DataTableQueryBuilder` for automatic relationship joins, search, filtering, and sorting

### Component Class Architecture (2025-01-XX)

All PHP logic was moved directly into component classes (`Datatable` and `Table`):

- **Component Classes**: `App\View\Components\Datatable` and `App\View\Components\Table` - All logic in component classes
- **On-demand processing**: Rows/columns processed only when iterating (no pre-processing)
- **Performance**: No unnecessary pre-processing, better performance
- **Removed**: `DataTableViewData` service class - all logic moved to component classes

## See Also

- [Alpine.js Documentation](../alpinejs/index.md) - Alpine.js conventions
- [Livewire 4 Documentation](../livewire-4/index.md) - Livewire 4 features

