# Commands API

The Commands module manages the definition and execution of editor commands.

## Methods

- `add(id, command)`: Add a new command.
- `get(id)`: Get a command by ID.
- `extend(id, command)`: Extend an existing command.
- `has(id)`: Check if a command exists.
- `getAll()`: Get all commands.
- `run(id, options)`: Execute a command.
- `stop(id, options)`: Stop a command.
- `isActive(id)`: Check if a command is currently active.
- `getActive()`: Get all active commands.
