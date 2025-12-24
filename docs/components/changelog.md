## Changelog

### 2025-01-XX

-   **Table Actions Component**: Fixed row actions dropdown not showing
    -   **Bug Fix**: Added missing actions column rendering in table body rows
    -   **Implementation**: Updated `<x-table.actions>` to render as dropdown menu with `ellipsis-vertical` icon
    -   **Icon**: Uses `DataTableUi::ICON_THREE_DOTS` constant (`ellipsis-vertical`) for dropdown trigger
    -   **Event Handling**: Added `wire:click.stop` to prevent row click events when interacting with actions
    -   **Styling**: Delete actions styled with error color and trigger confirmation modals
    -   **Documentation**: Updated documentation to reflect dropdown implementation details

### 2025-01-XX

-   **Table System**: Created comprehensive Blade-first data table system
    -   **New Components**: Created 9 table components (`<x-table>`, `<x-table.header>`, `<x-table.body>`, `<x-table.row>`, `<x-table.cell>`, `<x-table.actions>`, `<x-table.bulk>`, `<x-table.pagination>`, `<x-table.empty>`)
    -   **Architecture**: Strict separation of concerns - Livewire handles data/state, Blade handles rendering
    -   **Features**: Sortable columns, search, pagination, row actions, bulk actions, row click, empty state
    -   **Users Table**: Implemented `UsersTable` Livewire component with full CRUD capabilities
    -   **Authorization**: Protected by `Permissions::VIEW_USERS` permission
    -   **Documentation**: Added comprehensive table system documentation with usage examples
    -   **Tests**: Added authorization tests and core table behavior tests

### 2025-01-XX

-   **Confirm Modal Component:** Created reusable confirmation modal component
    -   **New Component**: Created `<x-ui.confirm-modal>` component for consistent confirmation dialogs
    -   **Alpine.js Integration**: Uses Alpine.js events (`confirm-modal`) for triggering confirmations
    -   **Livewire Support**: Supports Livewire actions via callback functions
    -   **Customizable**: Supports custom titles, messages, and button labels
    -   **Global Usage**: Included in app layout for global availability
    -   **Translation Support**: Uses translation keys with override capability
    -   **Migration**: Replaced browser `confirm()` dialogs in notification center with modal component
    -   **Documentation**: Added comprehensive documentation with usage examples

### 2025-01-XX

-   **Badge Component:** Created centralized badge component for consistent badge functionality
    -   **New Component**: Created `<x-ui.badge>` component with support for all DaisyUI badge variants
    -   **Style Support**: Supports outline, dash, soft, and ghost styles
    -   **Color Support**: Supports all DaisyUI colors (neutral, primary, secondary, accent, info, success, warning, error)
    -   **Size Support**: Supports all DaisyUI sizes (xs, sm, md, lg, xl)
    -   **Migration**: Migrated two-factor settings page and navigation items to use new badge component
    -   **Flexibility**: Supports empty badges for dot indicators and custom classes
    -   **Documentation**: Added comprehensive documentation with usage examples

### 2025-12-19

-   **Dropdown Component:** Enhanced dropdown component with Alpine.js class binding support
    -   **Alpine.js Integration**: Added support for `x-bind:class` to enable reactive class management
    -   **Class Merging**: Updated component to use `$attributes->merge(['class' => $dropdownClasses, 'id' => $dropdownId])` to properly merge static classes with Alpine-bound classes
    -   **State Management**: Enables use cases like conditionally applying `dropdown-open` class based on Alpine state
    -   **Usage Example**: Notification dropdown uses this feature to maintain open state during Livewire updates via `x-bind:class="{ 'dropdown-open': isOpen }"`

### 2025-01-XX

-   **Dropdown Component:** Created centralized dropdown component for consistent dropdown functionality
    -   **New Component**: Created `<x-ui.dropdown>` component with CSS focus pattern for better accessibility
    -   **Placement Support**: Supports all DaisyUI placements (start, center, end, top, bottom, left, right)
    -   **Menu Support**: Optional menu styling with size variants (xs, sm, md, lg, xl)
    -   **Hover Support**: Optional hover-to-open behavior
    -   **Migration**: Migrated locale-switcher and user menu to use new dropdown component
    -   **Accessibility**: Built-in ARIA attributes and keyboard navigation support
    -   **Documentation**: Added comprehensive documentation with usage examples

### 2025-01-XX

-   **Icon Component Refactoring:** Converted icon component from static SVG wrapper to dynamic Blade Icons component
    -   **Converted to Dynamic Component**: Changed from static SVG wrapper to dynamic icon component using Blade Icons
    -   **Multiple Icon Pack Support**: Added support for heroicons, fontawesome, bootstrap, and feather icon packs
    -   **Security Enhancements**: Implemented comprehensive input validation and sanitization for icon names, pack names, and CSS classes
    -   **Fallback Handling**: Automatically falls back to question mark icon if requested icon doesn't exist
    -   **Performance**: Uses `@inject` directive for dependency injection (no Livewire overhead)
    -   **Updated Usage**: All references changed from static SVG slots to dynamic icon names (e.g., `<x-ui.icon name="home" />`)
    -   **Size Support**: Maintains backward compatibility with predefined sizes (xs, sm, md, lg, xl) and custom Tailwind classes

### 2025-01-XX

-   **Component Centralization:** Migrated all UI components to use centralized components
    -   **Modal:** All modals now use `<x-ui.base-modal>` component with Alpine.js state management
    -   **Button:** All buttons now use `<x-ui.button>` component
    -   **Input:** All inputs now use `<x-ui.input>` component (with `label-append` slot support)
    -   **Form:** All forms now use `<x-ui.form>` component with automatic CSRF and method spoofing
    -   **Icon:** Created `<x-ui.icon>` component for consistent SVG icon rendering
    -   Updated all authentication forms (login, register, password reset, etc.)
    -   Updated all settings forms (profile, password, two-factor)
    -   Updated layout components (header, sidebar)
    -   Added comprehensive documentation for all components

---

**Last Updated:** 2025-12-19
