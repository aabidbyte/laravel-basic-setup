# #[Isolate]

The `#[Isolate]` attribute ensures that a component is rendered independently of its parents or children during standard refreshes.

## Basic Usage

```php
use Livewire\Attributes\Isolate;

#[Isolate]
class Sidebar extends Component
{
    // ...
}
```

Isolating a component prevents performance "leakage" where a parent refresh forces expensive children to re-render unnecessarily.
