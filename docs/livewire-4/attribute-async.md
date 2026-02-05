# #[Async]

The `#[Async]` attribute allows an action to be executed asynchronously, meaning Livewire won't wait for the server response before continuing its lifecycle.

## Basic Usage

```php
use Livewire\Attributes\Async;

#[Async]
public function sendNotification()
{
    // Long-running task
}
```

This is useful for secondary tasks (like analytics or background notifications) that shouldn't block the UI.
