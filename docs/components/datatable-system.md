## DataTable System

**Location:** `app/Services/DataTable/`, `app/Livewire/DataTable/`

**Component Name:** `BaseDataTableComponent` (abstract base class)

### Description

A comprehensive, service-based DataTable system that provides a reusable architecture for building data tables with advanced search, filtering, sorting, and pagination. The system follows a strict separation of concerns with a service layer handling all business logic and a base Livewire component providing the integration point.

### Architecture

The DataTable System consists of several layers:

1. **Service Layer** (`app/Services/DataTable/`):
   - `DataTableBuilder`: Orchestrates building the DataTable response
   - `SearchService`: Applies global search using search macro
   - `FilterService`: Applies filters based on request parameters
   - `SortService`: Applies sorting to queries (supports relation fields)
   - `SessionService`: Manages session state for filters (uses DataTablePreferencesService)
   - `DataTablePreferencesService`: Manages DataTable preferences (filters, per_page, sort, search) with persistence

2. **Configuration Layer** (`app/Services/DataTable/Configs/`):
   - `DataTableConfigInterface`: Contract for DataTable configuration
   - Entity-specific configs (e.g., `UsersDataTableConfig`)

3. **Transformation Layer** (`app/Services/DataTable/Transformers/`):
   - `TransformerInterface`: Contract for transforming models
   - Entity-specific transformers (e.g., `UserDataTableTransformer`)

4. **Options Provider Layer** (`app/Services/DataTable/OptionsProviders/`):
   - `OptionsProviderInterface`: Contract for filter options
   - Entity-specific providers (e.g., `RoleOptionsProvider`)

5. **Livewire Integration** (`app/Livewire/DataTable/`):
   - `BaseDataTableComponent`: Abstract base class for DataTable components
   - Entity-specific components extend this base class

### Key Features

- **Global Search**: Search across multiple fields using the search macro
- **Advanced Filtering**: Support for select, multiselect, boolean, relationship, date range filters
- **Smart Sorting**: Optimized sorting with support for relation fields
- **Preferences Persistence**: All preferences (filters, per_page, sort, search) persisted in session and user's `frontend_preferences` JSON column (for authenticated users)
- **Bulk Actions**: Support for bulk operations
- **URL Synchronization**: Search, filters, and sorting sync with URL via `#[Url]` attributes
- **Computed Properties**: Uses `#[Computed]` for efficient data loading
- **Automatic Preference Loading**: Preferences are automatically loaded on component mount and saved when changed

### Usage Example

**Create a DataTable Config** (`app/Services/DataTable/Configs/UsersDataTableConfig.php`):

```php
<?php

use App\Services\DataTable\Contracts\DataTableConfigInterface;

class UsersDataTableConfig implements DataTableConfigInterface
{
    public function getSearchableFields(): array
    {
        return ['name', 'email', 'username'];
    }

    public function getFilterableFields(): array
    {
        return [
            'role' => [
                'type' => 'select',
                'label' => __('ui.table.users.filters.role'),
                'options_provider' => RoleOptionsProvider::class,
                'relationship' => [
                    'name' => 'roles',
                    'column' => 'name',
                ],
            ],
        ];
    }

    public function getSortableFields(): array
    {
        return [
            'name' => ['label' => __('ui.table.users.name')],
            'email' => ['label' => __('ui.table.users.email')],
        ];
    }

    public function getDefaultSort(): ?array
    {
        return ['column' => 'created_at', 'direction' => 'desc'];
    }

    // ... other required methods
}
```

**Create a Transformer** (`app/Services/DataTable/Transformers/UserDataTableTransformer.php`):

```php
<?php

use App\Services\DataTable\Contracts\TransformerInterface;

class UserDataTableTransformer implements TransformerInterface
{
    public function transform($user): array
    {
        return [
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            // ... other fields
        ];
    }
}
```

**Create a Livewire Component** (`resources/views/components/users/⚡table.blade.php`):

