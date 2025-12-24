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

