# Modal

The Modal module provides a simple way to display custom content in a dialog.

## Basic Usage

```javascript
editor.Modal.open({
  title: 'My Title',
  content: '<div>My Content</div>',
});
```

## API usage

```javascript
const modal = editor.Modal;

modal.setTitle('New Title');
modal.setContent('New Content');
modal.close();
modal.isOpen();
```

## Customization

You can pass custom attributes to the modal for styling purposes.

```javascript
editor.Modal.open({
  title: 'Small Modal',
  content: '...',
  attributes: { class: 'my-small-modal' },
});
```
