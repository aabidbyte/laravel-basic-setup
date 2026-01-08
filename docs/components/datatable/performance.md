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

### Memoization

Expensive computations are cached per-request using `memoize()`:

```php
protected function getRoleOptions(): array
{
    return $this->memoize('filter:roles', fn () =>
        Role::pluck('name', 'name')->toArray()
    );
}

// Use with Filter
Filter::make('role', __('Role'))
    ->options($this->getRoleOptions()) // ✅ Computed once per request
```

### Avoid N+1 Patterns

❌ **Bad** - Dynamic callback executed every render:
```php
Filter::make('role', __('Role'))
    ->optionsCallback(fn () => Role::pluck('name', 'name')->toArray())
```
