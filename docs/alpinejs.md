# Alpine.js Documentation

> **Important**: This documentation is compiled from the official Alpine.js documentation. When working with Alpine.js in this project, refer to this file for comprehensive usage examples, directives, magics, and best practices.

## Table of Contents

- [Getting Started](#getting-started)
- [Essentials](#essentials)
  - [State](#state)
  - [Templating](#templating)
  - [Installation](#installation)
  - [Events](#events)
  - [Lifecycle](#lifecycle)
- [Directives](#directives)
- [Magics](#magics)
- [Global APIs](#global-apis)
- [Plugins](#plugins)
- [Components](#components)
- [Advanced Topics](#advanced-topics)

---

## Getting Started

### Quick Start

Create a blank HTML file and include Alpine.js:

```html
<html>
<head>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/[email protected]/dist/cdn.min.js"></script>
</head>
<body>
    <h1 x-data="{ message: 'I ❤️ Alpine' }" x-text="message"></h1>
</body>
</html>
```

### Building a Counter

A simple counter component demonstrating state and event listening:

```html
<div x-data="{ count: 0 }">
    <button x-on:click="count++">Increment</button>
    <span x-text="count"></span>
</div>
```

**Key Concepts:**
- `x-data` declares the component's reactive state
- `x-on:click` (or `@click`) listens for click events
- `x-text` sets the text content reactively

### Building a Dropdown

A dropdown component using `x-show`:

```html
<div x-data="{ open: false }">
    <button @click="open = ! open">Toggle</button>
    <div x-show="open" @click.outside="open = false">
        Contents...
    </div>
</div>
```

**Key Concepts:**
- `x-show` toggles visibility by adding/removing `display: none`
- `.outside` modifier listens for clicks outside the element

---

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

## Directives

### `x-data`

Everything in Alpine starts with `x-data`. It defines a chunk of HTML as an Alpine component and provides reactive data.

```html
<div x-data="{ open: false }">
    <button @click="open = ! open">Toggle Content</button>
    <div x-show="open">Content...</div>
</div>
```

**Scope:** Properties defined in `x-data` are available to all element children, including nested `x-data` components.

**Methods:** Store methods in `x-data`:

```html
<div x-data="{ open: false, toggle() { this.open = ! this.open } }">
    <button @click="toggle()">Toggle Content</button>
</div>
```

**Getters:** Use JavaScript getters for computed properties:

```html
<div x-data="{
    open: false,
    get isOpen() { return this.open },
    toggle() { this.open = ! this.open },
}">
    <div x-show="isOpen">Content...</div>
</div>
```

**Data-less components:**

```html
<div x-data>
    <!-- or -->
<div x-data="{}">
```

**Single-element components:**

```html
<button x-data="{ open: true }" @click="open = false" x-show="open">
    Hide Me
</button>
```

[Read more about x-data →](https://alpinejs.dev/directives/data)

### `x-init`

Hook into the initialization phase of any element:

```html
<div x-init="console.log('I\'m being initialized!')"></div>
```

Fetch data during initialization:

```html
<div
    x-data="{ posts: [] }"
    x-init="posts = await (await fetch('/posts')).json()"
>...</div>
```

**`$nextTick`:** Wait until after Alpine has finished rendering:

```html
<div x-init="$nextTick(() => { ... })"></div>
```

**Auto-evaluate `init()` method:** If `x-data` contains an `init()` method, it's called automatically:

```html
<div x-data="{
    init() {
        console.log('I am called automatically')
    }
}">...</div>
```

If both `x-data` `init()` method and `x-init` directive exist, the method is called first.

[Read more about x-init →](https://alpinejs.dev/directives/init)

### `x-bind`

Set HTML attributes based on JavaScript expressions. Use `:` as shorthand.

```html
<div x-data="{ placeholder: 'Type here...' }">
    <input type="text" x-bind:placeholder="placeholder">
    <!-- or -->
    <input type="text" :placeholder="placeholder">
</div>
```

**Binding Classes:**

```html
<div :class="open ? '' : 'hidden'">
<!-- or using object syntax -->
<div :class="{ 'hidden': ! show }">
```

Alpine preserves existing classes when binding, so you can mix static and dynamic classes:

```html
<div class="opacity-50" :class="hide && 'hidden'">
    <!-- Results in: class="opacity-50 hidden" -->
</div>
```

**Binding Styles:**

```html
<div :style="{ color: 'red', display: 'flex' }">
    <!-- Will render: style="color: red; display: flex;" -->
</div>
```

Mix with existing styles:

```html
<div style="padding: 1rem;" :style="{ color: 'red', display: 'flex' }">
    <!-- Will render: style="padding: 1rem; color: red; display: flex;" -->
</div>
```

[Read more about x-bind →](https://alpinejs.dev/directives/bind)

### `x-show`

Show and hide DOM elements by toggling CSS `display` property.

```html
<div x-data="{ open: false }">
    <button @click="open = ! open">Toggle Dropdown</button>
    <div x-show="open">
        Dropdown Contents...
    </div>
</div>
```

**With transitions:**

```html
<div x-show="open" x-transition>
    Content...
</div>
```

**Using the important modifier:**

```html
<div x-show.important="open">
    <!-- Uses display: none !important -->
</div>
```

**Note:** If the default state is `false`, use `x-cloak` to avoid page flicker.

[Read more about x-show →](https://alpinejs.dev/directives/show)

### `x-on`

Listen for browser events. Use `@` as shorthand.

```html
<button x-on:click="alert('Hello World!')">Say Hi</button>
<!-- or -->
<button @click="alert('Hello World!')">Say Hi</button>
```

**Note:** `x-on` can only listen for events with lowercase names (HTML attributes are case-insensitive).

**The event object:** Use `$event` magic variable:

```html
<button @click="alert($event.target.getAttribute('message'))" message="Hello World">
    Say Hi
</button>
```

**Keyboard events:**

```html
<input type="text" @keyup.enter="alert('Submitted!')">
<input type="text" @keyup.shift.enter="alert('Submitted!')">
```

Common key modifiers: `.shift`, `.enter`, `.space`, `.ctrl`, `.cmd`, `.meta`, `.alt`, `.up`, `.down`, `.left`, `.right`, `.escape`, `.tab`, etc.

**Mouse events:** Modifiers work on click events too:

```html
<button @click.shift="message = 'added to selection'">...</button>
```

**Custom events:**

```html
<div x-data @foo="alert('Button Was Clicked!')">
    <button @click="$dispatch('foo')">...</button>
</div>
```

**Modifiers:**
- `.prevent` - Prevents default behavior
- `.stop` - Stops event propagation
- `.once` - Only trigger once
- `.debounce` - Debounce the handler
- `.window` - Listen on window object
- `.outside` - Listen for clicks outside the element
- `.document` - Listen on document object

[Read more about x-on →](https://alpinejs.dev/directives/on)

### `x-text`

Set the text content of an element to the result of an expression.

```html
<div x-data="{ username: 'calebporzio' }">
    Username: <strong x-text="username"></strong>
</div>
```

You can use any JavaScript expression:

```html
<span x-text="1 + 2"></span>
```

[Read more about x-text →](https://alpinejs.dev/directives/text)

### `x-html`

Set the `innerHTML` property of an element to the result of an expression.

**⚠️ Warning:** Only use on trusted content and never on user-provided content. Dynamically rendering HTML from third parties can easily lead to XSS vulnerabilities.

```html
<div x-data="{ username: '<strong>calebporzio</strong>' }">
    Username: <span x-html="username"></span>
</div>
```

[Read more about x-html →](https://alpinejs.dev/directives/html)

### `x-model`

Bind the value of an input element to Alpine data (two-way binding).

```html
<div x-data="{ message: '' }">
    <input type="text" x-model="message">
    <span x-text="message"></span>
</div>
```

**Works with:**
- `<input type="text">`
- `<textarea>`
- `<input type="checkbox">`
- `<input type="radio">`
- `<select>`
- `<input type="range">`

**Checkbox examples:**

Single checkbox with boolean:

```html
<input type="checkbox" id="checkbox" x-model="show">
<label for="checkbox" x-text="show"></label>
```

Multiple checkboxes bound to array:

```html
<input type="checkbox" value="red" x-model="colors">
<input type="checkbox" value="orange" x-model="colors">
<input type="checkbox" value="yellow" x-model="colors">
Colors: <span x-text="colors"></span>
```

**Radio inputs:**

```html
<input type="radio" value="yes" x-model="answer">
<input type="radio" value="no" x-model="answer">
Answer: <span x-text="answer"></span>
```

**Select inputs:**

Single select:

```html
<select x-model="color">
    <option>Red</option>
    <option>Orange</option>
    <option>Yellow</option>
</select>
Color: <span x-text="color"></span>
```

Single select with placeholder:

```html
<select x-model="color">
    <option value="" disabled>Select A Color</option>
    <option>Red</option>
    <option>Orange</option>
    <option>Yellow</option>
</select>
```

Multiple select:

```html
<select x-model="color" multiple>
    <option>Red</option>
    <option>Orange</option>
    <option>Yellow</option>
</select>
Colors: <span x-text="color"></span>
```

**Modifiers:**

- `.lazy` - Update property only when user focuses away
- `.number` - Store value as JavaScript number
- `.boolean` - Store value as JavaScript boolean
- `.debounce` - Debounce the updating

[Read more about x-model →](https://alpinejs.dev/directives/model)

### `x-modelable`

Expose any Alpine property as the target of `x-model`.

```html
<div x-data="{ number: 5 }">
    <div x-data="{ count: 0 }" x-modelable="count" x-model="number">
        <button @click="count++">Increment</button>
    </div>
    Number: <span x-text="number"></span>
</div>
```

Useful for abstracting Alpine components into backend templates.

[Read more about x-modelable →](https://alpinejs.dev/directives/modelable)

### `x-for`

Create DOM elements by iterating through a list.

```html
<ul x-data="{ colors: ['Red', 'Orange', 'Yellow'] }">
    <template x-for="color in colors">
        <li x-text="color"></li>
    </template>
</ul>
```

**Rules:**
- `x-for` MUST be declared on a `<template>` element
- That `<template>` element MUST contain only one root element

**Keys:** Specify unique keys for re-ordering items:

```html
<ul x-data="{ colors: [
    { id: 1, label: 'Red' },
    { id: 2, label: 'Orange' },
    { id: 3, label: 'Yellow' },
]}">
    <template x-for="color in colors" :key="color.id">
        <li x-text="color.label"></li>
    </template>
</ul>
```

**Accessing indexes:**

```html
<template x-for="(color, index) in colors">
    <li>
        <span x-text="index + ': '"></span>
        <span x-text="color"></span>
    </li>
</template>
```

**Iterating over objects:**

```html
<ul x-data="{ car: { make: 'Jeep', model: 'Grand Cherokee', color: 'Black' } }">
    <template x-for="(value, index) in car">
        <li>
            <span x-text="index"></span>: <span x-text="value"></span>
        </li>
    </template>
</ul>
```

**Iterating over a range:**

```html
<ul>
    <template x-for="i in 10">
        <li x-text="i"></li>
    </template>
</ul>
```

[Read more about x-for →](https://alpinejs.dev/directives/for)

### `x-transition`

Create smooth transitions when elements are shown or hidden (only works with `x-show`, not `x-if`).

**Simple transition:**

```html
<div x-data="{ open: false }">
    <button @click="open = ! open">Toggle</button>
    <div x-show="open" x-transition>
        Hello 👋
    </div>
</div>
```

**Customizing duration:**

```html
<div x-show="open" x-transition.duration.500ms>
<!-- or separate enter/leave -->
<div
    x-show="open"
    x-transition:enter.duration.500ms
    x-transition:leave.duration.400ms
>
```

**Customizing delay:**

```html
<div x-show="open" x-transition.delay.50ms>
```

**Only opacity:**

```html
<div x-show="open" x-transition.opacity>
```

**Only scale:**

```html
<div x-show="open" x-transition.scale>
<div x-show="open" x-transition.scale.80>
```

**Custom origin:**

```html
<div x-show="open" x-transition.scale.origin.top>
<!-- or combined -->
<div x-show="open" x-transition.scale.origin.top.right>
```

**Applying CSS classes:**

```html
<div
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90"
>
    Hello 👋
</div>
```

**Transition phases:**
- `:enter` - Applied during entire entering phase
- `:enter-start` - Added before element is inserted, removed one frame after
- `:enter-end` - Added one frame after insertion, removed when transition finishes
- `:leave` - Applied during entire leaving phase
- `:leave-start` - Added immediately when leaving triggered, removed after one frame
- `:leave-end` - Added one frame after leaving triggered, removed when transition finishes

[Read more about x-transition →](https://alpinejs.dev/directives/transition)

### `x-effect`

Re-evaluate an expression when one of its dependencies change. Automatically watches all properties used within it.

```html
<div x-data="{ label: 'Hello' }" x-effect="console.log(label)">
    <button @click="label += ' World!'">Change Message</button>
</div>
```

When the component loads, "Hello" is logged. When the button is clicked and `label` changes, "Hello World!" is logged.

[Read more about x-effect →](https://alpinejs.dev/directives/effect)

### `x-if`

Toggle elements by completely adding/removing them from the DOM (unlike `x-show` which uses CSS).

```html
<template x-if="open">
    <div>Contents...</div>
</template>
```

**Important:**
- `x-if` must be applied to a `<template>` tag, not directly to the element
- `<template>` tags can only contain one root element
- `x-if` does NOT support `x-transition`

[Read more about x-if →](https://alpinejs.dev/directives/if)

### `x-ref`

Reference DOM elements for easy access using `$refs`.

```html
<button @click="$refs.text.remove()">Remove Text</button>
<span x-ref="text">Hello 👋</span>
```

**Note:** `x-ref` must be used within an element that has `x-data` defined.

[Read more about x-ref →](https://alpinejs.dev/directives/ref)

### `x-cloak`

Hide elements until Alpine is fully loaded to prevent "blip" of uninitialized templates.

**Required CSS:**

```css
[x-cloak] { display: none !important; }
```

**Usage:**

```html
<span x-cloak x-show="false">This will not 'blip' onto screen at any point</span>
<span x-cloak x-text="message"></span>
```

When Alpine loads, it removes the `x-cloak` attribute, showing the element.

**Alternative:** Use `x-if="true"` on a `<template>` element as an alternative to global CSS:

```html
<template x-if="true">
    <span x-text="message"></span>
</template>
```

[Read more about x-cloak →](https://alpinejs.dev/directives/cloak)

### `x-ignore`

Prevent Alpine from initializing a specific section of HTML.

```html
<div x-data="{ label: 'From Alpine' }">
    <div x-ignore>
        <span x-text="label"></span>
        <!-- This span will NOT contain "From Alpine" -->
    </div>
</div>
```

[Read more about x-ignore →](https://alpinejs.dev/directives/ignore)

### `x-id`

Declare a new "scope" for IDs generated using `$id()`. Accepts an array of strings (ID names) and adds a unique suffix to each `$id()` generated within it.

```html
<div x-id="['text-input']">
    <label :for="$id('text-input')">Username</label>
    <!-- for="text-input-1" -->
    <input type="text" :id="$id('text-input')">
    <!-- id="text-input-1" -->
</div>

<div x-id="['text-input']">
    <label :for="$id('text-input')">Username</label>
    <!-- for="text-input-2" -->
    <input type="text" :id="$id('text-input')">
    <!-- id="text-input-2" -->
</div>
```

**Note:** `x-id` must be used within an element that has `x-data` defined.

[Read more about x-id →](https://alpinejs.dev/directives/id)

### `x-teleport`

Transport part of your Alpine template to another part of the DOM entirely. Useful for modals (especially nesting them) to break out of z-index constraints.

```html
<div x-data="{ open: false }">
    <button @click="open = ! open">Toggle Modal</button>
    <template x-teleport="body">
        <div x-show="open">
            Modal contents...
        </div>
    </template>
</div>
```

The selector can be any valid CSS selector: tag name (`body`), class (`.my-class`), ID (`#my-id`), etc.

**Forwarding events:** Register event listeners on the `<template x-teleport...>` element:

```html
<div x-data="{ open: false }">
    <button @click="open = ! open">Toggle Modal</button>
    <template x-teleport="body" @click="open = false">
        <div x-show="open">
            Modal contents... (click to close)
        </div>
    </template>
</div>
```

**Nesting:** Especially helpful for nesting modals:

```html
<div x-data="{ open: false }">
    <button @click="open = ! open">Toggle Modal</button>
    <template x-teleport="body">
        <div x-show="open">
            Modal contents...
            <div x-data="{ open: false }">
                <button @click="open = ! open">Toggle Nested Modal</button>
                <template x-teleport="body">
                    <div x-show="open">
                        Nested modal contents...
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
```

[Read more about x-teleport →](https://alpinejs.dev/directives/teleport)

---

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

## Components

### Dropdown

A complete dropdown component example:

```html
<div
    x-data="{
        open: false,
        toggle() {
            if (this.open) {
                return this.close()
            }
            this.$refs.button.focus()
            this.open = true
        },
        close(focusAfter) {
            if (! this.open) return
            this.open = false
            focusAfter && focusAfter.focus()
        }
    }"
    @keydown.escape.prevent.stop="close($refs.button)"
    @focusin.window="! $refs.panel.contains($event.target) && close()"
    x-id="['dropdown-button']"
    class="relative"
>
    <!-- Button -->
    <button
        x-ref="button"
        @click="toggle()"
        :aria-expanded="open"
        :aria-controls="$id('dropdown-button')"
        type="button"
    >
        Options
    </button>

    <!-- Panel -->
    <div
        x-ref="panel"
        x-show="open"
        x-transition.origin.top.left
        @click.outside="close($refs.button)"
        :id="$id('dropdown-button')"
        x-cloak
    >
        <!-- Dropdown items -->
    </div>
</div>
```

[Read more components →](https://alpinejs.dev/components)

---

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

## Upgrade Guide (V2 to V3)

Key breaking changes in Alpine V3:

1. **`$el` is now always the current element** - Use `$root` for the root element
2. **Automatically evaluate `init()` functions** - No need to manually call `init()` on data objects
3. **Need to call `Alpine.start()` after import** - Required when importing as a module
4. **`x-show.transition` is now `x-transition`** - Use `x-show="open" x-transition` instead
5. **`x-if` no longer supports `x-transition`** - Use `x-show` with transitions instead
6. **`x-data` cascading scope** - Scope is available to all children unless overwritten
7. **`x-init` no longer accepts a callback return** - Changes to initialization behavior
8. **Returning `false` from event handlers** - No longer implicitly prevents default
9. **`x-spread` is now `x-bind`** - Directive renamed
10. **`x-ref` no longer supports binding** - Only static references supported
11. **Use global lifecycle events** - Instead of `Alpine.deferLoadingAlpine()`
12. **IE11 no longer supported**

[Read full upgrade guide →](https://alpinejs.dev/upgrade-guide)

---

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

For complete Livewire 4 documentation, see [docs/livewire-4.md](./livewire-4.md).

---

## References

- [Official Alpine.js Documentation](https://alpinejs.dev/)
- [Alpine.js GitHub Repository](https://github.com/alpinejs/alpine)
- [Livewire 4 Documentation](./livewire-4.md) - Alpine.js integration with Livewire
- [JavaScript & Alpine.js Improvements](./javascript-alpine-improvements.md) - Codebase improvements and best practices

---

*This documentation is compiled from the official Alpine.js documentation. For the most up-to-date information, refer to the official website.*

