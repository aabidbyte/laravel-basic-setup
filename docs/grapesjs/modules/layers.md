# Layer Manager

The Layer Manager provides a tree representation of the components in the canvas.

## Configuration

```javascript
const editor = grapesjs.init({
  // ...
  layerManager: {
    root: '#my-custom-root', // Root element for the layers tree
    sortable: true,
    hidable: true,
  }
});
```

## Programmatic Usage

```javascript
const lm = editor.LayerManager;

// Select the root layer
lm.setRoot('#some-id');
```

## Customization

You can create a custom UI for the Layer Manager by setting `custom: true` and listening to events.

```javascript
const editor = grapesjs.init({
  // ...
  layerManager: { custom: true },
});

editor.on('layer:custom', (props) => {
  // props.container is the HTMLElement where you can append your custom UI
});
```