```php
<?php

use App\Livewire\DataTable\BaseDataTableComponent;
use App\Models\User;
use App\Services\DataTable\Configs\UsersDataTableConfig;
use App\Services\DataTable\Transformers\UserDataTableTransformer;

new class extends BaseDataTableComponent
{
    protected function getConfig(): DataTableConfigInterface
    {
        return app(UsersDataTableConfig::class);
    }

    protected function getBaseQuery(): Builder
    {
        return User::query();
    }

    protected function getTransformer(): TransformerInterface
    {
        return app(UserDataTableTransformer::class);
    }

    /**
     * Get headers configuration (for table header row)
     */
    public function getHeaders(): array
    {
        return [
            ['key' => 'name', 'label' => __('ui.table.users.name'), 'sortable' => true],
            ['key' => 'email', 'label' => __('ui.table.users.email'), 'sortable' => true],
        ];
    }

    /**
     * Get columns configuration (for table body cells)
     */
    public function getColumns(): array
    {
        return [
            ['key' => 'name', 'type' => 'text', 'bold' => true],
            ['key' => 'email', 'type' => 'text', 'muted' => true],
        ];
    }

    /**
     * Get row actions configuration
     */
    public function getRowActions(): array
    {
        return [
            ['key' => 'view', 'label' => __('ui.actions.view'), 'variant' => 'ghost', 'icon' => 'eye'],
            ['key' => 'edit', 'label' => __('ui.actions.edit'), 'variant' => 'ghost', 'icon' => 'pencil'],
        ];
    }
}; ?>

<x-datatable
    :rows="$this->rows->items()"
    :headers="$this->getHeaders()"
    :columns="$this->getColumns()"
    :actions-per-row="$this->getRowActions()"
    :bulk-actions="$this->getBulkActions()"
    row-click="rowClicked"
    :selected="$selected"
    :sort-by="$sortBy ?: null"
    :sort-direction="$sortDirection"
    :paginator="$this->rows"
/>
```

### DataTable DSL (Domain-Specific Language)

The DataTable System includes a fluent DSL for defining table structure, similar to the Navigation and Notification builders. This DSL provides type-safe, autocomplete-friendly definitions with no hardcoded strings.

**Key Principles:**
- **No hardcoded strings**: All action keys, column types, filter types, icons, and component names must use constants/enums
- **Typed closures**: Action `execute()` closures are fully typed for autocomplete
- **Transformer-only values**: All cell values come from the transformer array (no model access in Blade)
- **Component registry**: All cell and filter components are allowlisted via registries for security

**Location**: `app/Services/DataTable/Dsl/`, `app/Enums/DataTable/DataTableColumnType.php`, `app/Enums/DataTable/DataTableFilterType.php`, `app/Constants/DataTable/DataTableUi.php`

#### DSL Classes

- **`DataTableDefinition`**: Main builder for table definitions
- **`HeaderItem`**: Fluent builder for table headers (with sorting, visibility, viewport-only)
- **`ColumnItem`**: Fluent builder for table columns (with type, custom render, viewport-only)
- **`RowActionItem`**: Fluent builder for row actions (with execute closure, modal support)
- **`BulkActionItem`**: Fluent builder for bulk actions (with execute closure, modal support)
- **`FilterItem`**: Fluent builder for filters (with type, options provider, relationship)

#### Usage Example (Model Definition)

