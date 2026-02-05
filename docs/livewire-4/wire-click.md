# wire:click

`wire:click` is the most common way to trigger server-side actions from an element click.

## Basic Usage

```blade
<button wire:click="save">Save Changes</button>
```

## Passing Parameters

```blade
<button wire:click="delete({{ $post->id }})">Delete</button>
```

## Modifiers

-   `.prevent`: Prevent default behavior (essential for `<a>` tags).
-   `.stop`: Stop event propagation.
-   `.self`: Only trigger if the event originated on this element.
-   `.once`: Trigger the action only once.
-   `.debounce.500ms`: Wait 500ms after the last click before sending the request.
-   `.throttle.500ms`: Limits requests to once every 500ms.
-   `.renderless`: Executes the action but skips re-rendering the component.
-   `.preserve-scroll`: Maintains the current scroll position after the update.
-   `.async`: Runs the action in parallel (queued by default).

## Confirming Actions

```blade
<button wire:click="delete" wire:confirm="Are you sure?">Delete</button>
```
