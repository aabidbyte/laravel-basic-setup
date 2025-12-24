## Nesting Components

Components can be nested to create complex UIs.

### Independent Nature

Nested components are independent by default:

```blade
<livewire:post-list />
<livewire:post-form />
```

### Passing Props

Pass props to nested components:

```blade
<livewire:post-item :post="$post" />
```

### Loops

Use components in loops:

```blade
@foreach ($posts as $post)
    <livewire:post-item :post="$post" wire:key="post-{{ $post->id }}" />
@endforeach
```

### Reactive Props

Props are reactive by default:

```blade
<livewire:post-item :post="$post" />
```

When `$post` changes, the component updates automatically.

### wire:model Binding

Bind `wire:model` across component boundaries:

```blade
<livewire:post-form />
```

```php
// In post-form component
public string $title = '';
```

```blade
<!-- In parent -->
<input wire:model="title">
```

### Slots

Pass content to components using slots:

```blade
<livewire:modal>
    <x-slot:title>Delete Post</x-slot:title>
    <p>Are you sure?</p>
</livewire:modal>
```

### HTML Attributes

Pass HTML attributes:

```blade
<livewire:button class="btn-primary" data-id="123">
    Click Me
</livewire:button>
```

### Islands vs. Nested Components

Islands are isolated regions that update independently:

```blade
@island(name: 'stats')
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

Nested components are full components with their own lifecycle.

### Event Communication

Components communicate via events:

```php
// Child component
$this->dispatch('post-created', id: $this->postId);

// Parent component
#[On('post-created')]
public function refreshList($id)
{
    // Handle event
}
```

### Direct Parent Access

Access parent component:

```php
$parent = $this->getParent();
```

### Dynamic/Recursive Components

Create dynamic components:

```blade
<livewire:dynamic-component :component="$componentName" :props="$props" />
```

### Forcing Re-render

Force a component to re-render:

```php
$this->dispatch('$refresh');
```

