## Properties

Properties store component state and can be accessed in both PHP and JavaScript.

### Initializing Properties

Properties can be initialized with default values:

```php
public string $title = '';
public int $count = 0;
public array $items = [];
public ?User $user = null;
```

### Bulk Assignment

Use `fill()` for bulk assignment:

```php
$this->fill([
    'title' => 'New Title',
    'content' => 'New Content',
]);
```

Or use `fillOnly()` / `fillExcept()`:

```php
$this->fillOnly(['title', 'content'], $request->all());
$this->fillExcept(['password'], $request->all());
```

### Data Binding

Bind properties to form inputs using `wire:model`:

```blade
<input type="text" wire:model="title">
<textarea wire:model="content"></textarea>
<select wire:model="category">
    <option value="">Select...</option>
</select>
```

**Modifiers:**

-   `wire:model.live` - Updates in real-time (default in v4)
-   `wire:model.defer` - Updates on blur/change
-   `wire:model.lazy` - Updates on blur
-   `wire:model.debounce.300ms` - Debounces updates

### Resetting Properties

Reset properties to their initial values:

```php
$this->reset('title', 'content');
$this->reset(['title', 'content']);
$this->reset(); // Reset all properties
```

### Pulling Properties

Pull properties from the component's state:

```php
$title = $this->pull('title');
```

### Supported Types

Livewire supports many PHP types:

-   **Primitives**: `string`, `int`, `float`, `bool`, `array`
-   **Objects**: Eloquent models, DTOs, collections
-   **Wireables**: Classes implementing `Wireable` interface
-   **Synthesizers**: Custom property types

### Wireables

Wireables allow custom objects to be stored as properties:

```php
use Livewire\Wireable;

class Address implements Wireable
{
    public function __construct(
        public string $street,
        public string $city,
    ) {}

    public function toLivewire(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
        ];
    }

    public static function fromLivewire($value): static
    {
        return new static(
            $value['street'],
            $value['city']
        );
    }
}
```

Use in component:

```php
public Address $address;
```

### Synthesizers

Synthesizers handle custom property types automatically:

```php
use Livewire\Mechanisms\HandleProperties\Synthesizers\Synthesizer;

class AddressSynthesizer extends Synthesizer
{
    public static $key = 'address';

    public static function match($target, $value): bool
    {
        return $value instanceof Address;
    }

    public static function dehydrate($target, $value, $context): array
    {
        return [
            'street' => $value->street,
            'city' => $value->city,
        ];
    }

    public static function hydrate($value, $target, $context): Address
    {
        return new Address($value['street'], $value['city']);
    }
}
```

Register in service provider:

```php
Livewire::propertySynthesizer(AddressSynthesizer::class);
```

### Accessing from JavaScript

Access properties from JavaScript using `$wire`:

```javascript
$wire.title = "New Title";
let title = $wire.title;
```

### Security Concerns

**Public properties are exposed to the frontend.** Never store sensitive data in public properties:

```php
// ❌ Bad
public string $password = '';

// ✅ Good
#[Locked]
public string $apiKey = '';
```

Use the `#[Locked]` attribute to prevent frontend modification:

```php
use Livewire\Attributes\Locked;

#[Locked]
public string $secret = 'my-secret';
```

### Computed Properties

Computed properties are calculated on-demand:

```php
use Livewire\Attributes\Computed;

#[Computed]
public function fullName(): string
{
    return "{$this->firstName} {$this->lastName}";
}
```

Access in Blade:

```blade
{{ $this->fullName }}
```

### Session Properties

Store properties in the session:

```php
use Livewire\Attributes\Session;

#[Session]
public string $search = '';
```

### URL Query Parameters

Sync properties with URL query parameters:

```php
use Livewire\Attributes\Url;

#[Url]
public string $search = '';

#[Url(as: 'q')]
public string $query = '';
```

