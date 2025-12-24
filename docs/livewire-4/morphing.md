## Morphing

Morphing is Livewire's algorithm for updating the DOM efficiently.

### How It Works

Livewire compares the old and new HTML and updates only what changed.

### Shortcomings

-   Can't morph certain elements (scripts, styles)
-   Requires `wire:key` in loops
-   May have issues with third-party libraries

### Internal Look-Ahead

Livewire uses look-ahead to optimize morphing.

### Morph Markers

Use morph markers:

```blade
<div wire:key="unique-id">
    <!-- Content -->
</div>
```

### Wrapping Conditionals

Wrap conditionals:

```blade
<div wire:key="conditional-{{ $condition }}">
    @if ($condition)
        <!-- Content -->
    @endif
</div>
```

### wire:replace

Replace entire element:

```blade
<div wire:replace>
    <!-- Entire div is replaced -->
</div>
```

