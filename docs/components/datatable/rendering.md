# Rendering API

The Datatable uses backend-driven rendering to keep the main template clean and allow for easy customization.

## Rendering Methods

These methods are defined in the `Datatable` base class and can be overridden in your component if you need custom UI for specific sections:

| Method | Description |
|--------|-------------|
| `renderColumn($columnData, $row)` | Renders a single cell's content. |
| `renderRowActions($row)` | Renders the row actions dropdown for a specific row. |

## Modular Sub-views

The rendering methods use dedicated sub-views located in `resources/views/components/datatable/`:

- `filters.blade.php` - Search, bulk actions, filter panel
- `header.blade.php` - Table header with sorting
- `row.blade.php` - Table rows with selection and actions
- `actions.blade.php` - Row action dropdown
- `pagination.blade.php` - Pagination controls