```php
// In User model (using HasDataTable trait)
use App\Constants\DataTable\DataTableUi;
use App\Enums\DataTable\DataTableColumnType;
use App\Enums\DataTable\DataTableFilterType;
use App\Services\DataTable\Dsl\BulkActionItem;
use App\Services\DataTable\Dsl\ColumnItem;
use App\Services\DataTable\Dsl\FilterItem;
use App\Services\DataTable\Dsl\HeaderItem;
use App\Services\DataTable\Dsl\RowActionItem;

public static function datatable(): DataTableDefinition
{
    return DataTableDefinition::make()
        ->headers(
            HeaderItem::make()
                ->label(__('ui.table.users.name'))
                ->sortable('name')
                ->column(
                    ColumnItem::make()
                        ->name('name')
                        ->type(DataTableColumnType::TEXT)
                        ->props(['bold' => true])
                ),
            HeaderItem::make()
                ->label(__('ui.table.users.email'))
                ->sortable('email')
                ->column(
                    ColumnItem::make()
                        ->name('email')
                        ->type(DataTableColumnType::TEXT)
                        ->props(['muted' => true])
                )
        )
        ->actions(
            RowActionItem::make()
                ->key(DataTableUi::ACTION_VIEW)
                ->label(__('ui.actions.view'))
                ->icon(DataTableUi::ICON_EYE)
                ->variant(DataTableUi::VARIANT_GHOST),
            RowActionItem::make()
                ->key(DataTableUi::ACTION_DELETE)
                ->label(__('ui.actions.delete'))
                ->icon(DataTableUi::ICON_TRASH)
                ->variant(DataTableUi::VARIANT_GHOST)
                ->color(DataTableUi::COLOR_ERROR)
                ->showModal(DataTableUi::MODAL_TYPE_CONFIRM)
                ->execute(function (User $user) {
                    $user->delete();
                })
        )
        ->bulkActions(
            BulkActionItem::make()
                ->key(DataTableUi::BULK_ACTION_DELETE)
                ->label(__('ui.actions.delete_selected'))
                ->icon(DataTableUi::ICON_TRASH)
                ->variant(DataTableUi::VARIANT_GHOST)
                ->color(DataTableUi::COLOR_ERROR)
                ->showModal(DataTableUi::MODAL_TYPE_CONFIRM)
                ->execute(function (Collection $users) {
                    User::whereIn('uuid', $users->pluck('uuid'))->delete();
                })
        )
        ->filters(
            FilterItem::make()
                ->key('role')
                ->label(__('ui.table.users.filters.role'))
                ->placeholder(__('ui.table.users.filters.all_roles'))
                ->type(DataTableFilterType::SELECT)
                ->optionsProvider(RoleOptionsProvider::class)
                ->relationship(['name' => 'roles', 'column' => 'name'])
        );
}
```

#### Row Actions UX

- **Always rendered as kebab dropdown**: Row actions are always shown in a 3-dots (kebab) dropdown menu in the actions column
- **Icon**: Uses `ellipsis-vertical` icon (from `DataTableUi::ICON_THREE_DOTS` constant) as the dropdown trigger button
- **Dropdown placement**: Actions dropdown is placed at the end (right side) of the table row
- **Event handling**: Uses `wire:click.stop` to prevent row click events from firing when interacting with actions
- **Modal support**: Actions can have `showModal()` configured to open a modal before execution
- **Modal types**: `'blade'`, `'livewire'`, `'html'`, or `'confirm'` (uses confirm-modal component)
- **Execute closures**: Typed closures receive the model instance and execute server-side
- **Delete actions**: Delete actions are styled with error color and trigger confirmation modals

#### Bulk Actions UX

- **Buttons if ≤3**: If 3 or fewer bulk actions, they render as separate buttons
- **Dropdown if >3**: If more than 3 bulk actions, they render in a dropdown labeled `__('ui.table.bulk_actions')`
- **Modal support**: Same modal support as row actions

#### Viewport-Only Visibility

Headers and columns support `showInViewPortsOnly(['sm', 'lg'])` which means "show ONLY on these viewports":

```php
HeaderItem::make()
    ->label('Name')
    ->showInViewPortsOnly(['sm', 'lg'])  // Hidden by default, visible only on sm and lg
```

This generates Tailwind classes: `hidden sm:table-cell lg:table-cell`

#### Component Registry System

All cell and filter components are registered via allowlisted registries:

- **`DataTableComponentRegistry`**: Maps `DataTableColumnType` enum → Blade component names
- **`DataTableFilterComponentRegistry`**: Maps `DataTableFilterType` enum → Blade/Livewire component names

**Security**: Only registered components can be rendered, preventing XSS via component injection.

#### Column Types

