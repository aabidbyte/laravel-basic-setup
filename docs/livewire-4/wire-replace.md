# wire:replace

`wire:replace` is a specialized directive used to tell Livewire to replace the element entirely on every render, rather than attempting to morph it.

## Basic Usage

```blade
<div wire:replace>
    <!-- Always replace this HTML block -->
</div>
```

Useful for complex components that "break" when morphing, such as nested iframes or specific canvas elements.
