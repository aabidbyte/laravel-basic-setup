## Actions

Actions are methods that handle user interactions and can be called from the frontend.

### Basic Usage

Define actions as public methods:

```php
public function save()
{
    // Save logic
}
```

Call from Blade:

```blade
<button wire:click="save">Save</button>
```

### Parameters

Pass parameters to actions:

```blade
<button wire:click="delete({{ $post->id }})">Delete</button>
```

```php
public function delete(int $id)
{
    Post::find($id)->delete();
}
```

### Dependency Injection

Actions support dependency injection:

```php
public function save(PostService $service)
{
    $service->create($this->title, $this->content);
}
```

### Event Listeners

Listen for events using the `#[On]` attribute:

```php
use Livewire\Attributes\On;

#[On('post-created')]
public function updatePostList($title)
{
    session()->flash('status', "New post created: {$title}");
}
```

### Magic Actions

Magic actions are automatically available:

```blade
<button wire:click="$refresh">Refresh</button>
<button wire:click="$set('title', 'New Title')">Set Title</button>
<button wire:click="$toggle('show')">Toggle</button>
<button wire:click="$reset">Reset All</button>
```

### JavaScript Actions

Call actions from JavaScript:

```javascript
$wire.save();
$wire.delete(123);
```

### Skipping Re-renders

Skip component re-rendering after an action:

```blade
<button wire:click.renderless="trackClick">Track</button>
```

Or in PHP:

```php
use Livewire\Attributes\Renderless;

#[Renderless]
public function trackClick()
{
    // Analytics tracking
}
```

### Async Actions

Run actions asynchronously without blocking:

```blade
<button wire:click.async="logActivity">Track</button>
```

Or in PHP:

```php
use Livewire\Attributes\Async;

#[Async]
public function logActivity()
{
    // Non-blocking operation
}
```

### Preserving Scroll

Preserve scroll position during updates:

```blade
<button wire:click.preserve-scroll="loadMore">Load More</button>
```

### Security

Always validate and authorize action parameters:

```php
public function delete(int $id)
{
    $post = Post::findOrFail($id);

    $this->authorize('delete', $post);

    $post->delete();
}
```

