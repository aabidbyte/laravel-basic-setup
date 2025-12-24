## CSP

Content Security Policy support in Livewire.

### CSP-Safe Build

Use CSP-safe build:

```javascript
import { Livewire } from "@livewire/livewire/csp";
```

### What's Supported/Not

**Supported:**

-   Basic directives
-   Event handling
-   Form submission

**Not Supported:**

-   Complex JavaScript expressions in directives
-   Inline event handlers

### Headers

Set CSP headers:

```php
return response()->view('app')
    ->header('Content-Security-Policy', "default-src 'self'");
```

### Performance

CSP mode has minimal performance impact.

### Testing

Test CSP compliance:

```php
Livewire::test(Component::class)
    ->assertCspCompliant();
```

