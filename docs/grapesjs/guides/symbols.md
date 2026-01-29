# Symbols

Symbols allow you to reuse components and keep them in sync across the project. Any change to the "Main Symbol" is replicated to all its "Instance Symbols".

## Concept

- **Main Symbol**: The source of truth stored in the project.
- **Instance Symbol**: A copy of the Main Symbol placed in the canvas.

## Programmatic Usage

### Create a Symbol

```javascript
const anyComponent = editor.getSelected();
const symbolMain = editor.Components.addSymbol(anyComponent);
```

The original component becomes an instance, and `symbolMain` is the new Main Symbol.

### Get Symbols

```javascript
const symbols = editor.Components.getSymbols();
```

### Symbol Details

```javascript
const info = editor.Components.getSymbolInfo(component);
console.log(info.isSymbol); // true
console.log(info.isMain); // true/false
console.log(info.instances); // Array of instance components
```

## Events

You can listen to symbol-related events like `component:symbol:add`, `component:symbol:remove`, etc.
