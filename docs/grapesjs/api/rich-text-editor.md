# Rich Text Editor API

The RichTextEditor module allows you to customize the built-in toolbar and use HTML Editing APIs.

## Methods

- `getConfig()`: Get the configuration object.
- `add(id, action)`: Add a new action to the built-in RTE toolbar.
- `get(id)`: Get an action by ID.
- `getAll()`: Get all actions.
- `remove(id)`: Remove an action.
- `run(command, value)`: Run an action command (e.g., `insertHTML`).
- `getToolbarEl()`: Get the toolbar HTMLElement.

## Events

- `rte:enable`: Triggered when the RTE is enabled.
- `rte:disable`: Triggered when the RTE is disabled.
- `rte:custom`: Triggered for custom RTE events.
