## Performance Improvements

Livewire v4 includes significant performance improvements:

-   **Non-blocking polling**: `wire:poll` no longer blocks other requests or is blocked by them
-   **Parallel live updates**: `wire:model.live` requests now run in parallel, allowing faster typing and quicker results

These improvements happen automatically—no changes needed to your code.

