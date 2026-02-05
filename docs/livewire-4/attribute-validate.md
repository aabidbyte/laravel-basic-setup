# #[Validate]

The `#[Validate]` attribute is the preferred way to define validation rules in Livewire 4.

## Basic Usage

```php
use Livewire\Attributes\Validate;

#[Validate('required|min:3')]
public $title = '';

#[Validate('required|email')]
public $email = '';
```

Validation is automatically triggered when data is synced from the client or upon form submission.
