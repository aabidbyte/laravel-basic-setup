# Sidebar Menu Rules
 
 This document outlines the rules and conventions for organizing the sidebar menu in this application.
 
 ## Core Principles
 
 The sidebar is divided into two main sections to separate business functionalities from application administration and development tools.
 
 ### 1. Top Section (Business Features)
 
 This section is reserved for the core business value of the application ("métier").
 
 -   **Dashboard**: Always at the top.
 -   **Business Resources**: Any resource that is part of the application's core purpose (e.g., Projects, Tasks, Products).
 -   **Group Title**: `navigation.platform`.
 
 ### 2. Bottom Section (Administration & Preferences)
 
 This section is for managing the application itself and accessed primarily by admins or for utility purposes.
 
 -   **Group Title**: `navigation.administration`.
 -   **Collapsible Groups**: All items in this section MUST be grouped into collapsible categories.
 
 #### Management Group
 
 Contains items related to organizational and access management.
 -   **Users**: User management.
 -   **Roles & Permissions**: Access control management (reserved).
 -   **Teams**: Multi-tenancy or team management (reserved).
 
 #### Developer Tools Group
 
 Contains tools for monitoring and debugging.
 -   **Environment Restriction**: This group MUST ONLY be visible in `local` or `development` environments.
 -   **Telescope**: Request monitoring.
 -   **Horizon**: Queue monitoring.
 -   **Log Viewer**: Third-party log viewer package.
 -   **Error Handler**: Custom error handling system.
 
 ## Implementation Rules
 
 ### Service Layer
 
 -   Always use `App\Services\SideBarMenuService`.
 -   Use `NavigationBuilder` and `NavigationItem` classes for menu definitions.
 -   The logic for visibility (RBAC) must be handled in the service layer using `->show()`.
 
 ### Collapsible Groups
 
 -   Use the `items()` method on a `NavigationItem` to create nested structures.
 -   Nested items are automatically rendered as collapsible menus via `<details>` and `<summary>`.
 
 ### Icons
 
 -   Each top-level item and each group MUST have an icon.
 -   Use icons from the default pack (Heroicons).
 
 ### Active State
 
 -   Top-level items should use `->route()` for automatic active state.
 -   Nested items should use `->activeRoutes()` with wildcards (e.g., `users.*`) to maintain active state across sub-pages.
 
 ## Example Component
 
 ```php
 NavigationItem::make()
     ->title(__('navigation.management'))
     ->icon('cog')
     ->items(
         NavigationItem::make()
             ->title(__('navigation.users'))
             ->route('users.index')
             ->activeRoutes('users.*')
             ->show(Auth::user()?->can(Permissions::VIEW_USERS) ?? false),
     )
 ```

## CSS Styling Conventions

All collapsible navigation styles are in `resources/css/sidebar.css`.

**Key Classes:**
- `.nav-details` - Full-width `<details>` container
- `.nav-summary` - Summary row with hover/active states
- `.nav-chevron` - Chevron icon that rotates 180° when open
- `.nav-nested-items` - Nested items container with left border indentation

**CSS Rules:**
- Always use `@apply` for Tailwind classes
- Chevron rotation uses `[open]` selector: `.nav-details[open] > .nav-summary .nav-chevron { @apply rotate-180; }`
- Avoid inline Tailwind classes in Blade for styling that belongs in sidebar.css
