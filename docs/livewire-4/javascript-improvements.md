## JavaScript Improvements

### $errors Magic Property

Access your component's error bag from JavaScript:

```blade
<div wire:show="$errors.has('email')">
    <span wire:text="$errors.first('email')"></span>
</div>
```

### $intercept Magic

Intercept and modify Livewire requests from JavaScript:

```blade
<script>
this.$intercept('save', ({ ... }) => {
    // ...
})
</script>
```

### Island Targeting from JavaScript

Trigger island renders directly from the template:

```blade
<button wire:click.append="loadMore" wire:island="stats">
    Load more
</button>
```

