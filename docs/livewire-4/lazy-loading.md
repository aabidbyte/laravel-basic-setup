## Lazy Loading

Lazy loading defers component rendering until needed.

### lazy vs defer

-   **lazy**: Loads when component enters viewport
-   **defer**: Loads after page load

```blade
<livewire:revenue lazy />
<livewire:expenses defer />
```

### Basic Usage

```blade
<livewire:revenue lazy />
```

Or in PHP:

```php
use Livewire\Attributes\Lazy;

#[Lazy]
class Revenue extends Component
{
    // Component code
}
```

### Placeholder HTML

Custom placeholder:

```blade
<livewire:revenue lazy>
    <x-slot:placeholder>
        <div>Loading revenue...</div>
    </x-slot:placeholder>
</livewire:revenue>
```

### Immediate Loading

Load immediately:

```blade
<livewire:revenue lazy.immediate />
```

### Props

Pass props to lazy components:

```blade
<livewire:revenue :year="$year" lazy />
```

### Enforcing Defaults

Enforce default props:

```blade
<livewire:revenue :year="2024" lazy />
```

### Bundling

Bundle lazy components:

```blade
<livewire:revenue lazy.bundle />
<livewire:expenses defer.bundle />
```

### Full-Page Loading

Lazy load full pages:

```php
Route::livewire('/dashboard', Dashboard::class)->lazy();
```

### Default Placeholder

Set default placeholder in config:

```php
'component_placeholder' => 'livewire.placeholder',
```

### Disabling for Tests

Disable lazy loading in tests:

```php
Livewire::withoutLazyLoading();
```