Available column types (via `DataTableColumnType` enum):
- `TEXT` → `datatable.cells.text`
- `BADGE` → `datatable.cells.badge`
- `BOOLEAN` → `datatable.cells.boolean`
- `DATE` → `datatable.cells.date`
- `DATETIME` → `datatable.cells.datetime`
- `CURRENCY` → `datatable.cells.currency`
- `NUMBER` → `datatable.cells.number`
- `LINK` → `datatable.cells.link`
- `AVATAR` → `datatable.cells.avatar`
- `SAFE_HTML` → `datatable.cells.safe-html` (sanitized via HtmlSanitizer)

#### Filter Types

Available filter types (via `DataTableFilterType` enum):
- `SELECT` → `datatable.filters.select`
- `MULTISELECT` → `datatable.filters.multiselect`
- `BOOLEAN` → `datatable.filters.boolean`
- `DATE_RANGE` → `datatable.filters.date-range`
- `RELATIONSHIP` → `datatable.filters.relationship`

#### Header Column Structure

The header column structure supports both DSL (from `HeaderItem`) and legacy formats. The `table.header` component automatically handles both:

**DSL Structure** (from `HeaderItem::toArray()`):
- `label`: Header label text
- `sortable`: Boolean indicating if column is sortable
- `sortKey`: Sort key (if different from column key)
- `column`: Associated column data with `key` property
- `showInViewPortsOnly`: Array of viewports where column is visible (hidden by default)

**Legacy Structure**:
- `key`: Column key for sorting
- `label`: Header label text
- `sortable`: Boolean indicating if column is sortable
- `hidden`: Boolean to hide the header
- `responsive`: Responsive breakpoint string (e.g., 'md')

**Header Actions** (optional):
- `headerActions`: Array of action configurations for buttons/components in header
- `headerSlot`: Custom HTML content slot

**Constants**: All header keys should use constants from `App\Constants\DataTable\DataTableUi`:
- `DataTableUi::HEADER_KEY` - Column key
- `DataTableUi::HEADER_LABEL` - Header label
- `DataTableUi::HEADER_SORTABLE` - Sortable flag
- `DataTableUi::HEADER_SORT_KEY` - Sort key (DSL)
- `DataTableUi::HEADER_HIDDEN` - Hidden flag (legacy)
- `DataTableUi::HEADER_RESPONSIVE` - Responsive breakpoint (legacy)
- `DataTableUi::HEADER_COLUMN` - Column data (DSL)
- `DataTableUi::HEADER_SHOW_IN_VIEWPORTS_ONLY` - Viewport visibility (DSL)
- `DataTableUi::HEADER_ACTIONS` - Header actions array
- `DataTableUi::HEADER_SLOT` - Custom header slot

**Header Action Constants**:
- `DataTableUi::HEADER_ACTION_COMPONENT` - Dynamic component name
- `DataTableUi::HEADER_ACTION_BUTTON` - Button flag
- `DataTableUi::HEADER_ACTION_WIRE_CLICK` - Livewire method to call
- `DataTableUi::HEADER_ACTION_ICON` - Icon name
- `DataTableUi::HEADER_ACTION_LABEL` - Button label
- `DataTableUi::HEADER_ACTION_CLASS` - CSS classes
- `DataTableUi::HEADER_ACTION_ATTRIBUTES` - Additional HTML attributes
- `DataTableUi::HEADER_ACTION_SLOT` - Custom slot content

#### Security Rules

1. **Transformer-only values**: All cell values must come from the transformer array. No model access in Blade templates.
2. **SafeHtml sanitization**: When using `DataTableColumnType::SAFE_HTML`, content is sanitized via `HtmlSanitizer` service before rendering.
3. **Component allowlisting**: Only components registered in `DataTableComponentRegistry` or `DataTableFilterComponentRegistry` can be rendered.
4. **No hardcoded strings**: Action keys, types, icons, component names, and header column keys must use constants/enums from `DataTableUi`, `DataTableColumnType`, or `DataTableFilterType`.

#### Integration with BaseDataTableComponent

The `BaseDataTableComponent` automatically uses the DSL definition if `getDefinition()` returns a `DataTableDefinition`:

