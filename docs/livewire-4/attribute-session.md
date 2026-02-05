# #[Session]

The `#[Session]` attribute automatically persists a property's value into the user's session.

## Basic Usage

```php
use Livewire\Attributes\Session;

#[Session]
public $perPage = 12;
```

This is extremely useful for preferences that should persist across page reloads (like table filters or sidebar state).
