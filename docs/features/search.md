# Search Functionality

The application provides powerful search and highlight functionality across select components, datatables, and other search-enabled interfaces.

---

## Features

✅ **Real-time filtering** - Results update as you type  
✅ **Smart highlighting** - Matching text highlighted in yellow  
✅ **Performance optimized** - Handles large datasets smoothly  
✅ **Auto-enabled** - Appears when needed (>10 items)  
✅ **Keyboard friendly** - Auto-focus and navigation support  

---

## Select Component Search

### Basic Usage

Search is automatically enabled on select components when there are more than 10 options:

```blade
<x-ui.select 
    wire:model="user_id"
    :options="$users"
    label="Select User">
</x-ui.select>
```

When opened, a search input will appear if `count($users) > 10`.

### Custom Threshold

Control when search appears:

```blade
<x-ui.select 
    wire:model="status"
    :options="$statuses"
    label="Status"
    :searchThreshold="5">
</x-ui.select>
```

### Disable Search

```blade
<x-ui.select 
    :searchable="false"
    :options="$items">
</x-ui.select>
```

### Server-Side Search

For very large datasets, delegate search to the backend:

```blade
<x-ui.select 
    wire:model="product_id"
    :options="$products"
    searchMethod="searchProducts">
</x-ui.select>
```

```php
// In your Livewire component
public function searchProducts(string $query = ''): array
{
    return Product::where('name', 'like', "%{$query}%")
        ->limit(50)
        ->pluck('name', 'id')
        ->toArray();
}
```

---

## Datatable Search

Datatables include a global search input by default:

```php
class UsersTable extends DataTable
{
    public function query(): Builder
    {
        return User::query();
    }
    
    public function columns(): array
    {
        return [
            Column::make('name')->searchable(),
            Column::make('email')->searchable(),
        ];
    }
}
```

Matching text in searchable columns will be highlighted automatically.

---

## Performance

### Small Lists (<100 items)
- Instant filtering
- No loading indicators needed

### Medium Lists (100-1000 items)
- First 50 results appear immediately
- Remaining results load in background
- No UI blocking

### Large Lists (>1000 items)
- Progressive loading with smooth UX
- Consider server-side search for >10k items

---

## Keyboard Shortcuts

| Key | Action |
|-----|--------|
| Type to search | Filter results |
| ↑ ↓ | Navigate options |
| Enter | Select highlighted option |
| Esc | Close dropdown |

---

## Highlighting

Matching text is highlighted with a yellow/warning background:

- **Case-insensitive** matching
- **Partial** matches highlighted
- **Multiple** matches per item
- **XSS-safe** rendering

---

## Configuration

### Search Options

```blade
<x-ui.select 
    :searchable="true"              {{-- Enable/disable search --}}
    :searchThreshold="10"           {{-- Min items to show search --}}
    :searchMethod="null"            {{-- Livewire method for server search --}}
    :searchDebounce="300"           {{-- Debounce delay (ms) --}}
    :searchPlaceholder="'Search...'"  {{-- Custom placeholder --}}
    :options="$items">
</x-ui.select>
```

### Translations

Search UI isfully localized. Keys defined in `lang/{locale}/table.php`:

- `search_options` - Search input placeholder
- `no_results_found` - Empty state message
- `clear_search` - Clear button text

---

## Browser Support

- ✅ Chrome / Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers

Uses `requestIdleCallback` with automatic fallback for older browsers.

---

## Examples

### User Selection
```blade
<x-ui.select 
    wire:model="assigned_to"
    :options="App\Models\User::pluck('name', 'id')"
    label="Assign To"
    placeholder="Select user">
</x-ui.select>
```

### Country Selection with Low Threshold
```blade
<x-ui.select 
    wire:model="country"
    :options="$countries"
    :searchThreshold="1"  {{-- Always show search --}}
    label="Country">
</x-ui.select>
```

### Product Search (Server-Side)
```blade
<x-ui.select 
    wire:model="product_id"
    :options="$initialProducts"
    searchMethod="searchProducts"
    :searchDebounce="500"  {{-- Wait 500ms before searching --}}
    label="Product">
</x-ui.select>
```

---

## Accessibility

- Auto-focus on dropdown open
- Keyboard navigation support
- Screen reader compatible
- Mobile-optimized touch targets

---

## Related

- [Select Component API](../components/select.md)
- [Datatable Component](../components/datatable.md)
- [Technical Documentation](../AGENTS/search-system.md)