```php
// In your Livewire component
protected function getDefinition(): ?DataTableDefinition
{
    return User::datatable();  // Uses HasDataTable trait
}

protected function getModelClass(): string
{
    return User::class;  // Required for action execution
}
```

The component automatically:
- Extracts headers, columns, actions, bulk actions, and filters from the definition
- Executes action closures when actions are clicked
- Opens modals when actions have `showModal()` configured
- Handles modal confirmations and executes closures after confirmation

### Component Architecture

The DataTable System uses **component classes** to handle all PHP logic directly. All processing methods are available in the component classes (`Datatable` and `Table`), providing a clean separation between logic and presentation while keeping everything in the component layer.

**Component Classes**: `App\View\Components\Datatable` and `App\View\Components\Table`

**Locations**: 
- `app/View/Components/Datatable.php`
- `app/View/Components/Table.php`

#### Purpose

The component classes:
- Accept all component props via constructor
- Initialize service registries once in constructor (DataTableComponentRegistry, DataTableFilterComponentRegistry)
- Provide public methods for all processing and computed values
- Enable on-demand processing in Blade templates using `@php` blocks (for performance)
- Process rows/columns only when iterating (no pre-processing)

#### Key Methods

**Datatable Component Methods:**

**Computed Values:**
- `getColumnsCount()` - Calculate total columns (bulk checkbox + data columns + actions)
- `hasActionsPerRow()` - Check if row actions exist
- `getBulkActionsCount()` - Get bulk actions count
- `showBulkActionsDropdown()` - Check if bulk actions should be in dropdown (>3)
- `hasFilters()` - Check if filters exist
- `hasSelected()` - Check if any rows are selected
- `showBulkBar()` - Check if bulk actions bar should be shown
- `hasPaginator()` - Check if paginator has pages
- `getSearchPlaceholder()` - Get search placeholder text

**Processing Methods:**
- `processFilter(array $filter)` - Filter component resolution and safe attributes extraction

**Modal Methods:**
- `getModalStateId(string $actionKey, ?string $rowUuid = null, string $type = 'row')` - Generate Alpine.js modal state ID
- `findActionByKey(string $key, string $type = 'row')` - Find action by key
- `getRowActionModalConfig()` - Get row action modal configuration
- `getBulkActionModalConfig()` - Get bulk action modal configuration

**Getter Methods:**
- `getRows()`, `getHeaders()`, `getColumns()`, `getFilters()`, `getBulkActions()`, `getActionsPerRow()`, `getSelected()`, `getSortBy()`, `getSortDirection()`, `getPaginator()`, `getEmptyMessage()`, `getEmptyIcon()`, `getClass()`, `getRowClick()`
- `isShowBulk()`, `isSelectPage()`, `isSelectAll()`, `isShowSearch()`

**Table Component Methods:**

**Processing Methods:**
- `processRow(array $row, int $index)` - Row UUID validation, selection state, row classes, click attributes
- `processColumn(array $column, array $row)` - Column component resolution, viewport classes, custom render detection
- `processHeaderColumn(array $column)` - Header column processing (hidden, responsive, sortable logic)

**Computed Values:**
- `getColumnsCount()` - Calculate total columns
- `hasActionsPerRow()` - Check if row actions exist

**Getter Methods:**
- `getRows()`, `getColumns()`, `getHeaders()`, `getSortBy()`, `getSortDirection()`, `getEmptyMessage()`, `getEmptyIcon()`, `getClass()`
- `isShowBulk()`

#### Benefits

1. **Performance**: On-demand processing, no unnecessary pre-processing
2. **Clarity**: All logic in component classes, easy to find
3. **Standardization**: One pattern, no backward compatibility confusion
4. **Flexibility**: `@php` blocks allowed for performance-critical loops

#### Usage in Components

Component methods are called directly from Blade templates:

