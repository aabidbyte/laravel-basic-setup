## JavaScript

Livewire provides extensive JavaScript APIs.

### Script Execution

Scripts in components are executed:

```blade
<script>
    console.log('Component loaded');
</script>
```

### $wire Object

Access component from JavaScript (see [AlpineJS Integration](#alpinejs-integration) section).

### Loading Assets @assets

Load assets in components:

```blade
@assets
    <link rel="stylesheet" href="/custom.css">
    <script src="/custom.js"></script>
@endassets
```

### Interceptors

Intercept Livewire operations:

**Component Interceptor:**

```javascript
Livewire.hook("component.init", ({ component, cleanup }) => {
    console.log("Component initialized:", component);
});
```

**Message Interceptor:**

```javascript
Livewire.hook("message.processed", ({ message, component }) => {
    console.log("Message processed:", message);
});
```

**Request Interceptor:**

```javascript
Livewire.hook("request", ({ payload, respond, preventDefault }) => {
    // Modify request
    respond(({ status, response }) => {
        // Handle response
    });
});
```

### Global Livewire Events

Listen for global events:

```javascript
document.addEventListener("livewire:init", () => {
    console.log("Livewire initialized");
});

document.addEventListener("livewire:navigated", () => {
    console.log("Navigation completed");
});
```

### Livewire Global Object

Access Livewire globally:

```javascript
Livewire.find("component-id");
Livewire.all();
Livewire.dispatch("event-name");
```

### Livewire.hook()

Hook into Livewire lifecycle:

```javascript
Livewire.hook("morph", ({ el, component, skip }) => {
    // Custom morphing logic
});
```

### Custom Directives

Create custom directives:

```javascript
Livewire.directive("custom", (el, directive, component) => {
    // Custom directive logic
});
```

### Server-Side JS Evaluation

Evaluate JavaScript from server:

```php
$this->js('console.log("Hello from server")');
```

### Common Patterns

**Debouncing:**

```javascript
let timeout;
$wire.$watch("search", (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        // Perform search
    }, 300);
});
```

**Polling:**

```blade
<div wire:poll.5s>
    <!-- Content -->
</div>
```

### Best Practices

-   Use `wire:key` in loops
-   Debounce expensive operations
-   Use `wire:loading` for feedback
-   Validate on server
-   Authorize actions

### Debugging

Enable debugging:

```javascript
window.Livewire = {
    ...window.Livewire,
    debug: true,
};
```

### $wire Reference

See [AlpineJS Integration](#alpinejs-integration) for `$wire` API.

### snapshot Object

Access component snapshot:

```javascript
let snapshot = $wire.$snapshot;
```

### component Object

Access component object:

```javascript
let component = $wire.$component;
```

### message Payload

Access message payload:

```javascript
Livewire.hook("message.processed", ({ message }) => {
    console.log(message.payload);
});
```

