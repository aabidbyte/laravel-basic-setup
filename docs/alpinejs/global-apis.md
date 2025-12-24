## Global APIs

### `Alpine.data()`

Re-use `x-data` contexts within your application.

```javascript
document.addEventListener('alpine:init', () => {
    Alpine.data('dropdown', () => ({
        open: false,
        toggle() {
            this.open = ! this.open
        }
    }))
})
```

Use in markup:

```html
<div x-data="dropdown">
    <button @click="toggle">...</button>
    <div x-show="open">...</div>
</div>
```

**Initial parameters:**

```html
<div x-data="dropdown(true)">
```

```javascript
Alpine.data('dropdown', (initialOpenState = false) => ({
    open: initialOpenState
}))
```

**Init functions:** Alpine automatically executes `init()` methods:

```javascript
Alpine.data('dropdown', () => ({
    init() {
        // Executed before Alpine initializes the component
    }
}))
```

**Destroy functions:** Alpine automatically executes `destroy()` methods:

```javascript
Alpine.data('timer', () => ({
    timer: null,
    counter: 0,
    init() {
        this.timer = setInterval(() => {
            console.log('Increased counter to', ++this.counter);
        }, 1000);
    },
    destroy() {
        clearInterval(this.timer);
    },
}))
```

**Using magic properties:** Access magic methods/properties using `this`:

```javascript
Alpine.data('dropdown', () => ({
    open: false,
    init() {
        this.$watch('open', () => {...})
    }
}))
```

[Read more about Alpine.data() →](https://alpinejs.dev/globals/alpine-data)

### `Alpine.store()`

Global state management.

**Registering a store:**

```javascript
document.addEventListener('alpine:init', () => {
    Alpine.store('darkMode', {
        on: false,
        toggle() {
            this.on = ! this.on
        }
    })
})
```

**Accessing stores:**

```html
<div x-data :class="$store.darkMode.on && 'bg-black'">...</div>
<button x-data @click="$store.darkMode.toggle()">Toggle Dark Mode</button>
```

Access externally:

```javascript
Alpine.store('darkMode').toggle()
```

**Initializing stores:** Provide `init()` method:

```javascript
Alpine.store('darkMode', {
    init() {
        this.on = window.matchMedia('(prefers-color-scheme: dark)').matches
    },
    on: false,
    toggle() {
        this.on = ! this.on
    }
})
```

**Single-value stores:**

```javascript
Alpine.store('darkMode', false)
```

```html
<button x-data @click="$store.darkMode = ! $store.darkMode">Toggle Dark Mode</button>
```

[Read more about Alpine.store() →](https://alpinejs.dev/globals/alpine-store)

---

