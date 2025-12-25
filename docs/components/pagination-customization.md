## Pagination Customization

This document explains how to customize pagination in Laravel, specifically for DataTable components.

### Current Implementation

The DataTable component uses a custom pagination view that removes query strings from pagination URLs while providing a share button:

```blade
{{ $this->rows->links('components.datatable.pagination') }}
```

**Location:** `resources/views/components/datatable/pagination.blade.php`

**Key Features:**
- **Clean URLs**: Pagination links use Livewire's `wire:click` methods (no query strings in URL)
- **Share Button**: Includes a share button that generates a URL with all query strings (filters, search, sort, page) for sharing
- **Tooltip Feedback**: Share button shows "URL copied to clipboard!" tooltip after copying
- **DaisyUI Styling**: Uses DaisyUI button classes for consistent styling
- **Clipboard API**: Uses modern Clipboard API with fallback for older browsers and non-secure contexts

### Key Customization Options

#### 1. Clean URLs (Current Implementation)

**DataTables use clean URLs without query strings.** State is maintained in Livewire component state:

```blade
{{ $this->rows->links('components.datatable.pagination') }}
```

**What it does:**
- Clean URLs - No query strings appear in browser URL
- State maintained in Livewire component (search, filters, sort, pagination)
- Pagination uses `wire:click` methods instead of URL links
- Share button generates URLs with query strings for sharing

#### 2. Adjust Pagination Link Window

Control how many page numbers are shown on each side of the current page:

```blade
{{ $paginator->onEachSide(3)->links() }}
```

**Options:**
- `onEachSide(1)` - Shows 1 page on each side (minimal)
- `onEachSide(3)` - Shows 3 pages on each side (default)
- `onEachSide(5)` - Shows 5 pages on each side (more links)

**Example:**
- Current page: 10
- `onEachSide(3)` shows: `... 7 8 9 [10] 11 12 13 ...`
- `onEachSide(5)` shows: `... 5 6 7 8 9 [10] 11 12 13 14 15 ...`

#### 3. Customize Pagination View

Publish and customize the pagination view files:

```bash
php artisan vendor:publish --tag=laravel-pagination
```

This creates views in `resources/views/vendor/pagination/`:
- `tailwind.blade.php` - Default Tailwind CSS view
- `simple-tailwind.blade.php` - Simple pagination view
- `bootstrap-4.blade.php` - Bootstrap 4 view
- `bootstrap-5.blade.php` - Bootstrap 5 view

**Customize the view:**
```blade
{{ $paginator->links('vendor.pagination.custom') }}
```

#### 4. Set Default Pagination View

Set a default view for all paginators in `AppServiceProvider`:

```php
use Illuminate\Pagination\Paginator;

public function boot(): void
{
    Paginator::defaultView('vendor.pagination.custom');
    Paginator::defaultSimpleView('vendor.pagination.simple-custom');
}
```

#### 5. Customize Pagination URLs

**Change the base path:**
```php
$paginator->withPath('/admin/users')->links();
```

**Append query string values:**
```php
$paginator->appends(['sort' => 'name', 'direction' => 'asc'])->links();
```

**Append hash fragments:**
```php
$paginator->fragment('users')->links();
// Generates: /users?page=2#users
```

### DataTable-Specific Recommendations

For DataTable components, use the custom pagination view:

```blade
{{ $this->rows->links('components.datatable.pagination') }}
```

This provides:
- ✅ Clean URLs (no query strings in pagination links)
- ✅ Share functionality (generates URL with all query parameters)
- ✅ Search terms preserved in share URL
- ✅ Filters preserved in share URL
- ✅ Sort order preserved in share URL
- ✅ Per page selection preserved in share URL
- ✅ Current page number included in share URL

### Advanced: Custom Pagination View for DataTables

If you need a completely custom pagination view for datatables:

1. **Publish pagination views:**
   ```bash
   php artisan vendor:publish --tag=laravel-pagination
   ```

2. **Copy and customize:**
   ```bash
   cp resources/views/vendor/pagination/tailwind.blade.php \
      resources/views/vendor/pagination/datatable.blade.php
   ```

3. **Use in datatable:**
   ```blade
   {{ $this->rows->withQueryString()->links('vendor.pagination.datatable') }}
   ```

### Example: Full Customization

```blade
{{-- Custom pagination with query string, adjusted window, and custom view --}}
{{ $this->rows
    ->withQueryString()
    ->onEachSide(2)
    ->links('vendor.pagination.datatable') }}
```

### Best Practices

1. **Use clean URLs** - State is maintained in Livewire component, not URL
2. **Use share button** - Generate shareable URLs with query strings when needed
3. **Use `onEachSide()`** - Control link density based on your design (if customizing)
4. **Test pagination** - Ensure state persists correctly when navigating pages

---

