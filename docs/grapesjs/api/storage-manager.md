# Storage Manager API

The Storage Manager module handles the persistence of project data.

## Methods

- `getConfig()`: Get the configuration object.
- `isAutosave()`: Check if autosave is enabled.
- `setAutosave(value)`: Enable or disable autosave.
- `getStepsBeforeSave()`: Get steps required before saving.
- `setStepsBeforeSave(value)`: Set steps required before saving.
- `add(id, storage)`: Add a new storage strategy.
- `get(id)`: Get a storage strategy by ID.
- `store(data, options)`: Store data in the current storage.
- `load(keys, options)`: Load data from the current storage.
- `setCurrent(id)`: Set the current storage strategy.
