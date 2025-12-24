## Routing

For full-page components, use `Route::livewire()`:

```php
// Recommended for all component types
Route::livewire('/dashboard', Dashboard::class);

// For view-based components, you can use the component name
Route::livewire('/dashboard', 'pages::dashboard');
```

Using `Route::livewire()` is now the preferred method and is required for single-file and multi-file components to work correctly as full-page components.

