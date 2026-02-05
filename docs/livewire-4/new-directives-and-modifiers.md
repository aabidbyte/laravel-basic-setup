## New Directives and Modifiers

### wire:sort - Drag-and-Drop Sorting

Built-in support for sortable lists with drag-and-drop:

```blade
<ul wire:sort="updateOrder">
    @foreach ($items as $item)
        <li wire:sort:item="{{ $item->id }}" wire:key="{{ $item->id }}">{{ $item->name }}</li>
    @endforeach
</ul>
```

### wire:intersect - Viewport Intersection

Run actions when elements enter or leave the viewport:

```blade
<!-- Basic usage -->
<div wire:intersect="loadMore">...</div>

<!-- With modifiers -->
<div wire:intersect.once="trackView">...</div>
<div wire:intersect:leave="pauseVideo">...</div>
<div wire:intersect.half="loadMore">...</div>
<div wire:intersect.full="startAnimation">...</div>

<!-- With options -->
<div wire:intersect.margin.200px="loadMore">...</div>
<div wire:intersect.threshold.50="trackScroll">...</div>
```

Available modifiers:

-   `.once` - Fire only once
-   `.half` - Wait until half is visible
-   `.full` - Wait until fully visible
-   `.threshold.X` - Custom visibility percentage (0-100)
-   `.margin.Xpx` or `.margin.X%` - Intersection margin

### wire:ref - Element References

Easily reference and interact with elements in your template:

```blade
<div wire:ref="modal">
    <!-- Modal content -->
</div>

<button wire:click="$js.scrollToModal">Scroll to modal</button>

<script>
    this.$js.scrollToModal = () => {
        this.$refs.modal.scrollIntoView()
    }
</script>
```

### .renderless Modifier

Skip component re-rendering directly from the template:

```blade
<button wire:click.renderless="trackClick">Track</button>
```

### .preserve-scroll Modifier

Preserve scroll position during updates to prevent layout jumps:

```blade
<button wire:click.preserve-scroll="loadMore">Load More</button>
```

### wire:show

Toggles element visibility instantly on the client side using CSS `display: none`:

```blade
<div wire:show="isOpen">
    <!-- Toggles instantly when isOpen changes -->
</div>
```

### wire:text

Updates the text content of an element instantly on the client side:

```blade
Likes: <span wire:text="likes"></span>
```

### wire:bind

Bind any HTML attribute reactively on the client side:

```blade
<div wire:bind:class="count > 10 ? 'text-red-500' : 'text-green-500'">
    Count: {{ $count }}
</div>
```

### data-loading Attribute

Every element that triggers a network request automatically receives a `data-loading` attribute, making it easy to style loading states with Tailwind:

```blade
<button wire:click="save" class="data-loading:opacity-50 data-loading:pointer-events-none">
    Save Changes
</button>
```

