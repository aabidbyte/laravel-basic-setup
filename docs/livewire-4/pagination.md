## Pagination

Paginate data in Livewire components.

### WithPagination Trait

Use the trait:

```php
use Livewire\WithPagination;
use Livewire\Component;

class PostList extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.post-list', [
            'posts' => Post::paginate(10),
        ]);
    }
}
```

### Basic Usage

Display pagination links:

```blade
<div>
    @foreach ($posts as $post)
        <div>{{ $post->title }}</div>
    @endforeach

    {{ $posts->links() }}
</div>
```

### URL Query String Tracking

Track page in URL:

```php
use Livewire\WithPagination;

class PostList extends Component
{
    use WithPagination;

    protected $paginationQueryString = ['page'];
}
```

### Scroll Behavior

Reset scroll on page change:

```blade
<div wire:poll.5s>
    {{ $posts->links() }}
</div>
```

### Resetting Page

Reset page when filters change:

```php
public function updatedSearch()
{
    $this->resetPage();
}
```

### Multiple Paginators

Use multiple paginators:

```php
public function render()
{
    return view('livewire.dashboard', [
        'posts' => Post::paginate(10, ['*'], 'postsPage'),
        'users' => User::paginate(10, ['*'], 'usersPage'),
    ]);
}
```

### Hooks

Use pagination hooks:

```php
public function updatingPage($page)
{
    // Before page changes
}
```

### Simple/Cursor Pagination

Use simple pagination:

```php
$posts = Post::simplePaginate(10);
```

Or cursor pagination:

```php
$posts = Post::cursorPaginate(10);
```

### Bootstrap/Tailwind Themes

Use different themes:

```blade
{{ $posts->links('pagination::bootstrap-4') }}
{{ $posts->links('pagination::tailwind') }}
```

### Custom Views

Create custom pagination view:

```blade
{{ $posts->links('livewire.custom-pagination') }}
```

