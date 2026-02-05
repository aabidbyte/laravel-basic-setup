# Performance Optimization

## Livewire-native Infinite Scroll (Virtual List)

The DataTable uses a **Livewire-native Infinite Scroll** (Load More) architecture to efficiently handle large datasets (e.g., 100+ rows) without freezing the browser during the initial render.

### How it Works

1.  **Backend (`Datatable.php`)**:
    -   Tracks a `$visibleRows` property (defaulting to 20).
    -   Does NOT render all 100 rows at once.
    -   Provides a `loadMore()` method that increments `$visibleRows` by 20.
    -   Automatically resets `$visibleRows` to 20 when filters, search, or sorting change.

    ```php
    public int $visibleRows = 20;

    public function loadMore(): void
    {
        $this->visibleRows += 20;
    }
    ```

2.  **Frontend (`datatable.blade.php`)**:
    -   Loops through only the visible rows: `@foreach ($rows->take($this->visibleRows) as $row)`.
    -   Uses a lightweight, CSP-compliant Alpine.js component (`infinite-scroll.js`) at the bottom of the list.
    -   Uses an `IntersectionObserver` with a `rootMargin` of `1200px` to pre-fetch the next batch of rows before the user reaches the bottom.

    ```blade
    {{-- At the bottom of the table --}}
    @if ($rows->count() > $this->visibleRows)
        <div x-data="infiniteScroll">
            <x-ui.loading size="sm" />
        </div>
    @endif
    ```

### Infinite Scroll Component

The observer logic is encapsulated in `resources/js/alpine/data/infinite-scroll.js` to ensure compliance with strict Content Security Policies (CSP):

```javascript
export default () => ({
    init() {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                this.$wire.loadMore();
            }
        }, {
            root: null,
            rootMargin: '1200px', // Pre-fetch buffer
            threshold: 0
        });

        observer.observe(this.$el);
    }
})
```

### Benefits
-   **Instant Initial Load**: Only 20 rows are rendered initially.
-   **Smooth Scrolling**: New rows are appended via standard Livewire DOM diffing.
-   **Reduced DOM Size**: The browser doesn't deal with thousands of nodes at once.
-   **CSP Safe**: No inline scripts or `x-html` usage.

## Best Practices

### Computed Properties

We utilize Livewire 4's `#[Computed]` attribute for efficient caching of derived state. This replaces manual memoization patterns and ensures that expensive logic (like resolving columns, filters, or checking selection state) is executed only once per request.

```php
use Livewire\Attributes\Computed;

#[Computed]
public function hasSelection(): bool
{
    return count($this->selected) > 0;
}
```

This property can be accessed in views as `$this->hasSelection` without method parentheses, cleaner and more performant.

### Optimistic UI & Deferred Updates

To ensure the table feels instant, we use:

1.  **Deferred Selection**: Row checkboxes use `wire:model="selected"` (deferred). Checking a box does **not** send a request to the server immediately.
2.  **Optimistic Rendering**: We use `wire:show` and `wire:text` to instantly update the UI (like bulk action counts) based on client-side state, even before the server processes the data.
3.  **wire:cloak**: Ensures no UI flicker occurs during initialization.

### Avoid N+1 Patterns

❌ **Bad** - Dynamic callback executed every render:
```php
Filter::make('role', __('Role'))
    ->optionsCallback(fn () => Role::pluck('name', 'name')->toArray())
```
