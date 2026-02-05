# wire:dirty

`wire:dirty` allows you to track and display unsaved changes on the client side instantly.

## Basic Usage

```blade
<div wire:show="$dirty" wire:cloak>
    You have unsaved changes...
</div>
```

## Targeting Specific Properties

```blade
<input wire:model="title">
<span wire:show="$dirty('title')" class="italic">Modified</span>
```

## Styling Changes

You can apply classes to elements when their specific model is dirty:

```blade
<input wire:model="name" wire:dirty.class="border-yellow-500">
```

## Advanced Patterns

Use it to block navigation or confirm before leaving a page with dirty states using Alpine `beforeunload` listeners.
