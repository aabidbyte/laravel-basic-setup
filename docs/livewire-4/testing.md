## Testing

Livewire components can be tested using Pest or PHPUnit.

### Pest Testing

Test components with Pest:

```php
use Livewire\Volt\Volt;

test('counter increments', function () {
    Volt::test('counter')
        ->assertSee('Count: 0')
        ->call('increment')
        ->assertSee('Count: 1');
});
```

### Browser Testing

Test with browser:

```php
it('can create a post', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/posts/create');

    $page->fill('title', 'My Post')
         ->fill('content', 'Post content')
         ->click('Save')
         ->assertSee('Post created');
});
```

### Views

Test component views:

```php
Livewire::test(CreatePost::class)
    ->assertSee('Create Post')
    ->assertSee('Title');
```

### Authentication

Test authenticated components:

```php
Livewire::test(CreatePost::class)
    ->actingAs($user)
    ->assertSee('Create Post');
```

### Properties

Test properties:

```php
Livewire::test(CreatePost::class)
    ->assertSet('title', '')
    ->set('title', 'My Post')
    ->assertSet('title', 'My Post');
```

### Actions

Test actions:

```php
Livewire::test(CreatePost::class)
    ->set('title', 'My Post')
    ->set('content', 'Content')
    ->call('save')
    ->assertHasNoErrors();
```

### Validation

Test validation:

```php
Livewire::test(CreatePost::class)
    ->set('title', '')
    ->call('save')
    ->assertHasErrors(['title' => 'required']);
```

### Authorization

Test authorization:

```php
Livewire::test(DeletePost::class, ['post' => $post])
    ->call('delete')
    ->assertForbidden();
```

### Redirects

Test redirects:

```php
Livewire::test(CreatePost::class)
    ->call('save')
    ->assertRedirect('/posts');
```

### Events

Test events:

```php
Livewire::test(CreatePost::class)
    ->call('save')
    ->assertDispatched('post-created');
```

### PHPUnit

Test with PHPUnit:

```php
use Tests\TestCase;
use Livewire\Livewire;

class CreatePostTest extends TestCase
{
    public function test_can_create_post()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'My Post')
            ->call('save')
            ->assertHasNoErrors();
    }
}
```

