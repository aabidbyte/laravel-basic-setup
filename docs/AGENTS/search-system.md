# Centralized Search System

> **Rule:** Use `$store.search` for ALL search and highlight functionality across the application.

--- 

## Overview

The centralized search system provides reusable, performant, and secure search and highlight functionality through the Alpine `search` store. This system is used by select components, datatables, and any other search implementations.

---

## Architecture

### Components

```
┌─────────────────────────────────────────────┐
│   resources/js/alpine/store/search.js       │
│   (Centralized Search Store)                │
│                                             │
│   - highlightHTML()                         │
│   - filterOptions()                         │
│   - createSearchState()                     │
│   - escapeRegex()                           │
└──────────────┬──────────────────────────────┘
               │
               │ Used by
               ▼
    ┌──────────────────┐          ┌────────────────┐
    │  Select Component│          │  Datatable     │
    │                  │          │                │
    │  - Client search │          │  - Highlighting│
    │  - Highlighting  │          │  - Filtering   │
    └──────────────────┘          └────────────────┘
```

### Data Flow

1. **User Input** → Search query entered
2. **Query Processing** → Escaped and normalized
3. **Filtering** → Sync (<100 items) or Async (>100 items)
4. **Highlighting** → HTML parsing and Text Node modification (CSP-Safe)
5. **Display** → Results shown with highlighted matches in `<mark>` tags

---

## API Reference

### `highlightHTML(html, query)`

Highlights search terms within HTML content while preserving HTML structure and attributes. Safe to use with `innerHTML` because it only modifies text nodes. Only plain text nodes are wrapped in `<mark>` tags; existing HTML structure is preserved perfectly.

**Parameters:**
- `html` (string) - HTML content or plain text to highlight
- `query` (string) - Search query

**Returns:** `string` - HTML with matches wrapped in `<mark>` tags

**Example:**
```javascript
$store.search.highlightHTML('<span>Hello World</span>', 'world')
// Returns: '<span>Hello <mark class="bg-warning/30 rounded">World</mark></span>'
```

---

### `filterOptions(options, query, config)`

Filters options array with async chunking for large datasets.

**Parameters:**
- `options` (Array<[any, string]>) - Array of [value, label] pairs
- `query` (string) - Search query
- `config` (Object) - Configuration overrides

**Config Options:**
```javascript
{
    chunkSize: 50,           // Items per chunk
    syncThreshold: 100,      // Sync vs async threshold
    caseSensitive: false     // Case-sensitive matching
}
```

**Returns:**
```javascript
{
    sync: boolean,           // True if synchronous filtering
    results: Array,          // Immediate results (first chunk for async)
    promise: Promise<Array>  // Promise resolving to all results
}
```

---

### `createSearchState()`

Factory function for creating reactive search state.

**Returns:** object with query, results, isSearching flags.

### `escapeRegex(str)`

Escapes special regex characters in a string.

---

## Implementation Patterns

### Pattern 1: Reactive Highlighting (Select Component)

For components where the search query changes on the client-side (reactive), pass the parent scope and content to a dedicated component that watches for changes.

**Blade:**
```blade
<template x-if="searchQuery">
    <span x-data="highlightedText($data, label)"></span>
</template>
```

**JavaScript (Alpine Component):**
```javascript
window.Alpine.data('highlightedText', function(parentScope, content) {
    return {
        init() {
            const update = () => {
                this.$el.innerHTML = this.$store.search.highlightHTML(content, parentScope.searchQuery);
            };
            
            // Initial render
            update();
            
            // Watch parent's searchQuery for changes
            this.$watch(() => parentScope.searchQuery, update);
        }
    };
});
```

### Pattern 2: Server-Side Search Highlighting (Datatable)

For components where search is handled server-side (non-reactive client-side), use a simple component to highlight once on initialization.

**Blade:**
```blade
<span x-data="highlightedCell('{{ addslashes($content) }}', '{{ addslashes($query) }}')"></span>
```

**JavaScript:**
```javascript
window.Alpine.data('highlightedCell', function(content, query) {
    return {
        init() {
             this.$nextTick(() => {
                 this.$el.innerHTML = this.$store.search.highlightHTML(content, query);
             });
        }
    };
});
```

---

## Security: CSP Compliance

### ✅ DO (Safe)
- Use registered Alpine components (e.g., `x-data="highlightedText"`).
- Use `.innerHTML` assignment inside JavaScript code function bodies.
- Pass parent scope object explicitly if needed (e.g., `$data`) to avoid `$parent` magic ambiguity in JS.
- Use `json_encode()` with `JSON_HEX_APOS` for passing data in attributes.

### ❌ DON'T (Unsafe)
- `x-effect` or `x-init` with inline code strings (e.g., `x-effect="$el.innerHTML = ..."`) - blocked by strict CSP.
- `x-html` directive - blocked by CSP.
- accessing `$parent` directly inside `Alpine.data` definition (it is undefined).

### Data Passing Rule

> **⚠️ CRITICAL**: NEVER use `@js()` or `@json()` directives inside Alpine attributes.

**Instead, use `json_encode()` with `JSON_HEX_APOS`:**

```blade
{{-- ✅ CORRECT: CSP-safe --}}
<div x-data="component('{{ json_encode($data, JSON_HEX_APOS) }}')"></div>
```

---

## Performance Guidelines

### Sync vs Async Decision Matrix

| Item Count | Method | UI Blocking | Use Case |
|------------|--------|-------------|----------|
| < 100 | Synchronous | No | Small select dropdowns |
| 100-1000 | Async (chunked) | No | Medium datatables |
| > 10000 | Server-side | No | Enterprise datasets |

---

## Best Practices

1. **Always Use Centralized Store**: `$store.search`
2. **Handle Empty Queries**: Return full list or empty state as appropriate.
3. **Use wire:ignore**: For Livewire integration to prevent DOM morphing issues.
4. **Add Loading Indicators**: For async searches.

---

## Related Documentation

- [Select Component](file:///Users/hop/Packages/laravel-basic-setup/docs/components/select.md)
- [Datatable Component](file:///Users/hop/Packages/laravel-basic-setup/docs/components/datatable.md)
- [Development Conventions](file:///Users/hop/Packages/laravel-basic-setup/docs/AGENTS/development-conventions.md)
- [CSP Safety Rules](file:///Users/hop/Packages/laravel-basic-setup/docs/AGENTS/csp-safety.md)
