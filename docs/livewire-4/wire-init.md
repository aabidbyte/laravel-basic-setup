# wire:init

`wire:init` runs a specific action as soon as the component is finished rendering on the page.

## Basic Usage

```blade
<div wire:init="loadData">
    <div wire:loading wire:target="loadData">
        Loading...
    </div>
</div>
```

## Why use wire:init?

It's primarily used for **Deferring Loading**. By running an action on initialization, you can return the initial page response much faster (omitting expensive data fetching) and then load the data in a secondary request immediately after.

This is a great alternative to `lazy` loading for fine-grained control within a component.
