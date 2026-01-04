# Loading Component

A centralized loading spinner component for consistent loading UI across the application.

## Location

`resources/views/components/ui/loading.blade.php`

## Usage

```blade
{{-- Default: Large primary spinner, centered with padding --}}
<x-ui.loading></x-ui.loading>

{{-- Small spinner, not centered --}}
<x-ui.loading size="sm" :centered="false"></x-ui.loading>

{{-- Custom variant and color --}}
<x-ui.loading variant="dots" color="secondary"></x-ui.loading>

{{-- Inline in a button --}}
<button>
    <x-ui.loading size="xs" :centered="false" variant="spinner"></x-ui.loading>
    Loading...
</button>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `size` | string | `lg` | Size: `xs`, `sm`, `md`, `lg`, `xl` |
| `variant` | string | `spinner` | Style: `spinner`, `dots`, `ring`, `ball`, `bars`, `infinity` |
| `color` | string | `primary` | Color: `primary`, `secondary`, `accent`, `neutral`, `info`, `success`, `warning`, `error` |
| `centered` | bool | `true` | Wrap in flex container with centering |
| `padding` | string | `py-12` | Padding class for centered container |

## Examples

### Modal Loading State

```blade
<div x-show="isLoading" x-cloak>
    <x-ui.loading></x-ui.loading>
</div>
```

### Button Loading State

```blade
<button wire:loading.attr="disabled">
    <span wire:loading>
        <x-ui.loading size="xs" :centered="false"></x-ui.loading>
    </span>
    <span wire:loading.remove>Submit</span>
</button>
```

### Custom Styled

```blade
{{-- Error color with dots animation --}}
<x-ui.loading color="error" variant="dots" size="md"></x-ui.loading>

{{-- Small inline spinner --}}
<x-ui.loading size="sm" :centered="false" color="secondary"></x-ui.loading>
```

## CSS Classes Used

The component uses DaisyUI's loading classes:
- `loading` - Base class
- `loading-{variant}` - Animation variant
- `loading-{size}` - Size modifier
- `text-{color}` - Color modifier
