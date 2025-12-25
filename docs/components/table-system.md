## Table System

**Location:** `resources/views/components/table/`

**Component Names:** `<x-table>`, `<x-table.header>`, `<x-table.body>`, `<x-table.row>`, `<x-table.cell>`, `<x-table.actions>`, `<x-table.bulk>`, `<x-table.pagination>`, `<x-table.empty>`

### Description

A comprehensive, Blade-first data table system designed for Livewire 4. The table system follows a strict separation of concerns:

-   **Livewire Component**: Handles all data, state, queries, and business logic
-   **Blade Components**: Pure presentational components that render UI only

This architecture ensures:
-   Single Livewire component per table (island architecture)
-   Reusable table components across different data sources
-   No business logic in Blade templates
-   Easy to test and maintain

### Architecture

The table system uses a **single Livewire component** (e.g., `UsersTable`) that:
-   Manages all state (search, filters, sorting, pagination, bulk selection)
-   Handles all queries and data fetching
-   Provides computed properties for columns, actions, and rows
-   Emits events for row actions and bulk actions

The **Blade components** are pure presentational:
-   Accept data via props
-   Render HTML markup only
-   Use `wire:*` attributes to communicate with Livewire
-   No database queries or business logic

### Component Structure

```
<x-table>
    <x-table.header :columns="$columns" :sort-by="$sortBy" :sort-direction="$sortDirection" />
    <x-table.body>
        @foreach ($rows as $row)
            <x-table.row wire:key="row-{{ $row->id }}">
                <x-table.cell>Content</x-table.cell>
                <x-table.cell>
                    <x-table.actions :actions="$actions" :item-uuid="$row->uuid" />
                </x-table.cell>
            </x-table.row>
        @endforeach
    </x-table.body>
</x-table>
```

### Props

#### `<x-table>`

| Prop  | Type     | Default | Description                    |
| ----- | -------- | ------- | ------------------------------ |
| `class` | `string` | `''`    | Additional CSS classes         |

#### `<x-table.header>`

| Prop             | Type     | Default | Description                                    |
| ---------------- | -------- | -------- | ---------------------------------------------- |
| `columns`        | `array`  | `[]`     | Column configuration array                     |
| `sortBy`         | `string` | `null`   | Current sort column key                        |
| `sortDirection`  | `string` | `'asc'`  | Current sort direction (`asc` or `desc`)       |
| `showBulk`       | `bool`   | `false`  | Show bulk selection checkbox                   |
| `selectPage`     | `bool`   | `false`  | Whether current page is selected               |
| `selectAll`      | `bool`   | `false`  | Whether all items are selected                 |

#### `<x-table.row>`

| Prop      | Type   | Default | Description                          |
| --------- | ------ | ------- | ------------------------------------ |
| `selected` | `bool` | `false` | Whether the row is selected          |

**Note**: The row component accepts `wire:click` via `$attributes` for row click behavior.

#### `<x-table.cell>`

Accepts all standard HTML attributes via `$attributes`.

#### `<x-table.actions>`

| Prop       | Type   | Default | Description                                    |
| ---------- | ------ | ------- | ---------------------------------------------- |
| `actions`  | `array` | `[]`    | Array of action configurations                 |
| `itemUuid` | `string` | `''`   | UUID of the item for action callbacks          |

**Implementation Details:**
- Renders as a dropdown menu with an `ellipsis-vertical` icon trigger button
- Uses `<x-ui.dropdown>` component with `placement="end"` and `menu` styling
- All actions are rendered as menu items (`<li>` elements) within the dropdown
- Delete actions are styled with `text-error` class and trigger confirmation modals
- Uses `wire:click.stop` on action buttons to prevent row click events
- Only renders if `count($actions) > 0`

#### `<x-table.bulk>`

| Prop          | Type   | Default | Description                          |
| ------------- | ------ | ------- | ------------------------------------ |
| `selectedCount` | `int` | `0`     | Number of selected items             |
| `bulkActions` | `array` | `[]`    | Array of bulk action configurations  |

