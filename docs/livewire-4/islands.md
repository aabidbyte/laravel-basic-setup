## Islands

Islands are isolated regions within a component that update independently.

### @island Directive

Create an island:

```blade
@island(name: 'stats')
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Lazy Loading

Load islands lazily:

```blade
@island(name: 'stats', lazy: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Deferred Loading

Defer island loading:

```blade
@island(name: 'stats', defer: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Custom Placeholders

Custom placeholder:

```blade
@island(name: 'stats', lazy: true)
    <x-slot:placeholder>
        <div>Loading stats...</div>
    </x-slot:placeholder>
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Named Islands

Reference islands by name:

```blade
@island(name: 'stats')
    <!-- Content -->
@endisland

<button wire:click="refresh" wire:island="stats">Refresh</button>
```

### Append/Prepend Modes

Append or prepend content:

```blade
<button wire:click="loadMore" wire:island="stats" wire:append>Load More</button>
```

### Nested Islands

Nest islands:

```blade
@island(name: 'parent')
    @island(name: 'child')
        <!-- Content -->
    @endisland
@endisland
```

### Always Render

Always render island:

```blade
@island(name: 'stats', always: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Skip Initial Render

Skip initial render:

```blade
@island(name: 'stats', skip: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Polling

Poll islands:

```blade
@island(name: 'stats', poll: '5s')
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Data/Loop/Conditional Scope

Use islands in loops:

```blade
@foreach ($posts as $post)
    @island(name: "post-{$post->id}")
        <div>{{ $post->title }}</div>
    @endisland
@endforeach
```

