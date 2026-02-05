# wire:cloak

To use `wire:cloak`, add the directive to any element you want to hide during page load:

```html
<div wire:cloak>
    This content will be hidden until Livewire is fully loaded
</div>
```

`wire:cloak` works by adding a `<style>` tag to the `<head>` of your page that hides elements with the `[wire\:cloak]` attribute. When Livewire initializes, it removes this attribute from all elements, making them visible.

This is useful for preventing "content layout shift" or "flashing" of unstyled content while Livewire is initializing.
