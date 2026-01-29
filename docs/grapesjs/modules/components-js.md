# Components & JS

If you want to create Components with JavaScript attached (e.g., counters, galleries, slideshows), GrapesJS allows you to define a `script` property.

## Basic Scripts

```javascript
editor.DomComponents.addType('my-component-with-js', {
  model: {
    defaults: {
      script: function() {
        // This function will be executed in the canvas
        // `this` refers to the element's DOM node
        console.log('Component initialized', this);
      },
    },
  },
});
```

The script is stringified and placed in the final export code.

## Passing Properties to Scripts

You can pass properties from the component model to the script:

```javascript
editor.DomComponents.addType('my-component', {
  model: {
    defaults: {
      script: function() {
        const value = '{[ my-prop ]}';
        console.log('Property value:', value);
      },
      'my-prop': 'some-value',
    },
  },
});
```

## Dependencies

If your script depends on external libraries (like jQuery), you can specify them in the `script` option or ensure they are loaded in the canvas.
