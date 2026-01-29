# Asset Manager

The Asset Manager handles the management of images and other media files.

## Configuration

```javascript
const editor = grapesjs.init({
  // ...
  assetManager: {
    assets: [
      'http://placehold.it/350x250/78c5d6/fff/image1.jpg',
      {
        src: 'http://placehold.it/350x250/459ba8/fff/image2.jpg',
        name: 'DisplayName'
      }
    ],
    upload: 'https://endpoint/upload/assets', // Endpoint for uploads
    uploadName: 'files',
  }
});
```

## Programmatic Usage

```javascript
const am = editor.AssetManager;

// Add new assets
am.add({ src: '...' });

// Get all assets
const assets = am.getAll();

// Remove an asset
am.remove('http://.../img.jpg');
```

## Global vs Visible

Asset Manager keeps two collections:
- `global`: All available assets (`am.getAll()`).
- `visible`: Currently rendered assets (`am.getAllVisible()`).

You can filter visible assets via `am.render(filteredAssets)`.
