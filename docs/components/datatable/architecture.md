# Architecture

## Modular Design

The DataTable system uses a modular, trait-based architecture to adhere to SOLID principles and ensure maintainability:

```
Livewire Component (extends Datatable)
    ↓ (modular responsibilities via traits)
    - HasDatatableLivewireActions (row/bulk actions, modals)
    - HasDatatableLivewireFilters (filter state, rendering)
    - HasDatatableLivewirePagination (per-page, navigation)
    - HasDatatableLivewirePreferences (user preference persistence)
    - HasDatatableLivewireQueryParameters (URL state handling)
    - HasDatatableLivewireRendering (header, row, column rendering)
    - HasDatatableLivewireSelection (row selection management)
    - HasDatatableLivewireSorting (sort column/direction logic)
    ↓
Shared Template (resources/views/components/datatable.blade.php)
    ↓ (orchestrates sub-views via backend methods)
Modular Sub-views (resources/views/components/datatable/*.blade.php)
    ↓
DataTableQueryBuilder (auto-joins, search, filter, sort)
    ↓
Alpine.js Component (UI-only: filters panel, modals)
```

## Modern Livewire Features

The architecture fully leverages Livewire 4 capabilities:
- **`#[Computed]` Properties**: Used extensively for state derived from the render loop (columns, filters, selection state), ensuring efficient caching.
- **Optimistic UI**: `wire:model` (deferred) and `wire:show` allow instant user interaction without immediate server roundtrips.
- **Strict Typing**: All methods and properties are strictly typed for robustness.

**Key Benefits:**
- ✅ **SOLID**: Clear separation of responsibilities into focused traits
- ✅ **Readability**: Smaller, more manageable code files
- ✅ **Testability**: Logic isolated in traits is easier to test
- ✅ **DRY**: Shared logic reused across all datatables
- ✅ **Consistency**: Unified behavior for sorting, filtering, and selection

**Note:** This component-based architecture replaced the previous trait-based approach (`WithDataTable` trait). All DataTable components now extend the `Datatable` base class, which provides all the functionality previously in the trait. Individual datatables only contain PHP configuration - no HTML is needed in component files.
