# Toggle Component

The Toggle component is a variation of the checkbox that allows users to toggle a state on or off. It uses DaisyUI's toggle styling.

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `label` | `string` | `null` | The label text displayed next to the toggle. |
| `description` | `string` | `null` | Helper text displayed below the toggle. |
| `checked` | `boolean` | `false` | Whether the toggle is checked by default. |
| `color` | `string` | `'primary'` | The color variant of the toggle (primary, success, etc.). |
| `value` | `string\|number` | `null` | The value attribute of the input. |
| `size` | `string` | `null` | The size of the toggle (xs, sm, md, lg). |

## Usage Examples

### Basic Usage

```blade
<x-ui.toggle label="Enable Notifications" />
```

### With Livewire Binding

```blade
<x-ui.toggle label="Active Status" wire:model="isActive" />
```

### With Custom Color

```blade
<x-ui.toggle
    label="Account Active"
    color="success"
    checked
/>
```

### With Description

```blade
<x-ui.toggle
    label="Dark Mode"
    description="Switch between light and dark themes."
/>
```
