# Title Component

Standardized heading component for consistent typography across the application.

## Usage

```blade
{{-- Basic usage --}}
<x-ui.title level="1">Page Title</x-ui.title>
<x-ui.title level="2">Section Title</x-ui.title>
<x-ui.title level="3">Subsection Title</x-ui.title>

{{-- With subtitle --}}
<x-ui.title level="1" subtitle="Description text">Page Title</x-ui.title>

{{-- With custom size --}}
<x-ui.title level="2" size="xl">Large Section</x-ui.title>

{{-- With additional classes --}}
<x-ui.title level="3" class="text-base-content/70 border-b pb-2">Styled Title</x-ui.title>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `level` | `int` | `2` | Heading level (1-6), determines HTML tag |
| `size` | `string` | `null` | Visual size override: `xs`, `sm`, `md`, `lg`, `xl`, `2xl`, `3xl`, `4xl` |
| `class` | `string` | `''` | Additional CSS classes |
| `subtitle` | `string` | `null` | Optional subtitle text below heading |

## Default Styles by Level

| Level | Tag | Default Classes |
|-------|-----|-----------------|
| 1 | `<h1>` | `text-4xl font-bold` |
| 2 | `<h2>` | `text-2xl font-semibold` |
| 3 | `<h3>` | `text-lg font-semibold` |
| 4 | `<h4>` | `text-base font-medium` |
| 5 | `<h5>` | `text-sm font-medium` |
| 6 | `<h6>` | `text-xs font-medium` |

## Migration from Raw HTML

```blade
{{-- Before --}}
<h2 class="card-title text-2xl">{{ $user->name }}</h2>
<h3 class="text-lg font-semibold text-base-content/70 border-b pb-2">Section</h3>

{{-- After --}}
<x-ui.title level="2">{{ $user->name }}</x-ui.title>
<x-ui.title level="3" class="text-base-content/70 border-b pb-2">Section</x-ui.title>
```
