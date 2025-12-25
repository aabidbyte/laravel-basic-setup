## Tooltip

**Location:** `resources/views/components/ui/tooltip.blade.php`

**Component Name:** `<x-ui.tooltip>`

### Description

A centralized tooltip component using DaisyUI's tooltip classes. Tooltips provide additional information when users hover over or interact with elements.

### Props

| Prop       | Type           | Default | Description                                                      |
| ---------- | -------------- | ------- | ---------------------------------------------------------------- |
| `text`     | `string\|null` | `null`  | Tooltip text to display                                           |
| `position` | `string`       | `'top'` | Tooltip position: `top`, `bottom`, `left`, `right`                 |
| `open`     | `bool`         | `false` | Whether tooltip is open (for controlled state) - currently unused |

### Usage Examples

#### Basic Tooltip

```blade
<x-ui.tooltip text="This is a tooltip">
    <button class="btn">Hover me</button>
</x-ui.tooltip>
```

#### With Different Positions

```blade
<x-ui.tooltip text="Tooltip on top" position="top">
    <button class="btn">Top</button>
</x-ui.tooltip>

<x-ui.tooltip text="Tooltip on bottom" position="bottom">
    <button class="btn">Bottom</button>
</x-ui.tooltip>

<x-ui.tooltip text="Tooltip on left" position="left">
    <button class="btn">Left</button>
</x-ui.tooltip>

<x-ui.tooltip text="Tooltip on right" position="right">
    <button class="btn">Right</button>
</x-ui.tooltip>
```

#### Dynamic Tooltip with Alpine.js

For tooltips that change dynamically (e.g., showing "Copied!" after an action):

```blade
<div 
    x-data="{ 
        tooltipText: 'Copy to clipboard',
        copy() {
            // ... copy logic ...
            this.tooltipText = 'Copied!';
            setTimeout(() => { this.tooltipText = 'Copy to clipboard'; }, 2000);
        }
    }"
>
    <div class="tooltip tooltip-top" x-bind:data-tip="tooltipText">
        <button @click="copy()" class="btn">Copy</button>
    </div>
</div>
```

**Note:** When using dynamic tooltips with Alpine.js, use `x-bind:data-tip` directly on the tooltip div rather than the component, as the component's `text` prop is static.

### DaisyUI Tooltip Classes

The component uses DaisyUI's tooltip classes:
- `tooltip` - Base tooltip class
- `tooltip-top` - Tooltip appears above
- `tooltip-bottom` - Tooltip appears below
- `tooltip-left` - Tooltip appears on the left
- `tooltip-right` - Tooltip appears on the right

The tooltip text is set via the `data-tip` attribute, which DaisyUI uses to display the tooltip.

### Best Practices

1. **Keep tooltip text concise** - Tooltips should be brief and informative
2. **Use appropriate positions** - Choose positions that don't obstruct important content
3. **For dynamic tooltips** - Use Alpine.js `x-bind:data-tip` directly instead of the component when text changes
4. **Accessibility** - Tooltips provide additional context but shouldn't be the only way to convey critical information

### Integration with Other Components

Tooltips work well with:
- Buttons (for action descriptions)
- Icons (for icon meanings)
- Form inputs (for help text)
- Navigation items (for descriptions)

---

