# wire:bind

`wire:bind` allows you to bind any HTML attribute reactively and purely on the client side without waiting for a server request.

## Basic Usage

```blade
<div wire:bind:class="isOpen ? 'bg-blue-500' : 'bg-gray-500'">
    ...
</div>
```

When the `isOpen` property changes, the class will be toggled immediately on the client side.

## Common Use Cases

### Dynamic Attributes
You can bind any attribute, such as `disabled`, `readonly`, `style`, etc.

```blade
<button wire:bind:disabled="!isValid">Submit</button>
```

### Optimistic Styling
Use it to provide instant feedback while a server request is in flight.

```blade
<div wire:bind:style="message.length > 240 && 'color: red'">
    <textarea wire:model="message"></textarea>
</div>
```

## Difference from `:class` (Alpine)
While similar to Alpine's `:class`, `wire:bind` uses Livewire properties and integrates with Livewire's internal state tracking. It's particularly useful for "snappy" interfaces using `wire:text` and `wire:show`.
