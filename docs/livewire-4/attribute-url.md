# #[Url]

The `#[Url]` attribute binds a component property to a URL query parameter.

## Basic Usage

```php
use Livewire\Attributes\Url;

#[Url]
public $search = '';
```

## Options

-   `#[Url(as: 'q')]`: Use a different name in the URL (e.g., `?q=searchterm`).
-   `#[Url(history: true)]`: Update the browser history on every change (default).
-   `#[Url(keep: true)]`: Keep the parameter in the URL even if it matches the default value.
