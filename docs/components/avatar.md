# Avatar Component

Unified avatar component that handles both image and initials display.

## Usage

```blade
{{-- With user model (recommended) --}}
<x-ui.avatar :user="$user" size="md"></x-ui.avatar>

{{-- With name only (generates initials) --}}
<x-ui.avatar name="John Doe" size="lg"></x-ui.avatar>

{{-- With image URL --}}
<x-ui.avatar src="/path/to/avatar.jpg" name="John Doe"></x-ui.avatar>

{{-- Different shapes --}}
<x-ui.avatar :user="$user" shape="square"></x-ui.avatar>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `user` | `User\|null` | `null` | User model (extracts initials and avatar_url) |
| `name` | `string\|null` | `null` | Name for initials fallback |
| `src` | `string\|null` | `null` | Image URL (overrides user->avatar_url) |
| `size` | `string` | `md` | Size: `xs`, `sm`, `md`, `lg`, `xl` |
| `shape` | `string` | `circle` | Shape: `circle`, `square` |
| `placeholder` | `bool` | `true` | Show placeholder styling when no image |

## Size Reference

| Size | Dimensions | Text |
|------|------------|------|
| `xs` | 24px | text-xs |
| `sm` | 32px | text-sm |
| `md` | 40px | text-base |
| `lg` | 64px | text-xl |
| `xl` | 96px | text-2xl |

## Migration from Raw HTML

```blade
{{-- Before --}}
<div class="avatar placeholder">
    <div class="bg-primary text-primary-content rounded-full w-16">
        <span class="text-xl">{{ $user->initials() }}</span>
    </div>
</div>

{{-- After --}}
<x-ui.avatar :user="$user" size="lg"></x-ui.avatar>
```