```blade
{{-- In datatable.blade.php --}}
<div class="space-y-4 {{ $getClass() }}">
    @if ($hasFilters())
        @foreach ($getFilters() as $filter)
            @php
                $processedFilter = $processFilter($filter);
            @endphp
            {{-- Use processed filter --}}
        @endforeach
    @endif
    
    <x-table
        :rows="$rows"
        :headers="$headers"
        :columns="$columns"
        {{-- ... all props --}}
    ></x-table>
</div>
```

In the Table component, methods are called in `@php` blocks for performance:

```blade
{{-- In table.blade.php --}}
@forelse ($getRows() as $row)
    @php
        $rowData = $processRow($row, $loop->index);
    @endphp
    <tr {!! $rowData['rowClickAttr'] !!} {!! $rowData['rowClassAttr'] !!}>
        {{-- Bulk Selection Checkbox --}}
        @if ($isShowBulk() && $rowData['uuid'])
            <td wire:click.stop>
                <input type="checkbox" wire:model.live="selected" value="{{ $rowData['uuid'] }}" />
            </td>
        @endif

        {{-- Data Columns --}}
        @foreach ($getColumns() as $column)
            @php
                $columnData = $processColumn($column, $row);
            @endphp
            {{-- Use processed column data --}}
        @endforeach

        {{-- Actions Column (automatically rendered if actionsPerRow is provided) --}}
        @if ($hasActionsPerRow() && $rowData['uuid'])
            <td wire:click.stop>
                <x-table.actions :actions="$actionsPerRow" :item-uuid="$rowData['uuid']"></x-table.actions>
            </td>
        @endif
    </tr>
@endforelse
```

### Unified Table Component

The DataTable System includes a unified `<x-datatable>` component that handles all rendering logic, making it easy to create consistent tables across the application.

**Component**: `<x-datatable>`

**Location**: `resources/views/components/datatable.blade.php`

**Note**: The component uses its own class methods to process all data. All PHP logic is in the component class, with `@php` blocks used in Blade templates for on-demand processing when needed for performance.

**Props**:

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `rows` | `array` | `[]` | Array of row data (from transformer) |
| `headers` | `array` | `[]` | Header configuration with labels, sortable flags |
| `columns` | `array` | `[]` | Column configuration with types, render options |
| `actionsPerRow` | `array` | `[]` | Row action buttons configuration |
| `bulkActions` | `array` | `[]` | Bulk action buttons configuration |
| `rowClick` | `string\|null` | `null` | Livewire method name for row click handler |
| `filters` | `array` | `[]` | Applied filters (for future use) |
| `showSearch` | `bool` | `true` | Show search bar (by default) |
| `searchPlaceholder` | `string\|null` | `null` | Custom search placeholder |
| `selected` | `array` | `[]` | Selected row UUIDs |
| `selectPage` | `bool` | `false` | Select all on current page state |
| `selectAll` | `bool` | `false` | Select all across pages state |
| `showBulk` | `bool` | `true` | Show bulk selection checkbox |
| `sortBy` | `string\|null` | `null` | Current sort column |
| `sortDirection` | `string` | `'asc'` | Current sort direction |
| `paginator` | `LengthAwarePaginator\|null` | `null` | Paginator instance |
| `emptyMessage` | `string\|null` | `null` | Custom empty state message |
| `emptyIcon` | `string` | `'user-group'` | Icon for empty state |

**Column Types**:

The unified component supports multiple column types for automatic rendering:

- **`text`**: Plain text (default) - supports `bold` and `muted` options
- **`badge`**: Badge component with `badgeColor` and `badgeSize` options
- **`boolean`**: Boolean values with `trueLabel` and `falseLabel` options
- **`date`**: Date formatting with `format` option (default: 'Y-m-d')
- **`datetime`**: DateTime formatting with `format` option (default: 'Y-m-d H:i')
- **`currency`**: Currency formatting using `formatCurrency()` helper with `currency` option
- **`number`**: Number formatting with `decimals`, `decimalSeparator`, `thousandsSeparator` options
- **`link`**: Link with `href` and optional `external` target
- **`avatar`**: Avatar image with fallback to `defaultAvatar` or generated avatar

**Column Configuration Options**:

