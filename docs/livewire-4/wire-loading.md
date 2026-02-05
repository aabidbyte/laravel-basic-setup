# wire:loading

`wire:loading` allows you to toggle the visibility or styling of an element while a Livewire request is in flight.

## Basic Usage

```blade
<div wire:loading>
    Processing request...
</div>
```

## Targeting Specific Actions

You can use `wire:target` to only show the loading state for specific methods or inputs:

```blade
<button wire:click="save">Save</button>

<div wire:loading wire:target="save">
    Saving...
</div>
```

## Modifiers

-   `.remove`: Hides the element while loading (opposite of default).
-   `.class`: Toggles a class instead of showing/hiding the element.
-   `.attr`: Toggles an attribute instead (e.g., `disabled`).
-   `.delay`: Only show if the request takes longer than 200ms (useful for preventing blips on fast connections).
-   `.flex`, `.block`, `.inline`: Specific display modes for showing.

## Alpine.js Alternative (`data-loading`)

In Livewire 4, using the `data-loading` attribute is often cleaner for Tailwind-based styling:

```blade
<button wire:click="save" class="data-loading:opacity-50">
    Save
</button>
```
