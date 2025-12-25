## Share Button

**Location:** `resources/views/components/ui/share-button.blade.php`

**Component Name:** `<x-ui.share-button>`

### Description

A reusable share button component that copies a URL to the clipboard with visual feedback via tooltip. The component handles clipboard operations with fallback support for older browsers and non-secure contexts.

### Props

| Prop          | Type           | Default                    | Description                                                      |
| ------------- | -------------- | -------------------------- | ---------------------------------------------------------------- |
| `url`         | `string\|null` | `null`                     | URL to share. If null, uses `request()->fullUrl()`. For DataTables, use `$this->getShareUrl()` |
| `tooltipText` | `string\|null` | `null`                     | Custom tooltip text. Defaults to `ui.table.share_page`           |
| `size`        | `string`       | `'md'`                     | Button size: `xs`, `sm`, `md`, `lg`                              |
| `style`       | `string`       | `'ghost'`                  | Button style (DaisyUI button variant)                            |

### Features

- **Clipboard API Support**: Uses modern Clipboard API when available
- **Fallback Support**: Falls back to `document.execCommand('copy')` for older browsers
- **Dynamic Tooltip**: Shows "URL copied to clipboard!" after successful copy
- **Error Handling**: Gracefully handles copy failures
- **Secure Context**: Works in both HTTPS and HTTP contexts

### Usage Examples

#### Basic Usage (Uses Current URL)

```blade
<x-ui.share-button></x-ui.share-button>
```

**Note:** For DataTable components, always use `$this->getShareUrl()` to ensure all state (filters, search, sort, pagination) is included:

```blade
<x-ui.share-button :url="$this->getShareUrl()"></x-ui.share-button>
```

#### With Custom URL

```blade
<x-ui.share-button url="https://example.com/page?filter=value"></x-ui.share-button>
```

#### With Custom Size and Style

```blade
<x-ui.share-button size="sm" style="ghost"></x-ui.share-button>
```

#### In DataTable (Next to Filters)

The share button automatically uses the component's `getShareUrl()` method to generate URLs with all query parameters:

```blade
<div class="flex items-center gap-2">
    <x-ui.button @click="toggleFilters()" type="button" style="ghost" size="md">
        {{ __('ui.table.filters') }}
    </x-ui.button>
    
    <x-ui.share-button :url="$this->getShareUrl()" size="md" style="ghost"></x-ui.share-button>
</div>
```

**Note:** The `getShareUrl()` method generates a URL with all current state (search, filters, sort, per_page, page) even though the browser URL stays clean.

#### In Pagination (With Page Number)

The pagination view automatically passes the correct share URL:

```blade
@php
    // Get share URL from component (includes all query params: search, sort, filters, per_page, page)
    $shareUrl = $this->getShareUrl($paginator->currentPage());
@endphp

<x-ui.share-button :url="$shareUrl" size="sm" style="ghost"></x-ui.share-button>
```

### How It Works

1. **Clipboard Detection**: Checks if `navigator.clipboard` is available
2. **Modern API**: Uses `navigator.clipboard.writeText()` if available
3. **Fallback**: Uses `document.execCommand('copy')` if Clipboard API is unavailable
4. **Feedback**: Updates tooltip text to show success/error state
5. **Reset**: Tooltip resets to original text after 2 seconds

### Translation Keys

The component uses the following translation keys:
- `ui.table.share_page` - Default tooltip text ("Share page with filters")
- `ui.table.url_copied` - Success message ("URL copied to clipboard!")
- `ui.table.copy_failed` - Error message (optional, defaults to "Copy failed")

### Browser Compatibility

- **Modern Browsers**: Uses Clipboard API (requires secure context - HTTPS)
- **Older Browsers**: Falls back to `document.execCommand('copy')`
- **Non-Secure Contexts**: Automatically uses fallback method

### Best Practices

1. **Use Appropriate Size**: Match button size with surrounding elements
2. **Provide Context**: Use custom `tooltipText` when sharing specific content
3. **Include Query Params**: When sharing filtered/sorted views, include all query parameters
4. **Test Fallback**: Test in non-HTTPS environments to ensure fallback works

---

