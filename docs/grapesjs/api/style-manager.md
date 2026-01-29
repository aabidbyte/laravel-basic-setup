# Style Manager API

The Style Manager module manages the styling of components.

## Methods

- `getConfig()`: Get the configuration object.
- `addSector(id, options)`: Add a new sector of properties.
- `getSector(id)`: Get a sector by ID.
- `removeSector(id)`: Remove a sector.
- `addProperty(sectorId, property)`: Add a new property to a sector.
- `getProperty(sectorId, id)`: Get a property by ID.
- `select(target)`: Select a target (Component or CSSRule) to style.
- `getSelected()`: Get the last selected target.
- `addStyleTargets(style)`: Update selected targets with custom styles.
- `addBuiltIn(id, definition)`: Add a built-in property definition.
