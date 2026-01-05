# Empty State Component

Standardized empty state display for lists, tables, and content areas.

## Usage

```blade
{{-- Basic usage --}}
<x-ui.empty-state
    icon="bell"
    :description="__('ui.notifications.empty')"
></x-ui.empty-state>

{{-- With title --}}
<x-ui.empty-state
    icon="users"
    title="No users found"
    description="Try adjusting your search criteria"
></x-ui.empty-state>

{{-- With action slot --}}
<x-ui.empty-state
    icon="folder"
    description="Get started by creating your first project"
>
    <x-ui.button color="primary">Create Project</x-ui.button>
</x-ui.empty-state>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `icon` | `string` | `inbox` | Icon name (Heroicons) |
| `title` | `string\|null` | `null` | Optional title text |
| `description` | `string\|null` | `null` | Description text |
| `iconSize` | `string` | `h-12 w-12` | Icon size classes |
| `iconClass` | `string` | `opacity-50 mb-4` | Additional icon classes |
| `class` | `string` | `''` | Additional container classes |

## Migration from Raw HTML

```blade
{{-- Before --}}
<div class="card bg-base-200">
    <div class="card-body text-center">
        <x-ui.icon name="bell" class="h-12 w-12 mx-auto opacity-50 mb-4"></x-ui.icon>
        <p class="text-base-content opacity-60">No notifications</p>
    </div>
</div>

{{-- After --}}
<x-ui.empty-state
    icon="bell"
    description="No notifications"
></x-ui.empty-state>
```
