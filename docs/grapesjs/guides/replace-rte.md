# Replace the built-in Rich Text Editor

GrapesJS allows you to replace the default Rich Text Editor (RTE) with third-party libraries like CKEditor, Quill, or TinyMCE.

## Interface

To replace the RTE, you need to provide an object with `enable` and `disable` methods.

```javascript
editor.setCustomRte({
  enable(el, rte) {
    // el: The HTMLElement of the component
    // rte: The custom RTE instance (if already created)
    
    // Initialize your library here
    const myRte = MyLibrary.init(el);
    return myRte;
  },
  disable(el, rte) {
    // Destroy your library here
    rte.destroy();
  },
});
```

## Toolbar Position

You can customize the toolbar position by listening to the `rteToolbarPosUpdate` event.

```javascript
editor.on('rteToolbarPosUpdate', (pos) => {
  // Update pos.top and pos.left
});
```

## Built-in vs Third-party

When using a third-party RTE, GrapesJS just stores the HTML content. Behavior like link editing must be handled by the third-party library itself.
