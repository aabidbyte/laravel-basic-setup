## Essentials

### State

State (JavaScript data that Alpine watches for changes) is at the core of everything you do in Alpine.

#### Local State (`x-data`)

Declare an HTML block's state in a single `x-data` attribute:

```html
<div x-data="{ open: false }">
    <!-- Any Alpine syntax on or within this element can access 'open' -->
</div>
```

#### Nesting Data

Data is nestable in Alpine. Child elements can access parent data:

```html
<div x-data="{ open: false }">
    <div x-data="{ label: 'Content:' }">
        <span x-text="label"></span>
        <span x-show="open"></span> <!-- Accesses parent's 'open' -->
    </div>
</div>
```

If a child has a property matching a parent's property, the child property takes precedence.

#### Single-Element Data

Alpine data can be used within the same element:

```html
<button x-data="{ label: 'Click Here' }" x-text="label"></button>
```

#### Data-less Alpine

Sometimes you may want to use Alpine functionality without reactive data:

```html
<button x-data @click="alert('I\'ve been clicked!')">Click Me</button>
```

#### Re-usable Data

Extract the data portion using `Alpine.data()`:

```javascript
Alpine.data('dropdown', () => ({
    open: false,
    toggle() {
        this.open = ! this.open
    }
}))
```

Then use it in your markup:

```html
<div x-data="dropdown">
    <button @click="toggle">Expand</button>
    <span x-show="open">Content...</span>
</div>
```

#### Global State

Make data available to every component on the page using Alpine's global store:

```javascript
Alpine.store('tabs', {
    current: 'first',
    items: ['first', 'second', 'third'],
})
```

Access it anywhere:

```html
<div x-data>
    <template x-for="tab in $store.tabs.items">
        <!-- ... -->
    </template>
</div>

<div x-data>
    <button @click="$store.tabs.current = 'first'">First Tab</button>
</div>
```

### Templating

Alpine offers directives for manipulating the DOM.

#### Text Content (`x-text`)

Control the text content of an element:

```html
<div x-data="{ title: 'Start Here' }">
    <h1 x-text="title"></h1>
</div>
```

Like all directives, you can use any JavaScript expression:

```html
<span x-text="1 + 2"></span>
```

#### Toggling Elements

Alpine offers `x-show` and `x-if` for toggling elements.

##### `x-show`

Shows/hides elements by toggling CSS `display` property:

```html
<div x-data="{ open: false }">
    <button @click="open = ! open">Expand</button>
    <div x-show="open">
        Content...
    </div>
</div>
```

##### `x-if`

Completely adds/removes elements from the DOM:

```html
<div x-data="{ open: false }">
    <button @click="open = ! open">Expand</button>
    <template x-if="open">
        <div>
            Content...
        </div>
    </template>
</div>
```

**Important:** `x-if` must be declared on a `<template>` tag and can only contain one root element.

#### Toggling with Transitions

Apply smooth transitions using `x-transition` (only works with `x-show`, not `x-if`):

```html
<div x-data="{ open: false }">
    <button @click="open = ! open">Expands</button>
    <div x-show="open" x-transition>
        Content...
    </div>
</div>
```

**Transition Helpers:**

```html
<!-- Custom duration -->
<div x-show="open" x-transition.duration.500ms>

<!-- Different durations for enter/leave -->
<div
    x-show="open"
    x-transition:enter.duration.500ms
    x-transition:leave.duration.1000ms
>

<!-- Only opacity transition -->
<div x-show="open" x-transition.opacity>
```

**Transition Classes (Tailwind CSS example):**

```html
<div
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-90"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-90"
>
    Content...
</div>
```

#### Binding Attributes

Use `x-bind` (or `:`) to set HTML attributes based on JavaScript expressions:

```html
<div x-data="{ placeholder: 'Type here...' }">
    <input type="text" :placeholder="placeholder">
</div>
```

**Binding Classes:**

```html
<div x-data="{ open: false }">
    <button @click="open = ! open">Toggle Dropdown</button>
    <div :class="open ? '' : 'hidden'">
        Dropdown Contents...
    </div>
</div>
```

**Shorthand conditionals:**

```html
<!-- Using || -->
<div :class="show || 'hidden'">

<!-- Using && -->
<div :class="closed && 'hidden'">
```

