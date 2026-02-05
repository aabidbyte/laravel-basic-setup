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
- ✅ Optimized Server-side selection with deferred updates

## Documentation Index

- **[Architecture](architecture.md)** - Understanding the modular trait-based design.
- **[Column API](columns.md)** - Defining columns, formatting, sorting, and searching.
- **[Filter API](filters.md)** - Creating select, date range, and custom filters.
- **[Action API](actions.md)** - Row actions, bulk actions, and modal integrations.
- **[Features & UI](features.md)** - Search highlighting, badges, selection, and Alpine.js.
- **[Advanced](advanced.md)** - Custom queries, row clicks, state management, and preferences.
- **[Performance](performance.md)** - Infinite scroll, memoization, and optimization tips.
- **[Rendering](rendering.md)** - Customizing the table render pipeline.
- **[Troubleshooting](troubleshooting.md)** - Common issues and solutions.
- **[History](history.md)** - Changelog and architectural evolution.

## Quick Start

### 1. Create a DataTable Component

**File:** `app/Livewire/Tables/UserTable.php`

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Livewire\DataTable\Datatable;
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
public array $selected = [];             // Selected UUIDs (deferred)
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

    $component = Livewire::actingAs($this->user)
        ->test('users.table')
        ->call('toggleSelectAll');
        
    $this->assertTrue($component->instance()->isAllSelected);
});
```
