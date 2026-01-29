# Style Manager

The Style Manager allows users to style components via a set of CSS properties.

## Configuration

```javascript
const editor = grapesjs.init({
  // ...
  styleManager: {
    sectors: [
      {
        name: 'Dimension',
        open: false,
        buildProps: ['width', 'height', 'padding'],
        properties: [
          {
            type: 'number',
            property: 'width',
            label: 'The Width',
            units: ['px', '%'],
            defaults: 'auto',
          }
        ]
      }
    ]
  }
});
```

## Programmatic Usage

```javascript
const sm = editor.StyleManager;

// Add a new sector
sm.addSector('new-sector', {
  name: 'New Sector',
  properties: ['color', 'font-size'],
});

// Select a target manually
sm.select(someComponent);
```

## Built-in Properties

GrapesJS provides a long list of built-in properties (width, color, background-color, etc.) that can be easily added via `buildProps`.
