## Security

Livewire provides several security features.

### Authorizing Action Parameters/Public Properties

Always authorize:

```php
public function delete(int $id)
{
    $post = Post::findOrFail($id);
    $this->authorize('delete', $post);
    $post->delete();
}
```

### Model Properties

Protect model properties:

```php
#[Locked]
public Post $post;
```

### #[Locked] Attribute

Prevent frontend modification:

```php
use Livewire\Attributes\Locked;

#[Locked]
public string $secret = 'my-secret';
```

### Middleware Persistence

Middleware runs on every request:

```php
public function boot()
{
    $this->middleware('auth');
}
```

### Snapshot Checksums

Livewire validates snapshot checksums to prevent tampering.

