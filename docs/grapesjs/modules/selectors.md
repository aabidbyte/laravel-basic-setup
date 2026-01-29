# Selector Manager

The Selector Manager manages CSS classes and states (e.g., `:hover`, `:active`) for components.

## Configuration

```javascript
const editor = grapesjs.init({
  // ...
  selectorManager: {
    componentFirst: true, // Target single components instead of classes
  }
});
```

## Component-First Selectors

By default, styling is applied to the classes attached to a component. Enabling `componentFirst` allows styling specific component instances regardless of their classes.

## Programmatic Usage

```javascript
const sm = editor.SelectorManager;

// Add a new selector
sm.add('my-class');

// Get all selectors
const selectors = sm.getAll();
```
