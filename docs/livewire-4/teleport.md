## Teleport

Teleport allows you to render component content in a different part of the DOM.

### @teleport Directive

Teleport content:

```blade
@teleport('#modal-container')
    <div class="modal">
        <!-- Modal content -->
    </div>
@endteleport
```

### Basic Usage

```blade
<div id="modal-root"></div>

@teleport('#modal-root')
    <div class="modal">Content</div>
@endteleport
```

### Why Use

-   Render modals at root level
-   Avoid z-index issues
-   Better accessibility

### Common Use Cases

-   Modals
-   Dropdowns
-   Tooltips
-   Notifications

### Constraints

-   Target must exist in DOM
-   Only one target per teleport
-   Content is moved, not copied

### Alpine Integration

Use with Alpine:

```blade
<div x-data="{ open: false }">
    <button @click="open = true">Open</button>

    @teleport('body')
        <div x-show="open" class="modal">
            Content
        </div>
    @endteleport
</div>
```