#### `<x-table.pagination>`

| Prop      | Type              | Default | Description                    |
| --------- | ----------------- | ------- | ------------------------------ |
| `paginator` | `LengthAwarePaginator` | **Required** | Laravel paginator instance |

#### `<x-table.empty>`

| Prop         | Type  | Default | Description                          |
| ------------ | ----- | ------- | ------------------------------------ |
| `columnsCount` | `int` | `1`     | Number of columns for colspan        |

### Usage Example

**Livewire Component** (`resources/views/components/users/⚡table.blade.php`):

```php
<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public function getColumns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
        ];
    }

    public function getRowActions(): array
    {
        return [
            ['key' => 'view', 'label' => 'View', 'variant' => 'ghost', 'icon' => 'eye'],
            ['key' => 'delete', 'label' => 'Delete', 'variant' => 'ghost', 'color' => 'error', 'icon' => 'trash'],
        ];
    }

    #[Computed]
    public function rows()
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    public function sortBy(string $key): void
    {
        if ($this->sortBy === $key) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $key;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function handleRowAction(string $action, string $userUuid): void
    {
        match ($action) {
            'view' => $this->dispatch('user-view', userUuid: $userUuid),
            'delete' => $this->deleteUser($userUuid),
            default => null,
        };
    }
}; ?>

<div>

```blade
    <x-ui.input
        type="text"
        wire:model.live.debounce.300ms="search"
        placeholder="Search..."
    />

    <x-table>
        <x-table.header
            :columns="$this->getColumns()"
            :sort-by="$sortBy"
            :sort-direction="$sortDirection"
        />

        <x-table.body>
            @forelse ($this->rows as $user)
                <x-table.row wire:key="user-{{ $user->uuid }}" wire:click="rowClicked('{{ $user->uuid }}')">
                    <x-table.cell>{{ $user->name }}</x-table.cell>
                    <x-table.cell>{{ $user->email }}</x-table.cell>
                    <x-table.cell>
                        <x-table.actions
                            :actions="$this->getRowActions()"
                            :item-uuid="$user->uuid"
                        />
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty :columns-count="count($this->getColumns()) + 1" />
            @endforelse
        </x-table.body>
    </x-table>

    @if ($this->rows->hasPages())
        <div class="mt-6">
            <x-table.pagination :paginator="$this->rows" />
        </div>
    @endif
</div>
```

### Features

-   **Sortable Columns**: Click column headers to sort (toggles direction)
-   **Search**: Global search with debounced input
-   **Pagination**: Full pagination support with page numbers
-   **Row Actions**: Action buttons per row rendered as a dropdown menu with ellipsis-vertical icon (view, edit, delete, etc.)
-   **Bulk Actions**: Select multiple rows and perform bulk operations
-   **Row Click**: Optional row click behavior (navigate, view details, etc.)
-   **Empty State**: Automatic empty state when no results
-   **Responsive**: Table scrolls horizontally on small screens

### Best Practices

1. **Single Livewire Component**: One Livewire component per table (island architecture)
2. **SFC Format**: All Livewire components must use Single File Component (SFC) format with anonymous class syntax
3. **Pure Blade Components**: Table components should only render, never query or mutate
4. **Computed Properties**: Use `#[Computed]` for expensive queries
5. **URL Syncing**: Use `#[Url]` attributes for state that should sync with URL
6. **Authorization**: Check permissions in `mount()` method
7. **Event Dispatching**: Use `dispatch()` for actions that need to be handled elsewhere
8. **Wire Keys**: Always use `wire:key` in loops for proper Livewire tracking

### Current Usage in Project

1. **Users Table** (`resources/views/components/users/⚡table.blade.php`)
    - Lists all users with search, sorting, and pagination
    - Supports bulk selection and actions
    - Row click navigates to user details
    - Protected by `Permissions::VIEW_USERS` permission
    - Uses SFC format (Single File Component) with anonymous class syntax
    - **Now uses the DataTable System** (see below)

---

