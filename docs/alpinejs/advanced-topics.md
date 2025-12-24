## Advanced Topics

### Reactivity

Alpine uses VueJS's reactivity engine under the hood. The core reactive functions are `Alpine.reactive()` and `Alpine.effect()`.

**`Alpine.reactive()`:**

Creates a reactive version of a JavaScript object:

```javascript
let data = { count: 1 }
let reactiveData = Alpine.reactive(data)

reactiveData.count = 2
console.log(data.count) // 2
```

**`Alpine.effect()`:**

Runs a function and tracks interactions with reactive data:

```javascript
let data = Alpine.reactive({ count: 1 })

Alpine.effect(() => {
    console.log(data.count)
})

data.count = 2 // Logs "2"
```

**Example without Alpine syntax:**

```html
<button>Increment</button>
Count: <span></span>
```

```javascript
let button = document.querySelector('button')
let span = document.querySelector('span')
let data = Alpine.reactive({ count: 1 })

Alpine.effect(() => {
    span.textContent = data.count
})

button.addEventListener('click', () => {
    data.count = data.count + 1
})
```

[Read more about reactivity →](https://alpinejs.dev/advanced/reactivity)

### Extending Alpine

Alpine allows for extension through custom directives, magics, and data. Every available directive and magic in Alpine uses these same APIs.

**Lifecycle concerns:** Extension code must be registered AFTER Alpine is downloaded but BEFORE it initializes the page.

**Via script tag:**

```javascript
document.addEventListener('alpine:init', () => {
    Alpine.directive('foo', ...)
})
```

**Via NPM:**

```javascript
import Alpine from 'alpinejs'

Alpine.directive('foo', ...)

window.Alpine = Alpine
Alpine.start()
```

#### Custom Directives

Register custom directives using `Alpine.directive()`:

```javascript
Alpine.directive('[name]', (el, { value, modifiers, expression }, { Alpine, effect, cleanup }) => {})
```

**Parameters:**
- `el` - The DOM element the directive is added to
- `value` - The part after colon (e.g., `'bar'` in `x-foo:bar`)
- `modifiers` - Array of dot-separated modifiers (e.g., `['baz', 'lob']` from `x-foo.baz.lob`)
- `expression` - The attribute value (e.g., `'law'` from `x-foo="law"`)
- `Alpine` - The Alpine global object
- `effect` - Function to create reactive effects with auto-cleanup
- `cleanup` - Function to pass callbacks that run when directive is removed

**Simple example:**

```javascript
Alpine.directive('uppercase', el => {
    el.textContent = el.textContent.toUpperCase()
})
```

```html
<div x-data>
    <span x-uppercase>Hello World!</span>
</div>
```

**Evaluating expressions:**

```javascript
Alpine.directive('log', (el, { expression }, { evaluate }) => {
    console.log(evaluate(expression))
})
```

**Introducing reactivity:**

```javascript
Alpine.directive('log', (el, { expression }, { evaluateLater, effect }) => {
    let getThingToLog = evaluateLater(expression)
    
    effect(() => {
        getThingToLog(thingToLog => {
            console.log(thingToLog)
        })
    })
})
```

**Cleaning up:**

```javascript
Alpine.directive('...', (el, {}, { cleanup }) => {
    let handler = () => {}
    window.addEventListener('click', handler)
    
    cleanup(() => {
        window.removeEventListener('click', handler)
    })
})
```

[Read more about extending Alpine →](https://alpinejs.dev/advanced/extending)

### Other Advanced Topics

- **CSP (Content Security Policy)** - Using Alpine with strict CSP
- **Async** - Handling asynchronous operations

---

