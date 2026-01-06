# CSP-Safe Alpine.js Development

This document outlines the rules and best practices for developing Alpine.js components in this project to ensure compatibility with Content Security Policy (CSP) and Livewire's `csp_safe` mode.

## Why CSP Safety?

We use a strict Content Security Policy that blocks `eval()` and inline scripts with complex logic. Livewire's `csp_safe` mode uses a limited client-side parser that CANNOT handle:
- Closures / Arrow functions `() => { ... }`
- Method definitions `name() { ... }`
- Complex logic in `x-data`, `@click`, etc.

## The Rule

> [!IMPORTANT]
> **NO complex logic in Blade templates.** All Alpine components with logic must be extracted to separate JavaScript files and registered as data components.

## What's Allowed in Blade

You can use simple expressions that do not require a full JavaScript engine:
- Toggling a boolean: `@click="open = !open"`
- Simple assignments: `@click="tab = 'profile'"`
- Calling a registered component: `x-data="myComponent()"`
- Calling a method on a registered component: `@click="toggle()"`

## The Self-Registering Pattern

Each Alpine component should live in `resources/js/alpine/data/` and register itself on `alpine:init`.

### 1. Create the JS component

File: `resources/js/alpine/data/my-component.js`

```javascript
export function myComponent(config = {}) {
    return {
        open: false,
        title: config.title || 'Default',
        
        init() {
            // Complex initialization here
        },
        
        toggle() {
            this.open = !this.open;
        }
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('myComponent', myComponent);
});
```

### 2. Register in `resources/assets.json`

Add the file to the appropriate bundle (shared, app, or auth).

```json
{
    "js": {
        "shared": [
            "resources/js/alpine/data/my-component.js"
        ]
    }
}
```

### 3. Use in Blade

```blade
<div x-data="myComponent({ title: 'Dynamic Title' })">
    <button @click="toggle()">Toggle</button>
    <div x-show="open">Content</div>
</div>
```

## Special Tools

### Copy to Clipboard

Use the shared `copyToClipboard` component for any copy logic.

```blade
<div x-data="copyToClipboard('text to copy')">
    <button @click="copy()">Copy</button>
    <span x-show="copied">Copied!</span>
</div>
```

### Confirm Modal (CSP-Safe Pattern)

When using the confirm-modal component, **do NOT pass arrow functions**. Instead, use the `confirmEvent` + `confirmData` pattern:

**❌ CSP-UNSAFE (will cause parser errors):**
```blade
@click="$dispatch('confirm-modal', {
    title: 'Delete',
    message: 'Are you sure?',
    confirmAction: () => $wire.delete(id)  {{-- This breaks CSP! --}}
})"
```

**✅ CSP-SAFE (use this instead):**
```blade
{{-- In your Blade template: --}}
@click="openConfirmDelete('{{ $id }}', 'Delete', 'Are you sure?')"

{{-- In your Alpine component (JS file): --}}
openConfirmDelete(id, title, message) {
    window.dispatchEvent(new CustomEvent('confirm-modal', {
        detail: {
            title: title,
            message: message,
            confirmEvent: 'my-component:action-confirmed',
            confirmData: { action: 'delete', id: id }
        },
        bubbles: true
    }));
},

handleConfirmedAction(data) {
    if (data.action === 'delete') {
        this.$wire.delete(data.id);
    }
}
```

## Maintenance

When adding new components:
1. Check if it's complex (contains functions/methods).
2. If yes, extract to `resources/js/alpine/data/`.
3. Add to `resources/assets.json`.
4. Update `vite.config.js` if it's a NEW bundle (not common).
5. Run `npm run build` to verify bundles.

