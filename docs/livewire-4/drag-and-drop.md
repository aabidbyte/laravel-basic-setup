# Drag and Drop

Livewire 4 introduces native drag-and-drop sorting support without requiring external libraries.

## Basic Usage

To enable sorting on a list, add the `wire:sort` directive to the container and `wire:sort:item` to each item.

```blade
<ul wire:sort="reorder">
    @foreach ($items as $item)
        <li wire:key="{{ $item->id }}" wire:sort:item="{{ $item->id }}">
            {{ $item->title }}
        </li>
    @endforeach
</ul>
```

In your component, define the `reorder` method:

```php
public function reorder($item, $position)
{
    // $item is the ID of the moved item
    // $position is the new index (0-based)
    
    // Logic to update item order in database
}
```

## Drag Handles

To restrict dragging to a specific handle element within the item, use `wire:sort:handle`:

```blade
<li wire:sort:item="{{ $item->id }}">
    <span wire:sort:handle>☰</span> <!-- Dragging only works here -->
    {{ $item->title }}
</li>
```

## Ignoring Elements

To prevent specific elements inside an item from triggering a drag (like buttons or inputs), use `wire:sort:ignore`:

```blade
<li wire:sort:item="{{ $item->id }}">
    {{ $item->title }}
    <button wire:sort:ignore wire:click="delete({{ $item->id }})">Delete</button>
</li>
```

## Group Sorting

To allow dragging items between multiple lists, use `wire:sort:group` with a matching group name on all containers:

```blade
<ul wire:sort="reorderTodo" wire:sort:group="tasks">
    <!-- Todo items -->
</ul>

<ul wire:sort="reorderDone" wire:sort:group="tasks">
    <!-- Done items -->
</ul>
```

## Animations

Livewire handles smooth animations automatically for reordering items.
