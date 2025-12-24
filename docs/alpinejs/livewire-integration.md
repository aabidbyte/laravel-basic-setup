## Livewire Integration

This application uses Alpine.js with Livewire 4. When integrating Alpine.js with Livewire, follow these important guidelines:

### Using `$wire.$entangle()` Instead of `@entangle`

⚠️ **Important**: In Livewire v3/v4, **refrain from using the `@entangle` Blade directive**. While it was recommended in Livewire v2, `$wire.$entangle()` is now preferred as it is a more robust utility and avoids certain issues when removing DOM elements.

**❌ Avoid:**
```blade
<div x-data="{ open: @entangle('isOpen').live }">
    <!-- Content -->
</div>
```

**✅ Preferred:**
```blade
<div x-data="{ open: $wire.$entangle('isOpen') }">
    <!-- Content -->
</div>
```

**In Alpine.js data components:**
```javascript
Alpine.data('myComponent', ($wire) => ({
    isOpen: $wire.$entangle('isOpen'),
}));
```

**Benefits of `$wire.$entangle()`:**
- More robust and reliable
- Avoids issues when DOM elements are removed
- Better error handling
- Works seamlessly with Alpine.js lifecycle

### Accessing Livewire from Alpine.js

Use the `$wire` object to interact with Livewire components:

```blade
<div x-data>
    <button @click="$wire.save()">Save</button>
    <span x-text="$wire.title"></span>
</div>
```

**Common `$wire` methods:**
- `$wire.$refresh()` - Refresh the component
- `$wire.$dispatch('event')` - Dispatch an event
- `$wire.$watch('property', callback)` - Watch a property
- `$wire.$entangle('property')` - Entangle a property
- `$wire.$set('property', value)` - Set a property value

### Using Reactive `$wire` in Alpine Data Components

**Important**: `$wire` is **reactive** and automatically available in Alpine.js component context. You should **NOT** pass `$wire` as a parameter to Alpine data functions.

**❌ Avoid:**
```javascript
export function myComponent($wire) {
    return {
        init() {
            // $wire is captured at initialization time
            // May become stale after navigation
            if ($wire) {
                $wire.$refresh();
            }
        }
    };
}
```

**✅ Preferred:**
```javascript
export function myComponent() {
    return {
        init() {
            // $wire is reactive - automatically updated by Livewire
            // After navigation, if component exists, $wire will be available
            this.refreshIfAvailable();
        },

        refreshIfAvailable() {
            // $wire is reactive - Livewire handles the lifecycle
            // After navigation, if component exists, $wire is automatically available
            // If component was removed, $wire is null/undefined
            if (!$wire || typeof $wire.$refresh !== "function") {
                return;
            }

            // Additional validation: check if component still exists in Livewire registry
            if (window.Livewire && $wire.__instance?.id) {
                const component = window.Livewire.find($wire.__instance.id);
                if (!component) {
                    // Component was removed from DOM but $wire still exists
                    return;
                }
            }

            // Safe to refresh - component exists and is valid
            $wire.$refresh();
        }
    };
}
```

**Benefits:**
- **Reactive**: `$wire` is automatically updated by Livewire when components mount/unmount
- **Navigation-safe**: After navigation, `$wire` automatically points to the new component
- **No stale references**: No need to store component IDs or pass `$wire` as parameters
- **Cleaner code**: No try-catch blocks needed - validate before calling

**Usage in Blade:**
```blade
<!-- Don't pass $wire as parameter -->
<div x-data="myComponent()" x-init="init()">
    <!-- Content -->
</div>
```

For complete Livewire 4 documentation, see [docs/livewire-4/index.md](../livewire-4/index.md).

---

