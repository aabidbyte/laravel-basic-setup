# wire:navigate

`wire:navigate` provides a single-page application (SPA) experience within a traditional Laravel app by intercepting link clicks and fetching the page via AJAX.

## Basic Usage

```blade
<a href="/profile" wire:navigate>Go to Profile</a>
```

## Features

-   **Instant loading**: Merges the new page content into the current DOM.
-   **Scroll preservation**: Optionally use `.hover` to prefetch content when a user hovers over a link.
-   **Asset management**: Automatically handles script and style tag updates when navigating.

## Persistent Elements

Use `@persist` to keep an element from being destroyed during navigation (e.g., an audio player or top bar).

```blade
@persist('player')
    <audio controls src="/podcast.mp3"></audio>
@endpersist
```

## JavaScript Hooks

Livewire provides lifecycle events for navigation:
- `livewire:navigate`
- `livewire:navigated`

```javascript
document.addEventListener('livewire:navigated', () => {
    // Re-initialize external JS libraries here
});
```
