# History

## Bulk Actions Refactor and Selection Fix (2025-12-30)
- **UI Enhancement**: Moved Bulk Action buttons into a dedicated `<x-ui.dropdown>` for a cleaner interface.
- **Improved Layout**: Placed "Clear Selection" button outside the dropdown with `text-error` styling for better visibility and accessibility.
- **State Synchronization**: Updated row checkboxes to use `wire:model.live="selected"` for automatic synchronization with Livewire state.
- **Visual State Fix**: Added dynamic `wire:key` to all checkboxes (rows and "select all") to force visual re-render after clearing selection, solving persistent "checked" states.
- **Test Coverage**: Refactored `UsersTableTest.php` to fix legacy typos and added explicit coverage for selection clearing.

## Table Actions Component (2025-01-XX)
- **Bug Fix**: Fixed row actions dropdown not showing by adding missing actions column rendering in table body rows.
- **Implementation**: Updated `<x-table.actions>` to render as dropdown menu with `ellipsis-vertical` icon.
- **Icon**: Uses `DataTableUi::ICON_THREE_DOTS` constant (`ellipsis-vertical`) for dropdown trigger.
- **Event Handling**: Added `wire:click.stop` to prevent row click events when interacting with actions.

## Component-Based Architecture (2025-01-XX)

The DataTable system was refactored from a service-based architecture to a Livewire component-based system:

- **Removed**: Old service layer (`DataTableBuilder`, `DataTablePreferencesService`, `SearchService`, `FilterService`, `SortService`, `SessionService`, `DataTableServiceProvider`)
- **Removed**: Old configuration system (`DataTableConfigInterface`, `UsersDataTableConfig`, `TransformerInterface`)
- **New Architecture**: Livewire component-based system (`App\Livewire\Datatable`) with direct state management
- **State Management**: All state (search, sort, filters, pagination) managed directly in Livewire component properties
- **Query Building**: Uses `DataTableQueryBuilder` for automatic relationship joins, search, filtering, and sorting

## Component Class Architecture (2025-01-XX)

All PHP logic was moved directly into component classes (`Datatable` and `Table`):

- **Component Classes**: `App\View\Components\Datatable` and `App\View\Components\Table` - All logic in component classes
- **On-demand processing**: Rows/columns processed only when iterating (no pre-processing)
- **Performance**: No unnecessary pre-processing, better performance
- **Removed**: `DataTableViewData` service class - all logic moved to component classes
