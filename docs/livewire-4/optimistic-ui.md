# Optimistic UI

Livewire 4 provides tools to make your interfaces feel instant by removing round-trip latency for common interactions.

## wire:show

`wire:show` toggles element visibility using CSS immediately, without removing the element from the DOM or waiting for a server request.

```blade
<div wire:show="showModal">
    <!-- Hidden/shown instantly when $showModal changes -->
    I am a modal
</div>
```

## wire:text

`wire:text` updates text content immediately on the client side while the server processes the request.

```blade
Likes: <span wire:text="likes"></span>
```

## wire:bind

`wire:bind` allows you to bind any HTML attribute reactively and purely on the client side.

```blade
<!-- Updates class instantly based on message length -->
<input 
    wire:model="message" 
    wire:bind:class="message.length > 240 && 'text-red-500'"
>
```

## $dirty

The `$dirty` property allows you to track and display unsaved changes on the client side.

```blade
<!-- Show when any property is modified -->
<div wire:show="$dirty">
    You have unsaved changes
</div>

<!-- Show when a specific property is modified -->
<div wire:show="$dirty('title')">
    Title modified
</div>
```
