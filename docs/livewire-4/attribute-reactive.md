# #[Reactive]

The `#[Reactive]` attribute allows a child component's property to automatically update when the parent passes a new value.

## Basic Usage

```php
// Child Component
use Livewire\Attributes\Reactive;

#[Reactive]
public $activeTab;
```

Without `#[Reactive]`, properties passed from parents are only set during the initial `mount()` and won't reflect subsequent changes in the parent state.
