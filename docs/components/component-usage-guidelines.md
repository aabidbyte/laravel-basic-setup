## Component Usage Guidelines

### When to Use Centralized Components

1. **Consistency:** Always use centralized components for UI elements that appear multiple times
2. **Maintainability:** Changes to styling or behavior can be made in one place
3. **Accessibility:** Centralized components ensure consistent accessibility features
4. **Documentation:** Centralized components are documented and follow best practices

### Creating New Components

When creating a new reusable UI component:

1. Place it in `resources/views/components/ui/`
2. Use the `@props` directive for component properties
3. Follow DaisyUI patterns and classes
4. Support Livewire attributes (`wire:model`, `wire:click`, etc.)
5. Add comprehensive documentation to this file
6. Include usage examples

### Component Naming

-   Use kebab-case for component files: `button.blade.php`, `base-modal.blade.php`
-   Use dot notation for component names: `<x-ui.button>`, `<x-ui.base-modal>`
-   Use descriptive, semantic names that indicate purpose

### Best Practices

1. **Props:** Always provide sensible defaults for optional props
2. **Slots:** Use named slots for distinct content areas (e.g., `actions`, `header`, `footer`)
3. **Attributes:** Use Laravel's `$attributes` merging to allow additional HTML attributes
4. **Error Handling:** Include error states and validation feedback where applicable
5. **Accessibility:** Ensure components are keyboard navigable and screen-reader friendly
6. **Responsive:** Make components responsive using Tailwind's responsive utilities

### Migration from Custom Implementations

When migrating existing custom implementations to centralized components:

1. Identify the component pattern being used
2. Check if a centralized component exists
3. If not, create one following the guidelines above
4. Update all usages to use the centralized component
5. Test thoroughly to ensure functionality is preserved
6. Update this documentation

---

