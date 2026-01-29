# Page Manager

The Page Manager handles multiple pages within a single GrapesJS project.

## Initialization

```javascript
const editor = grapesjs.init({
  // ...
  pageManager: {
    pages: [
      {
        id: 'page-1',
        component: '<div>Page 1 Content</div>',
        styles: '.page1 { color: red }',
      },
      {
        id: 'page-2',
        component: '<div>Page 2 Content</div>',
        styles: '.page2 { color: blue }',
      }
    ]
  }
});
```

## Programmatic Usage

```javascript
const pm = editor.Pages;

// Get all pages
const allPages = pm.getAll();

// Add a new page
const newPage = pm.add({
  id: 'new-page',
  component: '<div>New Page</div>',
});

// Select a page
pm.select('new-page');

// Remove a page
pm.remove('page-1');
```

## Events

Listen to the `page` event for any changes in the Page Manager.

```javascript
editor.on('page', () => {
  // Update UI or perform actions on page change
});
```
