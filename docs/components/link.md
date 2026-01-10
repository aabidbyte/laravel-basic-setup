# Link Component

Standardized anchor link with consistent styling and automatic SPA navigation.

## Usage

```blade
{{-- Basic link (primary color, hover underline) --}}
<x-ui.link href="{{ route('login') }}">Login</x-ui.link>

{{-- Different colors --}}
<x-ui.link href="/terms" color="neutral">Terms of Service</x-ui.link>
<x-ui.link href="/danger" color="error">Delete Account</x-ui.link>

{{-- Always underlined --}}
<x-ui.link href="/help" underline>Help</x-ui.link>

{{-- External link (no wire:navigate) --}}
<x-ui.link href="https://external.com" :navigate="false">External</x-ui.link>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `href` | `string` | `#` | URL to link to |
| `variant` | `string` | `null` | Link visual style (future use) |
| `color` | `string` | `primary` | Semantic color variant |
| `underline` | `bool` | `false` | Always show underline (vs hover-only) |
| `navigate` | `bool` | `true` | Use wire:navigate for SPA navigation |

## Color Variants

`primary`, `secondary`, `accent`, `neutral`, `info`, `success`, `warning`, `error`

## Migration from Raw HTML

```blade
{{-- Before --}}
<a href="{{ route('register') }}" wire:navigate class="link link-primary">
    Sign up
</a>

{{-- After --}}
<x-ui.link href="{{ route('register') }}">Sign up</x-ui.link>
```
