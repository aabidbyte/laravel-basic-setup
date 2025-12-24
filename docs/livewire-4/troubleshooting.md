## Troubleshooting

Common issues and solutions.

### Component Mismatches

**Problem:** Component HTML doesn't match expected structure.

**Solution:** Ensure single root element and proper `wire:key` usage.

### wire:key

**Problem:** Components not updating correctly in loops.

**Solution:** Always use `wire:key` in loops:

```blade
@foreach ($items as $item)
    <div wire:key="item-{{ $item->id }}">{{ $item->name }}</div>
@endforeach
```

### Duplicate Keys

**Problem:** Duplicate `wire:key` values.

**Solution:** Ensure unique keys:

```blade
<div wire:key="post-{{ $post->id }}-{{ $post->updated_at }}">
```

### Multiple Alpine Instances

**Problem:** Multiple Alpine instances conflicting.

**Solution:** Livewire includes Alpine. Don't include it separately.

### Missing @alpinejs/ui

**Problem:** Alpine UI plugins not working.

**Solution:** Include Alpine UI if needed:

```javascript
import Alpine from "alpinejs";
import ui from "@alpinejs/ui";
Alpine.plugin(ui);
```

