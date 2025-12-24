## Button

**Location:** `resources/views/components/ui/button.blade.php`

**Component Name:** `<x-ui.button>`

### Description

A centralized button component that provides consistent styling using DaisyUI button classes. Supports combining style variants (solid, outline, ghost, etc.) with colors (primary, error, success, etc.) for flexible button styling.

**Smart Color Defaults**: When using the deprecated `variant` prop, the component automatically sets the appropriate color. For example, `variant="error"` will default to `color="error"` unless an explicit color is provided.

### Props

| Prop      | Type     | Default   | Description                                                                                                                                                                          |
| --------- | -------- | --------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `style`   | `string` | `'solid'` | Button style: `solid`, `outline`, `ghost`, `link`, `soft`, `dash`                                                                                                                    |
| `color`   | `string` | `null`    | Button color: `primary`, `secondary`, `accent`, `neutral`, `info`, `success`, `warning`, `error`. Defaults to `'primary'` if not set.                                                |
| `variant` | `string` | `null`    | **Deprecated**: Use `style` and `color` instead. Legacy prop for backward compatibility. Automatically sets appropriate color (e.g., `variant="error"` defaults to `color="error"`). |
| `size`    | `string` | `'md'`    | Button size: `xs`, `sm`, `md`, `lg`, `xl`                                                                                                                                            |
| `type`    | `string` | `null`    | HTML button type: `button`, `submit`, `reset` (defaults to `button` if not specified)                                                                                                |

### Usage Examples

#### Basic Button

```blade
<x-ui.button>Click Me</x-ui.button>
```

#### Button Styles and Colors

```blade
{{-- Solid buttons (default style) --}}
<x-ui.button color="primary">Primary</x-ui.button>
<x-ui.button color="secondary">Secondary</x-ui.button>
<x-ui.button color="error">Delete</x-ui.button>
<x-ui.button color="success">Save</x-ui.button>

{{-- Outline buttons --}}
<x-ui.button style="outline" color="primary">Outline Primary</x-ui.button>
<x-ui.button style="outline" color="error">Outline Error</x-ui.button>

{{-- Ghost buttons --}}
<x-ui.button style="ghost" color="primary">Ghost Primary</x-ui.button>
<x-ui.button style="ghost" color="error">Ghost Error</x-ui.button>

{{-- Link buttons --}}
<x-ui.button style="link" color="primary">Link Button</x-ui.button>
```

#### Combining Style and Color

```blade
{{-- Ghost button with error color (btn-ghost btn-error) --}}
<x-ui.button style="ghost" color="error">Delete</x-ui.button>

{{-- Outline button with success color (btn-outline btn-success) --}}
<x-ui.button style="outline" color="success">Save</x-ui.button>

{{-- Soft button with warning color (btn-soft btn-warning) --}}
<x-ui.button style="soft" color="warning">Warning</x-ui.button>
```

#### Button Sizes

```blade
<x-ui.button size="xs">Extra Small</x-ui.button>
<x-ui.button size="sm">Small</x-ui.button>
<x-ui.button size="md">Medium</x-ui.button>
<x-ui.button size="lg">Large</x-ui.button>
<x-ui.button size="xl">Extra Large</x-ui.button>
```

#### Submit Button

```blade
<x-ui.button type="submit" color="primary">Submit Form</x-ui.button>
```

#### Button with Livewire

```blade
<x-ui.button wire:click="save" color="primary">Save</x-ui.button>
```

#### Button with Additional Attributes

```blade
<x-ui.button style="ghost" color="error" class="w-full" data-test="delete-button">
    Delete
</x-ui.button>
```

#### Backward Compatibility (Deprecated)

The `variant` prop is still supported for backward compatibility but is deprecated. It maps to appropriate `style` and `color` combinations. **Important**: When using `variant`, the component automatically sets the appropriate color unless you explicitly provide one.

```blade
{{-- Old way (still works) --}}
{{-- variant="error" automatically defaults to color="error" --}}
<x-ui.button variant="error">Delete</x-ui.button>
<x-ui.button variant="primary">Primary</x-ui.button>
<x-ui.button variant="ghost">Ghost</x-ui.button>
<x-ui.button variant="success">Success</x-ui.button>

{{-- You can still override the color if needed --}}
<x-ui.button variant="error" color="primary">Error variant, primary color</x-ui.button>

{{-- New way (recommended) --}}
<x-ui.button color="primary">Primary</x-ui.button>
<x-ui.button style="ghost" color="primary">Ghost</x-ui.button>
<x-ui.button color="error">Error</x-ui.button>
```

### Implementation Details

-   Separates style variants (`btn-outline`, `btn-ghost`, etc.) from colors (`btn-primary`, `btn-error`, etc.)
-   Allows combining style and color for flexible button styling (e.g., `btn-ghost btn-error`)
-   Maps sizes to DaisyUI size classes (`btn-xs`, `btn-sm`, etc.)
-   Merges additional attributes (like `wire:click`, `class`, `data-*`) using Laravel's attribute merging
-   Defaults to `btn-primary` (solid primary) if no style/color is specified
-   Maintains backward compatibility with the deprecated `variant` prop
-   **Smart defaults**: When using `variant`, automatically sets the appropriate color (e.g., `variant="error"` → `color="error"`) unless an explicit color is provided

---

