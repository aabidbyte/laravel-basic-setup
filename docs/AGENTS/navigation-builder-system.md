## Navigation Builder System

The project includes a fluent navigation builder system for dynamically generating menus and sidebars.

### Architecture

```
app/Services/
├── SideBarMenuService.php              # Main menu service with getTopMenus(), getBottomMenus(), getUserMenus()
└── Navigation/
    ├── NavigationBuilder.php           # Fluent builder for menu groups/sections
    └── NavigationItem.php              # Fluent builder for individual menu items
```

### Key Classes

**NavigationItem** (`app/Services/Navigation/NavigationItem.php`):

-   Fluent builder for individual menu items
-   Supports: title, URL/route, icons, badges, nested items, conditional visibility, external links, HTML attributes
-   Methods: `make()`, `title()`, `url()`, `route()`, `icon()`, `show()`, `external()`, `items()`, `badge()`, `active()`, `attributes()`
-   **Note**: Form and button support have been removed. Use static forms in Blade templates for actions like logout.
-   **Attributes**: Returns attributes as an array (not a string) for use with `$attributes->merge()` in Blade components
-   **Icons**: Accepts icon component names (e.g., 'home', 'user', 'settings') which are rendered using the `<x-ui.icon>` Blade component. Icons support multiple icon packs (heroicons, fontawesome, bootstrap, feather) and include security validation.

**NavigationBuilder** (`app/Services/Navigation/NavigationBuilder.php`):

-   Fluent builder for menu groups/sections
-   Contains multiple NavigationItem instances
-   Methods: `make()`, `title()`, `items()`, `icon()`, `show()`

**SideBarMenuService** (`app/Services/SideBarMenuService.php`):

-   Centralized service for defining navigation menus
-   Three methods:
    -   `getTopMenus()`: Returns array of NavigationBuilder for top section
    -   `getBottomMenus()`: Returns array of NavigationBuilder for bottom section
    -   `getUserMenus()`: Returns array of NavigationBuilder for user dropdown
-   **Note**: Logout is handled as a static form in the sidebar components, not through NavigationItem

### Usage Example

```php
// In SideBarMenuService.php
use Illuminate\Support\Facades\Auth;

public function getTopMenus(): array
{
    return [
        NavigationBuilder::make()
            ->title('Platform')
            ->items(
                NavigationItem::make()
                    ->title('Dashboard')
                    ->route('dashboard')
                    ->icon('home')
                    ->show(Auth::user()->hasRole('admin')),

                NavigationItem::make()
                    ->title('Users')
                    ->route('users.index')
                    ->badge(fn() => User::count())
                    ->items(
                        NavigationItem::make()
                            ->title('Active Users')
                            ->route('users.active')
                    )
            ),
    ];
}
```

### In Blade Templates

```php
@inject('menuService', \App\Services\SideBarMenuService::class)

<!-- Render top menus -->
<div class="menu">
    @foreach($menuService->getTopMenus() as $group)
        <x-navigation.group :group="$group" />
    @endforeach
</div>
```

### Sidebar Components

The sidebar uses a unified component structure:

-   **`sidebar.blade.php`** (`<x-layouts.app.sidebar>`): Main wrapper component using DaisyUI drawer with integrated navbar and content area
    -   Location: `resources/views/components/layouts/app/sidebar.blade.php`
    -   Contains: Drawer structure, navbar with mobile toggle, main content area, and includes `<x-layouts.app.sidebar-menus />`
-   **`sidebar-menus.blade.php`** (`<x-layouts.app.sidebar-menus />`): Unified sidebar menu component
    -   Location: `resources/views/components/layouts/app/sidebar-menus.blade.php`
    -   Contains: Sidebar menu with top menus, bottom menus, and logo
    -   Responsive behavior is handled by DaisyUI's drawer component (`lg:drawer-open` class)
-   **`header.blade.php`** (`<x-layouts.app.header />`): Header component displaying page title, subtitle, and user menu
    -   Location: `resources/views/components/layouts/app/header.blade.php`
    -   Contains: Page title, optional subtitle, theme switcher, locale switcher, and user dropdown menu

**Usage**:

