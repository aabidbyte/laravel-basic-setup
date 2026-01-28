# Label Component

The `x-ui.label` component provides a consistent and premium way to render form labels across the application.

## Props

{{--
    Component Props:
    - for: Optional ID of the associated form element.
    - required: Boolean, if true displays a red asterisk.
    - text: Optional string for the label text (can also use slot).
--}}

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `for` | `string` | `null` | The ID of the input this label is for. |
| `required` | `boolean` | `false` | Whether the field is mandatory. |
| `text` | `string` | `null` | The label text (alternative to slot). |
| `labelAppend` | `slot` | `null` | Content to display on the far right (e.g., "Forgot Password" link). |

## Usage

### Simple Label
```blade
<x-ui.label text="Email Address" />
```

### With Slot
```blade
<x-ui.label>
    Password
</x-ui.label>
```

### Required Field
```blade
<x-ui.label for="email" :required="true">
    Email Address
</x-ui.label>
```

### With Associated Input
```blade
<div class="form-control">
    <x-ui.label for="username" text="Username" />
    <x-ui.input id="username" name="username" />
</div>
```

## Features

- **Semantic HTML**: Uses the `<label>` tag for accessibility.
- **Premium Styling**: Uses bold weighting and consistent spacing.
- **Translatable Required Tooltip**: The asterisk includes a tooltip for the "required" state.
- **Flexibility**: Can be used as a self-closing tag with the `text` prop or as a wrapper for more complex content.
