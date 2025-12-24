## Key Features

### Islands

Islands allow you to create isolated regions within a component that update independently, dramatically improving performance without creating separate child components.

```blade
@island(name: 'stats', lazy: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Loading Improvements

**Deferred loading:**

```blade
<livewire:revenue defer />
```

```php
#[Defer]
class Revenue extends Component { ... }
```

**Bundled loading:**

```blade
<livewire:revenue lazy.bundle />
<livewire:expenses defer.bundle />
```

```php
#[Lazy(bundle: true)]
class Revenue extends Component { ... }
```

### Async Actions

Run actions in parallel without blocking other requests:

```blade
<button wire:click.async="logActivity">Track</button>
```

```php
#[Async]
public function logActivity() { ... }
```

