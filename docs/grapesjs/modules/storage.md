# Storage Manager

The Storage Manager handles the persistence of the project data (JSON).

## Configuration

```javascript
const editor = grapesjs.init({
  // ...
  storageManager: {
    type: 'local', // local | remote
    autosave: true,
    autoload: true,
    stepsBeforeSave: 1, // Save after N changes
    options: {
      local: { key: 'gjsProject' },
      remote: {
        urlStore: '/api/store',
        urlLoad: '/api/load',
      }
    }
  }
});
```

## Project Data

The `projectData` is the source of truth. Always use `getProjectData` and `loadProjectData` for persistence.

```javascript
// Get current state
const data = editor.getProjectData();

// Load state
editor.loadProjectData(data);
```

## Remote Storage Setup

When using remote storage, ensure your server responds with the project data JSON and handles CORS properly. You can customize fetch options:

```javascript
storageManager: {
  type: 'remote',
  options: {
    remote: {
      fetchOptions: opts => (opts.method === 'POST' ? { method: 'PATCH' } : {}),
      onStore: data => ({ myData: data }),
      onLoad: res => res.myData,
    }
  }
}
```
