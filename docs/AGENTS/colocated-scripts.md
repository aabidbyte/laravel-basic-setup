# Colocated Scripts Pattern

## Context
See: [Livewire Assets Documentation](https://livewire.laravel.com/docs/assets)

To maintain a clean and modular codebase, we prefer **colocating** JavaScript logic with the Blade component that requires it, rather than splitting it into global `.js` files. This ensures that:
1.  Code is easier to maintain (everything is in one file).
2.  Scripts are loaded **on-demand** (only if the component is used).
3.  Scripts are **deduplicated** (loaded only once per page, even if multiple component instances exist).

## The Pattern
Use the `@assets` directive provided by Livewire 4.

### Implementation Guide
1.  Place your script at the bottom of your Blade component file.
2.  Wrap the `<script>` tag in `@assets` ... `@endassets`.
3.  Wrap your Alpine component registration in a self-executing function that checks if Alpine is already initialized.

### Example: `share-button.blade.php`

```blade
<!-- Component HTML -->
<div x-data="shareButton(...)">
    <!-- ... -->
</div>

<!-- Colocated Script -->
@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('shareButton', (config) => ({
                    // Component Logic
                    init() {
                        console.log('Share button initialized');
                    }
                }));
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
```

## Why this works
- **@assets**: Livewire ensures the content inside this directive is injected into the `<head>` (or where scripts are yielded) **exactly once** per request, based on the content hash.
- **Lazy Loading**: If the component is lazy-loaded (e.g., in a modal or via `wire:navigate`), Livewire will inject the asset.
- **Registration Check**: The `if (window.Alpine)` check ensures the logic runs even if Alpine has already initialized (which is common during lazy loading actions).
