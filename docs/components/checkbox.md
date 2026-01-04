# Checkbox Component

The Checkbox component allows users to select one or multiple options from a set. It uses DaisyUI's checkbox styling.

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `label` | `string` | `null` | The label text displayed next to the checkbox. |
| `description` | `string` | `null` | Helper text displayed below the checkbox. |
| `checked` | `boolean` | `false` | Whether the checkbox is checked by default. |
| `color` | `string` | `'primary'` | The color variant of the checkbox (primary, secondary, etc.). |
| `value` | `string\|number` | `null` | The value attribute of the checkbox. |
| `size` | `string` | `null` | The size of the checkbox (xs, sm, md, lg). |

## Usage Examples

### Basic Usage

```blade
<x-ui.checkbox label="Remember me" />
```

### With Livewire Binding

```blade
<x-ui.checkbox label="Terms accepted" wire:model="terms" />
```

### With Custom Color and Size

```blade
<x-ui.checkbox
    label="High Importance"
    color="error"
    size="lg"
/>
```

### With Description

```blade
<x-ui.checkbox
    label="Subscribe to newsletter"
    description="Receive weekly updates about our products."
/>
```
