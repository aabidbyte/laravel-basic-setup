# wire:show

`wire:show` allows you to toggle the visibility of an element based on a value.

## Basic Usage

```html
<div wire:show="showModal">
    ...
</div>
```

If `$showModal` is `true`, the element is shown. If `false`, `display: none` is applied.

While `wire:show` can be used with server-side properties (requiring a roundtrip), it is most powerful when combined with Alpine.js or deferred updates for optimistic UI. For example, if you use `wire:show` with a property that is updated client-side (via `$wire.set` or bound via `wire:model`), Livewire/Alpine may handle the visibility update optimistically.

## Using Transitions

You can combine `wire:show` with Alpine.js transitions:

```html
<div wire:show="showModal" x-transition.duration.500ms>
    ...
</div>
```

Since `wire:show` only toggles the CSS `display` property, Alpine's `x-transition` directives work perfectly with it.
