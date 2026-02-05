# wire:text

`wire:text` allows you to optimistically show updates to a Livewire property without waiting for a network roundtrip.

## Basic Usage

```html
<button x-on:click="$wire.likes++" wire:click="like">Likes: <span wire:text="likes"></span></button>
```

When the button is clicked, `$wire.likes++` immediately updates the displayed count through `wire:text`, while `wire:click="like"` persists the change to the database in the background.

This pattern makes `wire:text` and `wire:model` (which is deferred by default in v3/v4) perfect for building optimistic UIs where the interface reacts immediately to user input.
