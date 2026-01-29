# Commands

Commands are reusable functions that can be triggered from various parts of the editor (e.g., buttons, keymaps).

## Basic Usage

```javascript
// Definition at init
const editor = grapesjs.init({
  // ...
  commands: {
    defaults: [
      {
        id: 'say-hello',
        run(editor, sender, options = {}) {
          alert('Hello ' + (options.name || 'World'));
        },
      }
    ]
  }
});

// Programmatic addition
editor.Commands.add('my-command', (editor) => {
  // Logic
});

// Running a command
editor.runCommand('say-hello', { name: 'User' });
```

## Default Commands

GrapesJS provides several core commands:
- `core:canvas-clear`: Clear the canvas.
- `core:component-delete`: Delete the selected component.
- `core:open-assets`: Open the Asset Manager.
- `core:undo` / `core:redo`: Standard undo/redo operations.

## Stateful Commands

Commands can have a `stop` method for toggleable behaviors.

```javascript
editor.Commands.add('toggle-feature', {
  run(editor) {
    // Enable feature
  },
  stop(editor) {
    // Disable feature
  }
});
```
