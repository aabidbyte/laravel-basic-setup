## Events

Events allow components to communicate with each other.

### Dispatching Events

Dispatch events from components:

```php
use Livewire\Attributes\On;

public function save()
{
    // Save logic

    $this->dispatch('post-created', title: $this->title);
}
```

Or use the `$dispatch()` method:

```php
$this->dispatch('post-created', title: $this->title);
```

### Listening for Events

Listen for events using `#[On]`:

```php
#[On('post-created')]
public function updatePostList($title)
{
    $this->posts = Post::latest()->get();
}
```

### Dynamic Event Names

Use dynamic event names:

```php
$this->dispatch("post-{$action}-completed", id: $this->postId);
```

### Child Component Events

Listen for events from child components:

```blade
<livewire:create-post />
```

```php
#[On('post-created')]
public function refreshList()
{
    $this->posts = Post::latest()->get();
}
```

### JavaScript Interaction

Dispatch events from JavaScript:

```javascript
$wire.dispatch("post-created", { title: "New Post" });
```

Listen in JavaScript:

```javascript
Livewire.on("post-created", (data) => {
    console.log("Post created:", data);
});
```

### Alpine Events

Dispatch Alpine events:

```blade
<div x-data @post-created.window="handlePostCreated()">
    <!-- Content -->
</div>
```

### Direct Dispatching

Dispatch directly to a component:

```php
$this->dispatch('post-created')->to('post-list');
```

### Testing Events

Test events in tests:

```php
Livewire::test(CreatePost::class)
    ->call('save')
    ->assertDispatched('post-created');
```

### Laravel Echo Integration

Integrate with Laravel Echo for real-time events:

```php
use Livewire\Attributes\On;

#[On('echo:channel-name,EventName')]
public function handleBroadcast($event)
{
    // Handle broadcast event
}
```

