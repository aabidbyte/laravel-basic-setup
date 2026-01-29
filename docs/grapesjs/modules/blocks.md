# Block Manager

Blocks are reusable pieces of HTML that users can drag into the canvas.

## Configuration

Blocks can be defined during initialization:

```javascript
const editor = grapesjs.init({
  // ...
  blockManager: {
    appendTo: '#blocks',
    blocks: [
      {
        id: 'my-block',
        label: 'My Block',
        content: '<div class="my-block">Hello World</div>',
        category: 'Basic',
      },
    ],
  },
});
```

## Block Content Types

- **HTML strings**: Simple strings that get parsed.
- **Component Definition**: JSON objects representing components.

## Programmatic Usage

You can add blocks dynamically:

```javascript
editor.BlockManager.add('my-block-id', {
  label: 'New Block',
  content: {
    tagName: 'div',
    components: [
      { tagName: 'span', content: 'Inner content' }
    ]
  },
});
```
