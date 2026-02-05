# wire:sort

Livewire 4 includes built-in drag-and-drop sorting support.

## Basic Usage

```blade
<ul wire:sort="updateOrder">
    @foreach ($items as $item)
        <li wire:sort:item="{{ $item->id }}" wire:key="{{ $item->id }}">
            {{ $item->name }}
        </li>
    @endforeach
</ul>
```

## Backend Implementation

```php
public function updateOrder($items)
{
    // $items is an array of IDs in the new order
    foreach ($items as $index => $id) {
        Item::find($id)->update(['order' => $index]);
    }
}
```

## Modifiers

-   `.handle`: Specify a handle element for dragging.
-   `.ghost-class`: Custom CSS class for the ghost element during drag.
