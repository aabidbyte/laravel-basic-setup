## Best Practices

1. **Single root element**: Components must have exactly one root HTML element
2. **Use wire:loading**: Add loading states for better UX
3. **Use wire:key**: Always add `wire:key` in loops
4. **Use wire:model.live**: For real-time updates (deferred by default in v3, but can be made live)
5. **Prefer lifecycle hooks**: Use `mount()`, `updatedFoo()` for initialization and reactive side effects
6. **Validate form data**: Always validate form data in Livewire actions
7. **Run authorization checks**: Always run authorization checks in Livewire actions
8. **Avoid `@php` directives**: All PHP logic should be included in the Livewire component class. Use computed properties, methods, or properties instead of `@php` blocks in Blade templates. This keeps logic centralized in the component class and improves maintainability.
9. **Avoid conditional wrapper patterns with duplicated content**: When you have conditional wrapper elements (e.g., `<a>` vs `<div>`) that wrap the same content, extract the repeated content into a separate Blade component and call it twice—once inside each wrapper. This improves readability and maintainability. Example: Instead of duplicating content inside `@if ($hasLink) <a>...</a> @else <div>...</div> @endif`, create a component like `<x-notifications.notification-item />` and use it in both branches.

