# Plugins

Plugins are functions that extend the editor's functionality.

## Basic Plugin

```javascript
function myPlugin(editor, options) {
  editor.Blocks.add('my-block', {
    label: 'My Block',
    content: '<div>...</div>',
  });
}

const editor = grapesjs.init({
  // ...
  plugins: [myPlugin],
  pluginsOpts: {
    [myPlugin]: { /* options */ }
  }
});
```

## Usage with TypeScript

GrapesJS provides a `usePlugin` helper for better type safety.

```javascript
import { usePlugin } from 'grapesjs';

grapesjs.init({
  plugins: [
    usePlugin(myPlugin, { option1: 'val' })
  ]
});
```
