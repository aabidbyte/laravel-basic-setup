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

