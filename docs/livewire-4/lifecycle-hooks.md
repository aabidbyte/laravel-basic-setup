## Lifecycle Hooks

Lifecycle hooks allow you to run code at specific points in a component's lifecycle.

### Mount

The `mount()` method runs when a component is first created:

```php
public function mount(User $user)
{
    $this->user = $user;
    $this->loadPosts();
}
```

### Boot

The `boot()` method runs on every request:

```php
public function boot()
{
    // Runs on every request
}
```

### Update

The `updated()` method runs when a property is updated:

```php
public function updated($propertyName)
{
    if ($propertyName === 'search') {
        $this->resetPage();
    }
}
```

Or for specific properties:

```php
public function updatedSearch()
{
    $this->resetPage();
}
```

### Hydrate

The `hydrate()` method runs when a component is hydrated from storage:

```php
public function hydrate()
{
    // Runs when component is hydrated
}
```

### Dehydrate

The `dehydrate()` method runs when a component is dehydrated for storage:

```php
public function dehydrate()
{
    // Runs when component is dehydrated
}
```

### Render

The `render()` method is called to render the component:

```php
public function render()
{
    return view('livewire.posts.index', [
        'posts' => Post::latest()->get(),
    ]);
}
```

### Exception

The `exception()` method handles exceptions:

```php
public function exception($exception, $stopPropagation = false)
{
    // Handle exception
}
```

### Trait Hooks

Traits can define lifecycle hooks:

```php
trait HasPosts
{
    public function bootHasPosts()
    {
        // Runs when component boots
    }
}
```

### Form Object Hooks

Form objects can have lifecycle hooks:

```php
class PostForm extends Form
{
    public function mount(Post $post)
    {
        $this->title = $post->title;
        $this->content = $post->content;
    }
}
```

