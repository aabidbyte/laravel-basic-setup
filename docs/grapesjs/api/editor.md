# Editor API

The Editor instance is the main entry point to GrapesJS.

## Methods

### Content Management

- `getHtml()`: Returns HTML built inside the canvas.
- `getCss()`: Returns CSS built inside the canvas.
- `getJs()`: Returns JS built inside the canvas.
- `getComponents()`: Return the complete tree of components.
- `setComponents(components)`: Set components inside the canvas (overwrites actual components).
- `addComponents(components)`: Add components to the canvas.

### Selection

- `getSelected()`: Returns the last selected component.
- `getSelectedAll()`: Returns an array of all selected components.
- `select(component)`: Select a component.

### Storage

- `getProjectData()`: Get the JSON project data.
- `loadProjectData(json)`: Load the JSON project data.
- `store()`: Manually trigger store.
- `load()`: Manually trigger load.

### Commands

- `runCommand(id, options)`: Execute a command.
- `stopCommand(id, options)`: Stop a command.

### Events

- `on(event, callback)`: Attach an event listener.
- `once(event, callback)`: Attach a one-time event listener.
- `off(event, callback)`: Remove an event listener.

### Configuration

- `getConfig()`: Get the editor configuration.
- `setCustomRte(options)`: Replace the built-in Rich Text Editor.
- `setCustomParserCss(parser)`: Replace the built-in CSS parser.
