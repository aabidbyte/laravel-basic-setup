# Page Layout Title Pattern

> **Purpose**: Standardize how page titles and subtitles are rendered across the application to ensure consistency and reduce boilerplate.

## Overview

The application uses an automatic title handling system powered by `BasePageComponent`. Instead of manually rendering `<x-ui.title>` or headers in every Blade template, the layout handles this automatically using shared view variables.

## How It Works

1.  **State Management**: `BasePageComponent` holds `$pageTitle` and `$pageSubtitle` properties.
2.  **Variable Sharing**: The component automatically shares these fields globally with all views as `$pageTitle` and `$pageSubtitle` via `View::share()`.
3.  **Automatic Rendering**: The `x-layouts.page` component checks for these variables and renders the standardized header (Title + Subtitle) automatically.

### Exceptions

The following pages manage their own headers and **DO NOT** use this automatic rendering pattern:
- **Settings Pages** (Account, Security, etc.)
- **Dashboard**
- **Auth Pages**

## Implementation Guide for Developers

### 1. In Your Livewire Component (PHP)
Set the title and subtitle in your component. Use `mount()` or method calls to update them.

```php
// app/Livewire/Pages/MyPage.php
class MyPage extends BasePageComponent
{
    public function mount()
    {
        // Simple string
        $this->pageTitle = 'My Page Title';
        
        // Or translation key (automatically translated)
        $this->pageSubtitle = 'pages.my_page.subtitle';
    }
}
```

### 2. In Your Blade Template (View)
**DO NOT** render the title manually. Let the layout handle it.
**DO NOT** wrap content in `<x-layouts.app>` **if this is a Livewire Component** (Livewire config handles this).
*Standard Blade views returned from Controllers MUST still be wrapped.*

```blade
{{-- ❌ INCORRECT: Manual rendering --}}
<x-layouts.page>
    <x-ui.title>{{ $this->pageTitle }}</x-ui.title> {{-- Remove this --}}
    
    <div>Content...</div>
</x-layouts.page>


{{-- ✅ CORRECT: Layout handles it --}}
<x-layouts.page>
    {{-- Title is rendered automatically by the layout --}}
    {{-- Layout wrapper is injected automatically by Livewire --}}
    
    <div>Content...</div>
</x-layouts.page>
```

## Migration Checklist

When creating new pages or refactoring old ones:
- [ ] Ensure Component extends `BasePageComponent`
- [ ] Set `$pageTitle` (and optionally `$pageSubtitle`) in PHP
- [ ] Remove `<x-ui.title>` or `<h1>` blocks from the Blade template
- [ ] Verify title appears correctly in the layout header
