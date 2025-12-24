## Loading States

Show loading states during Livewire requests.

### data-loading Attribute

Every element that triggers a request gets `data-loading`:

```blade
<button wire:click="save" class="data-loading:opacity-50">
    Save
</button>
```

### wire:loading Directive

Show content during loading:

```blade
<button wire:click="save">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>
```

### Basic Usage

```blade
<div wire:loading>Loading...</div>
```

### Styling with Tailwind/CSS

Style with Tailwind:

```blade
<button wire:click="save" class="data-loading:opacity-50 data-loading:pointer-events-none">
    Save
</button>
```

Or with CSS:

```css
[data-loading] {
    opacity: 0.5;
    pointer-events: none;
}
```

### Advantages

-   Automatic: No manual state management
-   Performant: Uses CSS instead of JavaScript
-   Accessible: Works with screen readers

### Delays

Add delays:

```blade
<div wire:loading.delay>Loading...</div>
<div wire:loading.delay.shortest>Loading...</div>
```

### Targets

Target specific actions:

```blade
<div wire:loading wire:target="save">Saving...</div>
<div wire:loading wire:target="delete">Deleting...</div>
```