```php
[
    'key' => 'name',                    // Required: field key from row data
    'type' => 'text',                   // Optional: column type (default: 'text')
    'hidden' => false,                  // Optional: hide column
    'responsive' => 'md',               // Optional: show only on 'md' and up (e.g., 'md', 'lg')
    'bold' => true,                     // Optional: bold text (for text type)
    'muted' => true,                    // Optional: muted text color (for text type)
    'class' => 'custom-class',          // Optional: custom CSS classes
    'render' => '<span>Custom</span>',  // Optional: custom HTML string (overrides type)
    // Type-specific options:
    'format' => 'Y-m-d',                // For date/datetime types
    'badgeColor' => 'success',           // For badge type
    'badgeSize' => 'sm',                // For badge type
    'trueLabel' => 'Yes',               // For boolean type
    'falseLabel' => 'No',               // For boolean type
    'currency' => 'USD',                 // For currency type
    'decimals' => 2,                    // For number type
    'href' => '/users/{uuid}',          // For link type (supports {uuid} placeholder)
    'external' => false,                // For link type
]
```

**Header Configuration**:

The header structure supports both DSL (DataTableDefinition) and legacy formats:

**DSL Format** (from `HeaderItem::toArray()`):
```php
[
    'label' => 'Name',                  // Required: header label
    'sortable' => true,                 // Optional: enable sorting
    'sortKey' => 'name',                // Optional: sort key (if different from column key)
    'column' => [                       // Optional: associated column data
        'key' => 'name',                // Column key for sorting
        // ... other column properties
    ],
    'showInViewPortsOnly' => ['sm', 'lg'], // Optional: show only on these viewports (hidden by default)
]
```

**Legacy Format**:
```php
[
    'key' => 'name',                    // Required: column key
    'label' => 'Name',                  // Required: header label
    'sortable' => true,                 // Optional: enable sorting
    'hidden' => false,                  // Optional: hide header
    'responsive' => 'md',               // Optional: show only on 'md' and up
]
```

**Header Actions** (optional):
```php
[
    'headerActions' => [
        [
            'component' => 'ui.button',  // Optional: dynamic component name
            'wireClick' => 'addNew()',   // Optional: Livewire method to call
            'icon' => 'plus',            // Optional: icon name
            'label' => 'Add',            // Optional: button label
            'class' => 'btn btn-sm',     // Optional: CSS classes
            'attributes' => [            // Optional: additional HTML attributes
                'data-test' => 'add-btn',
            ],
            'slot' => '<span>Custom</span>', // Optional: custom slot content
        ],
    ],
    'headerSlot' => '<button>Custom HTML</button>', // Optional: custom HTML content
]
```

**Note**: All header keys should use constants from `App\Constants\DataTable\DataTableUi`:
- `DataTableUi::HEADER_KEY` - Column key
- `DataTableUi::HEADER_LABEL` - Header label
- `DataTableUi::HEADER_SORTABLE` - Sortable flag
- `DataTableUi::HEADER_SORT_KEY` - Sort key (DSL)
- `DataTableUi::HEADER_HIDDEN` - Hidden flag (legacy)
- `DataTableUi::HEADER_RESPONSIVE` - Responsive breakpoint (legacy)
- `DataTableUi::HEADER_COLUMN` - Column data (DSL)
- `DataTableUi::HEADER_SHOW_IN_VIEWPORTS_ONLY` - Viewport visibility (DSL)
- `DataTableUi::HEADER_ACTIONS` - Header actions array
- `DataTableUi::HEADER_SLOT` - Custom header slot

**Features**:

- **Automatic Search Bar**: Search bar is shown by default (can be disabled with `:show-search="false"`)
- **Reactive Selection**: Selected items are properly reactive using `wire:model.live` with proper `wire:key` handling
- **Column Types**: Automatic rendering based on column type
- **Custom Render**: Support for custom HTML via `render` option
- **Responsive Columns**: Hide/show columns based on viewport using `responsive` option
- **Row Click**: Optional row click handler via `rowClick` prop
- **Bulk Actions**: Automatic bulk action bar when items are selected
- **Pagination**: Automatic pagination display
- **Empty State**: Customizable empty state with icon and message

