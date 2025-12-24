## Magics

### `$el`

Retrieve the current DOM node.

```html
<button @click="$el.innerHTML = 'Hello World!'">
    Replace me with "Hello World!"
</button>
```

**Note:** In Alpine V3, `$el` always represents the element that an expression was executed on, not the root element of the component. Use `$root` to access the root element.

[Read more about $el →](https://alpinejs.dev/magics/el)

### `$refs`

Retrieve DOM elements marked with `x-ref` inside the component.

```html
<button @click="$refs.text.remove()">Remove Text</button>
<span x-ref="text">Hello 👋</span>
```

**Limitations:** In V3, `$refs` can only be accessed for elements created statically (not dynamically with `x-for`).

[Read more about $refs →](https://alpinejs.dev/magics/refs)

### `$store`

Access global Alpine stores registered using `Alpine.store()`.

```html
<button x-data @click="$store.darkMode.toggle()">Toggle Dark Mode</button>

<div x-data :class="$store.darkMode.on && 'bg-black'">
    ...
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('darkMode', {
        on: false,
        toggle() {
            this.on = ! this.on
        }
    })
})
</script>
```

**Single-value stores:**

```html
<button x-data @click="$store.darkMode = ! $store.darkMode">Toggle Dark Mode</button>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('darkMode', false)
})
</script>
```

[Read more about $store →](https://alpinejs.dev/magics/store)

### `$watch`

Watch a component property for changes.

```html
<div x-data="{ open: false }" x-init="$watch('open', value => console.log(value))">
    <button @click="open = ! open">Toggle Open</button>
</div>
```

**Access old value:**

```html
<div x-data="{ open: false }" x-init="$watch('open', (value, oldValue) => console.log(value, oldValue))">
    <button @click="open = ! open">Toggle Open</button>
</div>
```

**Watch deeply nested properties:**

```html
<div x-data="{ foo: { bar: 'baz' }}" x-init="$watch('foo.bar', value => console.log(value))">
    <button @click="foo.bar = 'bob'">Toggle Open</button>
</div>
```

**Deep watching:**

```html
<div x-data="{ foo: { bar: 'baz' }}" x-init="$watch('foo', (value, oldValue) => console.log(value, oldValue))">
    <button @click="foo.bar = 'bob'">Update</button>
</div>
```

**⚠️ Warning:** Changing a property of a "watched" object as a side effect of the `$watch` callback will generate an infinite loop.

[Read more about $watch →](https://alpinejs.dev/magics/watch)

### `$dispatch`

Dispatch browser events (shortcut for `element.dispatchEvent(new CustomEvent(...))`).

```html
<div @notify="alert('Hello World!')">
    <button @click="$dispatch('notify')">
        Notify
    </button>
</div>
```

**Pass data with the event:**

```html
<div @notify="alert($event.detail.message)">
    <button @click="$dispatch('notify', { message: 'Hello World!' })">
        Notify
    </button>
</div>
```

**Note on event propagation:** Use `.window` modifier when capturing events from nodes under the same nesting hierarchy:

```html
<!-- ✅ Works -->
<div x-data>
    <span @notify.window="..."></span>
    <button @click="$dispatch('notify')">Notify</button>
</div>
```

[Read more about $dispatch →](https://alpinejs.dev/magics/dispatch)

### `$nextTick`

Execute code AFTER Alpine has made its reactive DOM updates.

```html
<div x-data="{ title: 'Hello' }">
    <button
        @click="
            title = 'Hello World!';
            $nextTick(() => { console.log($el.innerText) });
        "
        x-text="title"
    ></button>
</div>
```

**Promises:** `$nextTick` returns a promise:

```html
<button
    @click="
        title = 'Hello World!';
        await $nextTick();
        console.log($el.innerText);
    "
    x-text="title"
></button>
```

[Read more about $nextTick →](https://alpinejs.dev/magics/nextTick)

### `$root`

Access the root element of the component (the closest element up the DOM tree that contains `x-data`).

```html
<div x-data data-message="Hello World!">
    <button @click="alert($root.dataset.message)">Say Hi</button>
</div>
```

**Note:** In Alpine V3, `$el` always represents the current element, so use `$root` to access the component's root element.

[Read more about $root →](https://alpinejs.dev/magics/root)

### Other Magics

- `$id` - Generate unique IDs (used with `x-id` directive)
- `$data` - Access the data object of the component
- `$persist` - Persist state across page loads (plugin)

---

