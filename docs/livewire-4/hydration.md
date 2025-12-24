## Hydration

Hydration is the process of restoring component state from storage.

### Dehydrating HTML/JSON Snapshot

Livewire dehydrates component state into HTML and JSON:

```html
<div wire:id="abc123">
    <!-- HTML -->
</div>

<script>
    window.Livewire.find("abc123").__instance = {
        /* JSON snapshot */
    };
</script>
```

### Hydrating

On subsequent requests, Livewire hydrates the component from the snapshot.

### Advanced Hydration with Tuples/Metadata

Store additional metadata:

```php
public function dehydrate(): array
{
    return [
        'data' => $this->data,
        'metadata' => $this->metadata,
    ];
}
```

### Custom Property Types with Synthesizers

Use synthesizers for custom types (see [Synthesizers](#synthesizers) section).

