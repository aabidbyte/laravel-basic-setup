# Optimistic UI & Snappy Interfaces

Optimistic UI is the practice of updating the client-side interface immediately after a user action, before the server response is received. Livewire 4, powered by its tighter integration with Alpine.js, provides several directives and patterns to make apps feel instant by removing round-trip latency.

## 1. Core Optimistic Directives

These directives evaluate client-side state instantly, even if the property being tracked hasn't finished syncing with the server yet.

### `wire:show`
Toggles visibility using CSS `display: none` instantly. Unlike `@if` which requires a server re-render, `wire:show` is handled by the browser.

```blade
<div wire:show="selectedLength > 0">
    <!-- Shows/hides instantly based on the 'selected' array length -->
    <button wire:click="deleteSelected">Delete Selected</button>
</div>
```

### `wire:text`
Updates an element's text content immediately. Extremely useful for "counters" or status labels.

```blade
<div wire:show="selectedLength > 0">
    <span wire:text="selectedLength"></span> items selected
</div>
```

### `wire:cloak`
Prevents a **Flash of Unstyled Content (FOUC)**. When using `wire:show`, elements might blip on the page for a second before Livewire's JavaScript boots up and hides them. `wire:cloak` hides the element until Livewire/Alpine are fully initialized.

```blade
<div wire:show="selectedLength > 0" wire:cloak>
    ...
</div>
```

### `wire:bind`
Binds HTML attributes (like classes or styles) reactively on the client side without waiting for the network.

```blade
<input wire:model="message" 
       wire:bind:class="message.length > 240 ? 'border-error' : 'border-success'">
```

---

## 2. $dirty Property

The `$dirty` property allows you to track and display unsaved changes on the client side instantly.

```blade
<!-- Show when any property is modified -->
<div wire:show="$dirty" wire:cloak class="badge badge-warning">
    Unsaved changes...
</div>

<!-- Target specific properties -->
<input wire:model="title">
<span wire:show="$dirty('title')" class="text-xs text-info italic">Modified</span>
```

---

## 3. Client-Side State Management (Alpine Patterns)

For complex interactions where server-side roundtrips feel laggy (e.g., list selection, archiving, keyboard navigation), the best approach is often moving state to Alpine.js.

### Minimizing Network Calls
Instead of using `wire:model.live` for state that only impacts the UI (like selecting a row to view details), use standard `wire:model` and rely on Alpine to handle the immediate feedback.

### The "Archive" Pattern (Tighten Tip)
If you want to remove an item from a list instantly, you can use `wire:loading.remove`. However, for more control, use Alpine to track the "active" items.

```blade
<div x-data="{ 
         items: @js($this->rows), 
         selectedId: null,
         async archive(id) {
             // 1. Update UI instantly
             this.items = this.items.filter(item => item.id !== id);
             // 2. Send the request in the background
             await $wire.archive(id);
         }
     }"
     wire:ignore>
    <template x-for="item in items" :key="item.id">
        <div class="row">
            <span x-text="item.name"></span>
            <button @click="archive(item.id)">Archive</button>
        </div>
    </template>
</div>
```

> **Note:** Use `wire:ignore` on the parent container when Alpine is fully managing the DOM of a list to prevent Livewire from overwriting morphing changes.

---

## 4. Case Study: High-Performance Bulk Actions

A common "lag factor" in Laravel apps is toggling bulk actions. Every checkbox click usually sends a network request if using `.live`.

**Typical (Slow) way:**
```blade
<input type="checkbox" wire:model.live="selected">
@if(count($selected) > 0)
    <div class="bulk-bar">...</div>
@endif
```

**Optimistic (Instant) way:**
1. Remove `.live` from the model (updates still happen, just quietly).
2. Use `wire:show` and `wire:text` to reflect the state instantly.

```blade
{{-- No network request on click --}}
<input type="checkbox" wire:model="selected">

{{-- Toggles and counts instantly via JavaScript --}}
<div wire:show="selected.length > 0" wire:cloak class="bulk-toolbar">
    <span wire:text="selected.length"></span> items selected
    <button wire:click="deleteSelected">Execute</button>
</div>
```

---

## 5. Tips for choosing Optimistic UI

| Use Optimistic UI when... | Avoid it when... |
| :--- | :--- |
| Action has a high success rate (e.g., toggling a favorite). | Success is uncertain (e.g., complex validation). |
| Latency is perceptible (e.g., mobile users). | The server response drastically changes the whole UI. |
| The state is local to the current view. | The action triggers side effects on other components. |

## Artificial Slowness
During development, you can test your optimistic UI by adding artificial slowness to your Livewire actions:
```php
public function performAction()
{
    if (app()->environment('local')) {
        usleep(500000); // 500ms
    }
    // ...
}
```
This forces you to see what the user sees on a slow connection, ensuring your optimistic transitions and loading states are robust.
