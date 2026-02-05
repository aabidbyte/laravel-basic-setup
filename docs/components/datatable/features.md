# Features & UI

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
- **New**: Uses deferred `wire:model="selected"` on row checkboxes for seamless synchronization without server roundtrips on every click
- **New**: Uses dynamic `wire:key` on checkboxes and the select-all input to force re-render and prevent persistent visual "checked" states after clearing selection
- **New**: Bulk actions are now consolidated into a premium dropdown menu in the header, appearing only when items are selected
- **New**: Added a "Clear Selection" button in the header for quick reset

### Conditional UI Rendering

The DataTable component intelligently renders UI elements only when needed:

| Feature | Method | UI Elements Hidden When False |
|---------|--------|-------------------------------|
| Filters | `hasFilters()` | Filter button, filter panel, active filter badges |
| Bulk Actions | `hasBulkActions()` | Checkbox column in header and rows |
| Row Actions | `hasRowActions()` | Actions column in header and rows |

This is automatic - simply define (or omit) `getFilterDefinitions()`, `bulkActions()`, or `rowActions()` in your datatable class.

**Example: Minimal DataTable with no extra UI**:
```php
class MinimalTable extends Datatable
{
    protected function baseQuery(): Builder { return Model::query(); }
    
    protected function columns(): array
    {
        return [Column::make('Name', 'name')];
    }
    // No filters, bulk actions, or row actions defined
    // → No checkboxes, no filter button, no actions column
}
```

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
    confirmAction(data)     // Confirm and execute pending action
    cancelAction()          // Cancel pending action
}
```

### Available Events

These events are dispatched by the Livewire component or listened to by the Alpine component:

- `datatable:open-modal:{id}`: Open action modal
- `datatable:close-modal:{id}`: Close action modal
- `datatable:action-confirmed:{id}`: Confirm pending action
- `datatable:action-cancelled:{id}`: Cancel pending action
- `datatable:scroll-to-top:{id}`: Scroll table into view
- `datatable:clean-url:{id}`: Remove query parameters from URL

```blade
```blade
<div x-data="dataTable('{{ $this->getId() }}')"
    @datatable-action-confirmed.window="confirmAction($event.detail)"
    @datatable-action-cancelled.window="cancelAction()">

    @include('components.datatable.filters')

    <div class="overflow-x-auto">
        <table class="table">
            @include('components.datatable.header')

            <tbody>
                @foreach($this->rows as $row)
                    @include('components.datatable.row')
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $this->rows->links('components.datatable.pagination') }}
</div>
```
