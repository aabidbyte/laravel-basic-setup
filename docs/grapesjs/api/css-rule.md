# CSS Rule API

The CssRule model represents a CSS rule in the project.

## Properties

- `selectors`: Collection of selectors.
- `style`: Object containing CSS properties.
- `mediaText`: Media query string.

## Methods

- `getAtRule()`: Get the at-rule (e.g., `@media`).
- `selectorsToString()`: Convert selectors to string.
- `getDeclaration(opts)`: Get the CSS declaration string.
- `getDevice()`: Get the related device (for media queries).
- `getState()`: Get the related state (e.g., `:hover`).
- `getComponent()`: Get the related component (if component-specific).
- `toCSS()`: Return the full CSS string of the rule.