**Example Usage**:

```blade
<x-datatable
    :rows="$this->rows->items()"
    :headers="$this->getHeaders()"
    :columns="$this->getColumns()"
    :actions-per-row="$this->getRowActions()"
    :bulk-actions="$this->getBulkActions()"
    row-click="rowClicked"
    :selected="$selected"
    :sort-by="$sortBy ?: null"
    :sort-direction="$sortDirection"
    :paginator="$this->rows"
    :show-search="true"
/>
```

### Filter Types

The DataTable System supports multiple filter types:

1. **Select**: Single value selection
2. **Multiselect**: Multiple value selection
3. **Boolean**: True/false filter
4. **Relationship**: Filter by related model
5. **Has Relationship**: Filter by presence/absence of relationship
6. **Date Range**: Filter by date range (from/to)

### Preferences System

The DataTable System includes a comprehensive preferences system that follows the same pattern as `FrontendPreferencesService`:

**Storage:**
- **Guests**: Preferences stored in session only
- **Authenticated Users**: Preferences stored in `users.frontend_preferences` JSON column under keys like `datatable_preferences.users`, synced to session

**Preferences Stored:**
- `search`: Global search query
- `per_page`: Items per page
- `sort`: Sort column and direction
- `filters`: Applied filter values

**Architecture:**
- `DataTablePreferencesService`: Main service (same pattern as `FrontendPreferencesService`)
- `SessionDataTablePreferencesStore`: Session-based storage
- `UserJsonDataTablePreferencesStore`: User JSON column storage
- `DataTablePreferencesStore` interface: Contract for storage implementations

**Behavior:**
- Preferences are automatically loaded on component mount
- Preferences are automatically saved when search, filters, per_page, or sort change
- On login, all DataTable preferences are synced from database to session
- Session is the single source of truth for reads (with automatic DB sync for authenticated users)

**Example Storage Structure:**

```json
{
  "locale": "en_US",
  "theme": "light",
  "datatable_preferences.users": {
    "search": "john",
    "per_page": 25,
    "sort": {
      "column": "name",
      "direction": "asc"
    },
    "filters": {
      "is_active": true,
      "email_verified_at": true
    }
  }
}
```

### Service Registration

All services are registered in `DataTableServiceProvider`:

```php
$this->app->bind(DataTableBuilderInterface::class, DataTableBuilder::class);
$this->app->singleton(DataTablePreferencesService::class);
$this->app->singleton(SearchService::class);
$this->app->singleton(FilterService::class);
// ... etc
```

### Best Practices

1. **Extend BaseDataTableComponent**: Always extend `BaseDataTableComponent` for new DataTable components
2. **Use Unified Component**: Always use `<x-datatable>` component for table rendering - it handles all UI logic automatically
3. **Separate Headers and Columns**: Define `getHeaders()` for table headers and `getColumns()` for body cells with types
4. **Use Column Types**: Leverage built-in column types (badge, boolean, date, etc.) for consistent rendering instead of custom HTML
5. **Use Configs**: Define entity-specific configurations in `Configs/` directory
6. **Use Transformers**: Transform models to arrays in `Transformers/` directory
7. **Use Options Providers**: Provide filter options in `OptionsProviders/` directory
8. **URL Syncing**: Use `#[Url]` attributes for state that should sync with URL
9. **Computed Properties**: Use `#[Computed]` for expensive queries
10. **Authorization**: Override `authorizeAccess()` method in child classes
11. **Reactive Selection**: Selected items are automatically reactive - use `wire:model.live` in the unified component
12. **Row Click Handler**: Define `rowClicked()` method in your component and pass `row-click="rowClicked"` to the unified component

### Current Usage in Project

1. **Users Table** (`resources/views/components/users/⚡table.blade.php`)
    - Extends `BaseDataTableComponent`
    - Uses `UsersDataTableConfig` for configuration
    - Uses `UserDataTableTransformer` for data transformation
    - Supports search, filtering by role and verification status, sorting, and pagination

---

