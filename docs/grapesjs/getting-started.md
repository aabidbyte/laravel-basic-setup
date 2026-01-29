# Getting Started

This is a step-by-step guide for anyone who wants to create their own builder with GrapesJS. This is not a comprehensive guide, just a concise overview of the most common modules. Follow along to create a page builder from scratch.

## Import the library

Before you start using GrapesJS, you'll have to import it. Let's import the latest version:

```html
<link rel="stylesheet" href="//unpkg.com/grapesjs/dist/css/grapes.min.css" />
<script src="//unpkg.com/grapesjs"></script>
```

Or if you're in a Node environment:

```javascript
import 'grapesjs/dist/css/grapes.min.css';
import grapesjs from 'grapesjs';
```

## Start from the canvas

The first step is to define the interface of our editor. The main part of the GrapesJS editor is the canvas, this is where you create the structure of your templates.

```html
<div id="gjs">
  <h1>Hello World Component!</h1>
</div>
```

```javascript
const editor = grapesjs.init({
  container: '#gjs',
  fromElement: true,
  height: '300px',
  width: 'auto',
  storageManager: false,
  panels: { defaults: [] },
});
```

## Add Blocks

Blocks are reusable pieces of HTML that you can drop in the canvas.

```javascript
const editor = grapesjs.init({
  // ...
  blockManager: {
    appendTo: '#blocks',
    blocks: [
      {
        id: 'section',
        label: '<b>Section</b>',
        attributes: { class: 'gjs-block-section' },
        content: `<section>
          <h1>This is a simple title</h1>
          <div>This is just a Lorem text: Lorem ipsum dolor sit amet</div>
        </section>`,
      },
      {
        id: 'text',
        label: 'Text',
        content: '<div data-gjs-type="text">Insert your text here</div>',
      },
      {
        id: 'image',
        label: 'Image',
        select: true,
        content: { type: 'image' },
        activate: true,
      },
    ],
  },
});
```

## Define Components

Each element in the canvas is transformed into a Component. Components are objects containing information about how the element is rendered and how it looks in the final code.

## Panels & Buttons

Custom panels and buttons can trigger commands.

```javascript
editor.Panels.addPanel({
  id: 'basic-actions',
  el: '.panel__basic-actions',
  buttons: [
    {
      id: 'visibility',
      active: true,
      className: 'btn-toggle-borders',
      label: '<u>B</u>',
      command: 'sw-visibility',
    },
    {
      id: 'export',
      className: 'btn-open-export',
      label: 'Exp',
      command: 'export-template',
      context: 'export-template',
    },
  ],
});
```

## Layers

The layer manager provides a tree overview of the document structure.

```javascript
const editor = grapesjs.init({
  // ...
  layerManager: {
    appendTo: '.layers-container',
  },
});
```

## Style Manager

The Style Manager allows users to style components using CSS properties.

```javascript
styleManager: {
  appendTo: '.styles-container',
  sectors: [{
    name: 'Dimension',
    open: false,
    buildProps: ['width', 'min-height', 'padding'],
    properties: [
      {
        type: 'integer',
        name: 'The width',
        property: 'width',
        units: ['px', '%'],
        defaults: 'auto',
        min: 0,
      },
    ],
  }],
}
```

## Traits

Traits are used to update HTML attributes (e.g., placeholder, alt).

```javascript
traitManager: {
  appendTo: '.traits-container',
},
```

## Responsive templates

Define devices and buttons for switching viewports.

```javascript
deviceManager: {
  devices: [
    { name: 'Desktop', width: '' },
    { name: 'Mobile', width: '320px', widthMedia: '480px' },
  ],
},
```

## Store & load data

GrapesJS supports local storage and remote storage.

```javascript
storageManager: {
  type: 'local', // or 'remote'
  autosave: true,
  autoload: true,
  options: {
    local: { key: 'gjsProject' },
  },
},
```

## Theming

Customize the editor's appearance using CSS variables or custom CSS rules.

```css
:root {
  --gjs-primary-color: #78366a;
  --gjs-secondary-color: rgba(255, 255, 255, 0.7);
  --gjs-tertiary-color: #ec5896;
  --gjs-quaternary-color: #ec5896;
}
```
