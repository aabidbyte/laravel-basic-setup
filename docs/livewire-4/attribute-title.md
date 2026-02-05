# #[Title]

The `#[Title]` attribute allows you to set the browser page title directly from a Livewire component.

## Basic Usage

```php
use Livewire\Attributes\Title;

#[Title('User Profile')]
public function render()
{
    // ...
}
```

This updates the `<title>` tag in real-time, even during `wire:navigate` transitions.
