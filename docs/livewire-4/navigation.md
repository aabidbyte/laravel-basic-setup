## Navigation

Livewire provides SPA-like navigation with `wire:navigate`.

### wire:navigate

Use `wire:navigate` for instant navigation:

```blade
<a href="/posts" wire:navigate>Posts</a>
```

### Redirects

Redirect in actions:

```php
public function save()
{
    // Save logic

    return $this->redirect('/posts');
}
```

### Prefetching

Prefetch pages on hover:

```blade
<a href="/posts" wire:navigate.hover>Posts</a>
```

### @persist

Persist elements across navigation:

```blade
@persist('sidebar')
    <div class="sidebar">
        <!-- Sidebar content -->
    </div>
@endpersist
```

### Active Links

Show active state:

```blade
<a href="/posts" wire:navigate class="{{ request()->is('posts*') ? 'active' : '' }}">
    Posts
</a>
```

### Scroll Position

Preserve scroll position:

```blade
<div wire:navigate:scroll>
    <!-- Scrollable content -->
</div>
```

### JavaScript Hooks

Hook into navigation:

```javascript
document.addEventListener("livewire:navigated", () => {
    console.log("Navigation completed");
});
```

### Manual Navigation

Navigate programmatically:

```php
$this->redirect('/posts');
```

Or from JavaScript:

```javascript
$wire.$redirect("/posts");
```

### Analytics

Track navigation:

```javascript
document.addEventListener("livewire:navigated", (event) => {
    // Track page view
    gtag("config", "GA_MEASUREMENT_ID", {
        page_path: event.detail.url,
    });
});
```

### Script Evaluation

Scripts are evaluated on navigation:

```blade
<script>
    console.log('Page loaded');
</script>
```

### Progress Bar Customization

Customize the progress bar:

```css
[wire\:navigate] {
    /* Custom styles */
}
```

