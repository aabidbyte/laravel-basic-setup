# wire:poll

`wire:poll` automatically sends a request to the server at regular intervals to refresh the component's state.

## Basic Usage

```blade
<div wire:poll>
    Current Time: {{ now() }}
</div>
```

## Interval Control

-   `wire:poll.500ms`: Poll every 500ms.
-   `wire:poll.10s`: Poll every 10 seconds.
-   `wire:poll.keep-alive`: Only poll while the tab is active.

## Targeted Polling

Show only specific data updates:

```blade
<div wire:poll.5s="refreshStock">
    Stock Level: {{ $stock }}
</div>
```

## Best Practices

Polling can be expensive. For real-time updates that are irregular, consider using **Laravel Reverb** (WebSockets) instead of polling.
