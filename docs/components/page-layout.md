# Page Layout Component

The `x-layouts.page` component provides a unified layout structure for all CRUD views.

> [!IMPORTANT]
> This component must be used **inside** `x-layouts.app`, not as a wrapper replacement.

## Usage

### Plain Blade Files
To set the page title and subtitle for plain Blade files (which don't extend `BasePageComponent`), use the `setPageTitle()` helper at the top of the file.

```blade
@php
    setPageTitle(__('pages.users.index.title'), __('pages.users.index.description'));
@endphp

<x-layouts.app>
    <x-layouts.page backHref="{{ route('users.index') }}" :showBottomBar="true">
        <x-slot:topActions>
            <x-ui.button color="primary">Save</x-ui.button>
        </x-slot:topActions>

        {{-- Main content --}}

        <x-slot:bottomActions>
            <x-ui.button variant="ghost">Cancel</x-ui.button>
            <x-ui.button color="primary">Save</x-ui.button>
        </x-slot:bottomActions>
    </x-layouts.page>
</x-layouts.app>
```

### Livewire SFC Files
For Livewire Single File Components that extend `BasePageComponent`, use directly without wrapper:
```blade
<?php
// PHP class definition...
new class extends BasePageComponent {
    // ...
}; ?>

<x-layouts.page backHref="{{ route('users.index') }}">
    <x-slot:topActions>
        <x-ui.button color="primary">Edit</x-ui.button>
    </x-slot:topActions>

    {{-- Content --}}
</x-layouts.page>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `backHref` | `string` | `null` | URL for back button. Shows in top-left if set. |
| `backLabel` | `string` | `'Back'` | Label for back button (uses `ui.actions.back` translation). |
| `showBottomBar` | `bool` | `false` | Whether to show bottom action bar. |

## Slots

| Slot | Description |
|------|-------------|
| `topActions` | Actions for top-right (e.g., Create, Save buttons). |
| `default` | Main content area. |
| `bottomLeft` | Override bottom-left (instead of back button). |
| `bottomActions` | Actions for bottom-right (e.g., Save, Cancel buttons). |

## Responsive Design

The component is fully responsive:
- **Mobile**: Stacks vertically, hides back button text (icon only)
- **Tablet+**: Horizontal layout with full labels

## Notes

- Titles are handled by `BasePageComponent`.
- You can override `getPageTitle(): string` in your component to provide a dynamic title (e.g. including user name).
- If `getPageTitle()` is not overridden, it falls back to the `$pageTitle` property.
- All actions and slots are optional.
- For plain Blade files, wrap with `x-layouts.app`.
- For Livewire SFCs extending `BasePageComponent`, the layout is handled by the base class.
