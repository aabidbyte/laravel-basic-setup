# Computed Properties

Computed properties allow you to create derived properties in Livewire components that are cached for the duration of the request.

## Basic Usage

Apply the `#[Computed]` attribute to any method to turn it into a cached property:

```php
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\User;

new class extends Component {
    public $userId;

    #[Computed]
    public function user() {
        return User::find($this->userId);
    }
};
```

The `user()` method can now be accessed like a property using `$this->user`. The first time it's accessed, the result is cached.

```html
<div>
    <h1>{{ $this->user->name }}</h1>
</div>
```

**Note:** Unlike normal properties, computed properties must be accessed via `$this` in your template (e.g., `$this->user` instead of `$user`).

## Performance Advantage

Computed properties cache their result for the request lifecycle. If you access `$this->user` multiple times, the underlying logic (e.g., database query) only executes once.

## Busting the Cache

If the underlying data changes during a request, using `unset($this->propertyName)` clears the cache so the next access retrieves updated data.

```php
unset($this->user);
```

## Caching Strategies

### Persist (Between Requests)
Cache across multiple requests using `persist`:

```php
#[Computed(persist: true)] // Defaults to 3600s
#[Computed(persist: true, seconds: 7200)]
```

### Cache (Across Components)
Share cached values across all component instances using `cache`:

```php
#[Computed(cache: true)]
#[Computed(cache: true, key: 'my-key')]
```
