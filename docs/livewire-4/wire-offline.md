# wire:offline

`wire:offline` allows you to toggle the visibility or styling of an element when the user's internet connection is lost.

## Basic Usage

```blade
<div wire:offline>
    You are currently offline. Some features may be unavailable.
</div>
```

## Styling Modifiers

```blade
<div wire:offline.class="bg-red-500">
    ...
</div>
```

Useful for highlighting that a "Save" button won't work currently.
