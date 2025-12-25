## Search

**Location:** `resources/views/components/ui/search.blade.php`

**Component Name:** `<x-ui.search>`

### Description

A specialized search input component that includes a magnifying glass icon inside the input field. Built on top of the input component with enhanced visual design for search functionality.

### Props

| Prop      | Type     | Default   | Description                                                                 |
| --------- | -------- | --------- | --------------------------------------------------------------------------- |
| `label`   | `string` | `null`    | Label text to display above the input field                                 |
| `error`   | `string` | `null`    | Error message to display below the input field                              |
| `required`| `bool`   | `false`   | Whether the field is required (shows asterisk in label)                    |

### Usage Examples

#### Basic Search Input

```blade
<x-ui.search wire:model.live.debounce.300ms="search"
    placeholder="Search...">
</x-ui.search>
```

#### Search with Label

```blade
<x-ui.search wire:model.live="query"
    label="Search Users"
    placeholder="Enter name or email...">
</x-ui.search>
```

#### Search with Error

```blade
<x-ui.search wire:model="searchTerm"
    error="Please enter at least 3 characters"
    placeholder="Search products...">
</x-ui.search>
```

#### In Datatable

```blade
<x-ui.search wire:model.live.debounce.300ms="search"
    placeholder="{{ __('ui.table.search_placeholder') }}">
</x-ui.search>
```

### Features

- **Icon Integration**: Includes a magnifying glass icon positioned inside the input field
- **Consistent Styling**: Follows the same design patterns as other UI components
- **Error Handling**: Supports error states with red border and error message
- **Label Support**: Optional label display with required indicator support
- **Accessibility**: Proper ARIA attributes and semantic HTML structure

### Implementation Details

- **Icon**: Uses `magnifying-glass` icon from the icon component system
- **Styling**: Adds left padding (`pl-10`) to accommodate the icon
- **Layout**: Uses relative positioning with absolute positioned icon
- **Colors**: Icon uses semi-transparent color (`text-base-content/60`) for subtle appearance

### Accessibility

- Maintains all accessibility features from the base input component
- Icon is decorative and marked as `pointer-events-none`
- Proper label association and error announcements

---


