# #[On]

The `#[On]` attribute is used to register event listeners on a Livewire component.

## Basic Usage

```php
use Livewire\Attributes\On;

#[On('post-created')]
public function updateList($postId)
{
    // ...
}
```

## Scoped Listeners

You can scope listeners to specific components or use the `to()` method in the dispatching component.