**Class object syntax:**

```html
<div :class="{ 'hidden': ! show }">
```

**Binding Styles:**

```html
<div :style="{ color: 'red', display: 'flex' }">
    <!-- Will render: style="color: red; display: flex;" -->
</div>
```

### Installation

There are 2 ways to include Alpine:

#### From a Script Tag

Include the script tag in the head of your HTML page:

```html
<html>
<head>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/[email protected]/dist/cdn.min.js"></script>
</head>
<body>
    <!-- Your content -->
</body>
</html>
```

**Important:** Don't forget the `defer` attribute.

For production, hardcode the latest version:

```html
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/[email protected]/dist/cdn.min.js"></script>
```

#### As a Module

Install via NPM:

```bash
npm install alpinejs
```

Import and initialize:

```javascript
import Alpine from 'alpinejs'

window.Alpine = Alpine

Alpine.start()
```

**Important:**
- Register any extension code BETWEEN importing `Alpine` and calling `Alpine.start()`
- Ensure `Alpine.start()` is only called once per page

### Events

Alpine makes it simple to listen for browser events and react to them.

#### Listening for Simple Events

Use `x-on` (or `@` shorthand) to listen for browser events:

```html
<button x-on:click="console.log('clicked')">...</button>
<!-- or -->
<button @click="console.log('clicked')">...</button>
```

You can listen for any browser event: `@mouseenter`, `@keyup`, etc.

#### Listening for Specific Keys

Listen for specific keys using modifiers:

```html
<input @keyup.enter="alert('Submitted!')">
```

Chain modifiers for key combinations:

```html
<input @keyup.shift.enter="alert('Submitted!')">
```

**Common Key Modifiers:**
- `.shift`, `.ctrl`, `.cmd`, `.meta`, `.alt`
- `.enter`, `.space`, `.escape`, `.tab`
- `.up`, `.down`, `.left`, `.right`

#### Preventing Default

Use `.prevent` to prevent default behavior:

```html
<form @submit.prevent="...">...</form>
```

Use `.stop` for `event.stopPropagation()`:

```html
<div @click.stop="...">...</div>
```

#### Accessing the Event Object

Alpine automatically injects an `$event` magic variable:

```html
<button @click="$event.target.remove()">Remove Me</button>
```

#### Dispatching Custom Events

Use `$dispatch` to dispatch custom events:

```html
<div @foo="console.log('foo was dispatched')">
    <button @click="$dispatch('foo')"></button>
</div>
```

#### Listening for Events on Window

Listen for events on the window object using `.window`:

```html
<div x-data>
    <button @click="$dispatch('foo')"></button>
</div>

<div x-data @foo.window="console.log('foo was dispatched')">...</div>
```

### Lifecycle

Alpine has lifecycle hooks for different parts of its lifecycle.

#### Element Initialization (`x-init`)

Execute code when Alpine begins initializing an element:

```html
<button x-init="console.log('Im initing')">
```

Alpine automatically calls `init()` methods stored on data objects:

```javascript
Alpine.data('dropdown', () => ({
    init() {
        // Called before the element initializes
    }
}))
```

#### After a State Change

Execute code when data changes using `$watch` or `x-effect`.

##### `$watch`

Hook into data changes using dot-notation:

```html
<div x-data="{ open: false }" x-init="$watch('open', value => console.log(value))">
```

Access old value:

```html
<div x-data="{ open: false }" x-init="$watch('open', (value, oldValue) => console.log(value, oldValue))">
```

Watch deeply nested properties:

```html
<div x-data="{ foo: { bar: 'baz' }}" x-init="$watch('foo.bar', value => console.log(value))">
```

##### `x-effect`

Automatically watches any Alpine data used within the expression:

```html
<div x-data="{ open: false }" x-effect="console.log(open)">
```

**Differences from `$watch`:**
1. Runs right away AND when data changes (`$watch` is lazy)
2. No knowledge of the previous value

#### Alpine Initialization

##### `alpine:init`

Execute code after Alpine is loaded but BEFORE it initializes:

```javascript
document.addEventListener('alpine:init', () => {
    Alpine.data(...)
})
```

##### `alpine:initialized`

Execute code AFTER Alpine is done initializing:

```javascript
document.addEventListener('alpine:initialized', () => {
    // ...
})
```

---

