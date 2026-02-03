# CSP-Safe Alpine.js Development

This document outlines the rules and best practices for developing Alpine.js components in this project to ensure compatibility with Content Security Policy (CSP) and Livewire's `csp_safe` mode.

## Why CSP Safety?

We use a strict Content Security Policy that blocks `eval()` and inline scripts with complex logic. Livewire's `csp_safe` mode uses a limited client-side parser that CANNOT handle:
- Closures / Arrow functions `() => { ... }`
- Method definitions `name() { ... }`
- Complex logic in `x-data`, `@click`, etc.

## The Rule

> [!IMPORTANT]
> **NO complex logic in Blade templates.** All Alpine components with logic must be extracted to separate JavaScript files.
>
> **Strict Prohibitions:**
> 1. ❌ Arrow functions `() => ...`
> 2. ❌ Template literals in attributes `:class="`...`"`
> 3. ❌ `x-html` directive (blocked by build)
> 4. ❌ Inline method definitions `foo() { ... }`

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

### Template Literals (Backticks)

Alpine's CSP parser **cannot** handle template literals in Blade attributes.

**❌ CSP-UNSAFE:**
```html
<div :class="`bg-${color}-500 text-white`"></div>
```

**✅ CSP-SAFE:**
```javascript
// In JS:
getColorClass() {
    return `bg-${this.color}-500 text-white`;
}
```
```html
<!-- In Blade: -->
<div :class="getColorClass()"></div>
```

### Safe JSON Parsing for Complex Data

Passing complex objects (like associative arrays) directly into Alpine attributes can fail due to Livewire's strict attribute parser or HTML entity decoding issues.

**❌ CSP-UNSAFE / FRAGILE:**
```blade
{{-- This can cause "Parser Error: Expected property key" or "Undefined variable JSON" --}}
<div x-data="myComponent({ 'key': 'value' })">...</div>
<div x-data="myComponent(@js($data))">...</div> 
```

**✅ CSP-SAFE (String Passing Pattern):**
Pass the data as a JSON-encoded **string** and parse it inside the component.

```blade
{{-- In Blade: Use JSON_HEX_APOS to ensure safety inside single quotes --}}
<div x-data="myComponent( '{{ json_encode($data, JSON_HEX_APOS) }}' )">...</div>
```

```javascript
// In JS Component:
Alpine.data('myComponent', function(dataStr) {
    return {
        // Parse the string at runtime in the standard JS engine
        data: JSON.parse(dataStr),
        
        init() {
            console.log(this.data); // { key: 'value' }
        }
    }
});
```

### no `x-html`

The `x-html` directive is prohibited in the CSP build because it requires unsafe-eval or potential XSS vectors.

**❌ CSP-UNSAFE:**
```html
<div x-html="content"></div>
```

**✅ CSP-SAFE:**
1. Use `x-text` for text content:
   ```html
   <div x-text="content"></div>
   ```
   ```
2. Use `<x-ui.icon>` wrapped in `<template x-if>` for mutual exclusivity:
   ```html
   {{-- Proper visibility handling for components that don't pass attributes --}}
   <template x-if="type === 'success'">
       <x-ui.icon name="check" />
   </template>
   ```

### Server-Side Conditional Classes (`@class`)

For static or server-rendered conditional classes, prefer Blade's `@class` directive over Alpine binding. It is faster (SSR) and 100% CSP safe.

**✅ RECOMMENDED:**
```blade
<div @class([
    'p-4 rounded',
    'bg-red-500' => $hasError,
    'bg-green-500' => !$hasError
])>
    ...
</div>
```

### Form Submission in Alpine

When submitting forms via Alpine:
- Avoid `this.$el.submit()` if `$el` is an input or button.
- Use `this.$refs.inputName.form.submit()` or `document.getElementById('form-id').submit()` to ensure you are targeting the actual form element.

## Maintenance

When adding new components:
1. Check if it's complex (contains functions/methods).
2. If yes, extract to `resources/js/alpine/data/`.
3. Add to `resources/assets.json`.
4. Update `vite.config.js` if it's a NEW bundle (not common).
5. Run `npm run build` to verify bundles.

## wire:navigate and CSP

When using Livewire's `wire:navigate` (SPA-style navigation), a nonce-based CSP strategy doesn't work because:
1. Each server response generates a NEW nonce.
2. The browser's CSP policy retains the OLD nonce from the initial page load.
3. New scripts/styles from navigated pages are blocked.

**Our Solution:**
We use `unsafe-inline` for scripts and styles in `MyCspPreset`, which allows:
- Livewire's wire:navigate to inject new content correctly
- Alpine.js attribute bindings (via `SCRIPT_ATTR` and `STYLE_ATTR` directives)
- Dynamic style updates from Alpine

This is a conscious trade-off documented in `app/Support/Csp/MyCspPreset.php`.

## Troubleshooting

### Modal Stuck Open
If modals get stuck open after navigation:
1. Ensure Alpine components are properly cleaned up via `destroy()` method
2. Check that event listeners are removed on component destruction
3. Verify the modal's `openState` is correctly synchronized with Livewire

### CSP Violation Errors in Console
If you see `script-src` or `style-src` violations:
1. Check that you're not using arrow functions in Blade templates
2. Verify template literals are not used in Alpine attributes
3. Ensure all complex logic is in registered Alpine components

