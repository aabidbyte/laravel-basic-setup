# #[Locked]

The `#[Locked]` attribute prevents a public property from being modified by the user on the client side.

## Basic Usage

```php
use Livewire\Attributes\Locked;

#[Locked]
public $userId;
```

If a user attempts to change this property via JavaScript or element inspection, Livewire will throw a security exception. This is critical for IDs or sensitive data that should be bound but not user-editable.
