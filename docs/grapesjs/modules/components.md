# Component Manager

The Component is a base element of the template. It might be something simple and atomic like an image or a text box, but also complex structures composed by other components.

## How Components work?

Technically, once you drop your HTML block inside the canvas each element of the content is transformed into a GrapesJS Component. A GrapesJS Component is an object containing information about how the element is rendered in the canvas (managed in the View) and how it might look in its final code (managed in the Model).

## Built-in Component Types

GrapesJS comes with several built-in component types:
- `text`: Double click to edit with the Rich Text Editor.
- `image`: Double click to open the Asset Manager.
- `link`: For `<a>` elements.
- `video`: For `<video>`, `<iframe>`, etc.
- `map`: For Google Maps.

## Define Custom Component Type

You can define new types to bind custom behaviors.

```javascript
editor.DomComponents.addType('my-new-component', {
  isComponent: el => el.tagName === 'DIV' && el.classList.contains('my-class'),
  model: {
    defaults: {
      tagName: 'div',
      draggable: true,
      droppable: true,
      attributes: { class: 'my-class' },
      styles: `.my-class { color: red }`,
    },
  },
  view: {
    // Custom rendering logic
  },
});
```

## Lifecycle Hooks

Components trigger various hooks throughout their lifecycle:

- `model.init()`: Executed once the model is initialized.
- `view.onRender()`: Executed once the component is rendered on the canvas.
- `component:mount`: Global hook after rendering.
- `model.updated()`: Executed when a property is updated.
- `model.removed()`: Executed when the component is removed.

## Components & CSS

You can add component-specific styles using the `styles` property in the model defaults.

```javascript
model: {
  defaults: {
    attributes: { class: 'cmp-css' },
    styles: `.cmp-css { color: red }`,
  },
}
```

## JSX Syntax

GrapesJS supports JSX syntax for defining components, which can be more readable than nested objects or long HTML strings.

```javascript
editor.addComponents(
  <div>
    <custom-component title="foo">Hello!</custom-component>
  </div>
);
```
