## Plugins

### Persist Plugin

Persist Alpine state across page loads using `$persist`.

**Installation:**

Via CDN (include BEFORE Alpine's core JS file):

```html
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/[email protected]/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/[email protected]/dist/cdn.min.js"></script>
```

Via NPM:

```bash
npm install @alpinejs/persist
```

```javascript
import Alpine from 'alpinejs'
import persist from '@alpinejs/persist'

Alpine.plugin(persist)
```

**Usage:**

```html
<div x-data="{ count: $persist(0) }">
    <button @click="count++">Increment</button>
    <span x-text="count"></span>
</div>
```

**How it works:** Alpine stores values in localStorage using the property name as the key (prefixed with `*x*` for namespacing).

**Custom key:**

```html
<div x-data="{ count: $persist(0).as('other-count') }">
    ...
</div>
```

**Custom storage (sessionStorage):**

```html
<div x-data="{ count: $persist(0).using(sessionStorage) }">
    ...
</div>
```

[Read more about Persist plugin →](https://alpinejs.dev/plugins/persist)

### Other Plugins

- **Mask** - Input masking
- **Focus** - Focus trap utilities
- **Intersect** - Intersection Observer API wrapper
- **Morph** - DOM morphing utilities
- **Collapse** - Collapsible content utilities

---

