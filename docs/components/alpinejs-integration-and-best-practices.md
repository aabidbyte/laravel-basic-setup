## Alpine.js Integration & Best Practices

All components in this application use Alpine.js for interactivity. When working with components that use Alpine.js, follow these guidelines:

### Alpine.js Best Practices

> [!IMPORTANT]
> **CSP Safety Rules Apply:** See [CSP Safety Guide](../AGENTS/csp-safety.md) for strict rules on arrow functions, template literals, and x-html.

1. **Use `Alpine.data()` Pattern**: Components should use `Alpine.data()` for reusable data objects instead of global functions.

2. **Avoid `@entangle` Directive**: In Livewire v3/v4, refrain from using the `@entangle` directive. Use `$wire.$entangle()` instead:
   ```blade
   <!-- ❌ Avoid -->
   <div x-data="{ open: @entangle('isOpen').live }">
   
   <!-- ✅ Preferred -->
   <div x-data="{ open: $wire.$entangle('isOpen') }">
   ```

3. **Prefer Alpine.js Over Plain JavaScript**: Always use Alpine.js directives instead of plain JavaScript:
   ```blade
   <!-- ❌ Avoid -->
   <button onclick="document.getElementById('id').showModal()">
   
   <!-- ✅ Preferred -->
   <button @click="$el.closest('section').querySelector('#id')?.showModal()">
   ```

4. **Use `$el` and `$refs`**: Reference elements using Alpine.js utilities:
   ```blade
   <!-- ✅ Preferred -->
   <button @click="$refs.modal.showModal()">
   <div x-ref="modal">
   ```

5. **Proper Cleanup**: Always implement `destroy()` methods in Alpine components to clean up subscriptions, intervals, and event listeners.

For comprehensive Alpine.js documentation, see [docs/alpinejs/index.md](../alpinejs/index.md).

