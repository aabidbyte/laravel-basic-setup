## Breaking Changes

### Method Signature Changes

**Streaming:**

```php
// Before (v3)
$this->stream(to: '#container', content: 'Hello', replace: true);

// After (v4)
$this->stream(content: 'Hello', replace: true, el: '#container');
```

### JavaScript Deprecations

**Deprecated: $wire.$js() method**

```javascript
// Deprecated (v3)
$wire.$js("bookmark", () => {
    // Toggle bookmark...
});

// New (v4)
$wire.$js.bookmark = () => {
    // Toggle bookmark...
};
```

**Deprecated: commit and request hooks**

The commit and request hooks have been deprecated in favor of a new interceptor system. See the JavaScript Interceptors documentation for migration details.

### Use wire:navigate:scroll

When using `wire:scroll` to preserve scroll in a scrollable container across `wire:navigate` requests in v3, you will need to instead use `wire:navigate:scroll` in v4:

```blade
@persist('sidebar')
    <div class="overflow-y-scroll" wire:navigate:scroll>
        <!-- ... -->
    </div>
@endpersist
```