```php
<x-layouts.app.sidebar>
 <!-- Main content -->
</x-layouts.app.sidebar>
```

**Component Structure**:

```php
<!-- sidebar.blade.php -->
<div class="drawer lg:drawer-open">
    <div class="drawer-content">
        <div class="navbar">
            <x-layouts.app.header />
        </div>
        <main>{{ $slot }}</main>
    </div>
    <x-layouts.app.sidebar-menus />
</div>
```

**Note**: The sidebar uses View Composers (registered in `BladeServiceProvider`) to automatically inject menu data (`$sideBarTopMenus`, `$sideBarBottomMenus`, `$sideBarUserMenus`) into sidebar components. No manual service injection needed. Navigation items use `<div>` elements instead of `<ul>`/`<li>` for semantic HTML flexibility.

### Features

-   ✅ Fluent, chainable API
-   ✅ Permission-based visibility (`show()` with closures)
-   ✅ **Backend filtering**: Invisible items are filtered server-side for security
-   ✅ Nested menus (unlimited depth via `items()`)
-   ✅ Dynamic badges (closures for real-time counts)
-   ✅ External link handling
-   ✅ Active state detection (automatic route matching)
-   ✅ Icon support (icon component names via `<x-ui.icon>`, supports multiple icon packs with security validation)
-   ✅ HTML attributes support (returns array for `$attributes->merge()`)
-   ✅ Fully testable (24 unit tests)
-   ✅ Reusable across multiple services
-   ✅ **No form/button support**: Use static forms in Blade templates for actions
-   ✅ **Semantic HTML**: Uses `<div>` elements instead of `<ul>`/`<li>` for flexibility

### Testing

-   Unit tests: `tests/Unit/Services/Navigation/`
-   All unit tests pass (24 tests, 52 assertions)
-   Tests cover: fluent API, visibility filters, nested items, badges, active states, attributes
-   **Note**: Form/button tests have been removed as this functionality is no longer supported

### Design Patterns

-   **Builder Pattern**: Fluent interface for constructing navigation
-   **Factory Pattern**: Static `make()` methods for instantiation
-   **Composite Pattern**: Nested items (tree structure)
-   **Service Pattern**: Centralized menu definition
-   **Lazy Evaluation**: Closures for `show()` and `badge()` evaluated at render time

### Security

**Backend Filtering**: All visibility checks are performed server-side. Items with `show(false)` or failed permission checks are filtered out before being sent to the frontend, ensuring:

-   No sensitive menu items are exposed in HTML/JavaScript
-   Better performance (fewer items to render)
-   Security by default (frontend cannot bypass visibility rules)

The filtering happens at three levels:

1. **NavigationItem**: `getItems()` only returns visible nested items
2. **NavigationBuilder**: `getItems()` only returns visible items
3. **SideBarMenuService**: Each method filters out invisible groups/items before returning

### Extension Points

To add new navigation sections:

1. Add a new method to `SideBarMenuService` (e.g., `getAdminMenus()`)
2. Use NavigationBuilder and NavigationItem to define the structure
3. Filter invisible items: `array_filter($items, fn($item) => $item->isVisible())`
4. Render in Blade using `<x-navigation.group>` component

## History

### Navigation System Refactoring (2025-01-XX)

Simplified navigation system and split sidebar components:

- **Removed form/button support** from `NavigationItem`: Form and button methods (`form()`, `button()`) have been removed. Use static forms in Blade templates for actions like logout.
- **Removed class property**: The `class()` method has been removed from `NavigationItem`. Use `attributes(['class' => '...'])` instead.
- **Attributes as array**: `NavigationItem` now returns attributes as an array (not a string) for use with `$attributes->merge()` in Blade components.
- **Service injection**: Updated to use View Composers instead of `@inject` directive for automatic menu data injection.
- **Semantic HTML**: Navigation components now use `<div>` elements instead of `<ul>`/`<li>` for better flexibility.
- **Static logout form**: Logout is now handled as a static form in the sidebar components, not through `NavigationItem`.
- **Updated tests**: Removed `NavigationItemFormTest.php` as form/button functionality no longer exists.

