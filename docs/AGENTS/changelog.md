## Changelog

### 2025-01-XX

-   **Alpine.js + Livewire Integration - Reactive `$wire` Pattern**: Improved Alpine.js data components to use reactive `$wire` instead of passing it as a parameter
    -   **Removed `$wire` parameter**: Alpine.js data functions no longer accept `$wire` as a parameter - it's automatically available and reactive in the component context
    -   **Reactive `$wire`**: `$wire` is automatically updated by Livewire when components mount/unmount, making it safe for navigation scenarios
    -   **Component validation**: Added `refreshIfAvailable()` helper method that validates component existence before calling `$refresh()`
    -   **Navigation-safe**: After navigation, `$wire` automatically points to the new component when `init()` runs again
    -   **Removed try-catch blocks**: Replaced error handling with validation checks before calling `$refresh()`
    -   **Updated components**: `notificationCenter()` and `notificationDropdown()` Alpine data components now use reactive `$wire` pattern
    -   **Updated Blade templates**: Removed `$wire` parameter from `x-data` directives in notification components
    -   **Benefits**:
        -   No stale references after navigation
        -   Cleaner code without try-catch blocks
        -   Automatic component lifecycle handling
        -   Better error prevention through validation
    -   **Documentation**: Updated `docs/alpinejs/livewire-integration.md` with reactive `$wire` pattern and best practices

### 2025-12-22

-   **User Status and Login Tracking**: Implemented user active status and login tracking functionality
    -   **Database Fields**: Added `is_active` (boolean, default: `true`) and `last_login_at` (timestamp, nullable) to `users` table
    -   **BaseUserModel Enhancements**: Moved user status and login tracking methods to `BaseUserModel` for reuse across all authenticatable models
        -   `isActive()` - Check if user is active
        -   `activate()` - Activate the user
        -   `deactivate()` - Deactivate the user
        -   `updateLastLoginAt()` - Update last login timestamp (base implementation)
        -   `scopeActive()` - Query scope for active users
        -   `scopeInactive()` - Query scope for inactive users
    -   **User ID 1 Protection**: Moved boot method to `BaseUserModel` with automatic protection for user ID 1
        -   Prevents deletion of user ID 1
        -   Handles MySQL trigger for user ID 1 updates (requires `@laravel_user_id_1_self_edit` session variable)
        -   Only user ID 1 can edit themselves when authenticated
        -   System updates (like `last_login_at`) automatically bypass protection
    -   **Authentication Integration**:
        -   Inactive users cannot log in - `FortifyServiceProvider` checks `isActive()` before allowing authentication
        -   `last_login_at` is automatically updated on successful login via `SyncUserPreferencesOnLogin` listener
    -   **User Factory**: Added `is_active => true` to default factory state and `inactive()` state method
    -   **User Registration**: New users are created with `is_active => true` by default
    -   **DataTable Integration**:
        -   Added `is_active` filter and sortable field to `UsersDataTableConfig`
        -   Added bulk actions for activate/deactivate users
        -   Updated `UserDataTableTransformer` to include `is_active` and `last_login_at` in transformed data
    -   **Documentation**: Updated `AGENTS.md` with user status management and login tracking details

### 2025-01-XX

-   **DataTable Component Architecture**: Moved all PHP logic directly into component classes (`Datatable` and `Table`)
    -   **Component Classes**: `App\View\Components\Datatable` and `App\View\Components\Table` - All logic in component classes
    -   **Purpose**: Consolidates logic in component classes, providing methods that can be called from Blade templates
    -   **Features**:
        -   All component props accepted via constructor
        -   Service registries initialized once in constructor (DataTableComponentRegistry, DataTableFilterComponentRegistry)
        -   Public methods available for all processing and computed values
        -   On-demand processing in Blade templates using `@php` blocks (for performance)
        -   No pre-processing - rows/columns processed only when iterating
    -   **Key Methods**:
        -   **Datatable Component**: `getColumnsCount()`, `hasActionsPerRow()`, `getBulkActionsCount()`, `showBulkActionsDropdown()`, `hasFilters()`, `hasSelected()`, `showBulkBar()`, `hasPaginator()`, `processFilter()`, `getRowActionModalConfig()`, `getBulkActionModalConfig()`, `getModalStateId()`, `findActionByKey()`, and all getter methods
        -   **Table Component**: `processRow()`, `processColumn()`, `processHeaderColumn()`, `getColumnsCount()`, `hasActionsPerRow()`, and all getter methods
    -   **Updated Components**:
        -   `resources/views/components/datatable.blade.php` - Uses component methods directly, `@php` blocks for filter processing and modal configs
        -   `resources/views/components/table/table.blade.php` - Uses component methods in `@php` blocks for row/column processing
        -   `resources/views/components/table/header.blade.php` - Uses inline closure for header processing, accepts props directly
    -   **Benefits**:
        -   **Performance**: On-demand processing, no unnecessary pre-processing
        -   **Clarity**: All logic in component classes, easy to find
        -   **Standardization**: One pattern, no backward compatibility confusion
        -   **Flexibility**: `@php` blocks allowed for performance-critical loops
    -   **Removed**: `DataTableViewData` service class - all logic moved to component classes
    -   **Documentation**: Updated `docs/components.md` with new component-based architecture

### 2025-01-XX

-   **DataTable Preferences System**: Implemented comprehensive preferences system for DataTable components following the FrontendPreferences pattern
    -   **Service**: `App\Services\DataTable\DataTablePreferencesService` (singleton) with session-backed caching
    -   **Storage Strategy**:
        -   **Guests**: Preferences stored in session only
        -   **Authenticated users**: Preferences stored in `users.frontend_preferences` JSON column under keys like `datatable_preferences.users`, synced to session
        -   **Session as Single Source of Truth**: Session is always the single source of truth for reads (with automatic DB sync for authenticated users)
    -   **Preferences Stored**: All DataTable preferences (search, filters, per_page, sort) are automatically saved and loaded
    -   **Architecture**:
        -   `DataTablePreferencesService`: Main service (same pattern as `FrontendPreferencesService`)
        -   `SessionDataTablePreferencesStore`: Session-based storage
        -   `UserJsonDataTablePreferencesStore`: User JSON column storage
        -   `DataTablePreferencesStore` interface: Contract for storage implementations
    -   **Constants**: `App\Constants\DataTable\DataTable` for session keys, preference keys, and helper methods
    -   **Integration**:
        -   `BaseDataTableComponent` automatically loads preferences on mount
        -   Preferences are automatically saved when search, filters, per_page, or sort change
        -   `SessionService` uses `DataTablePreferencesService` to store all preferences
        -   Login listener (`SyncUserPreferencesOnLogin`) syncs all DataTable preferences from DB to session on login
    -   **Storage Structure**: Preferences stored in user's `frontend_preferences` JSON column:
        ```json
        {
            "locale": "en_US",
            "theme": "light",
            "datatable_preferences.users": {
                "search": "john",
                "per_page": 25,
                "sort": { "column": "name", "direction": "asc" },
                "filters": { "is_active": true, "email_verified_at": true }
            }
        }
        ```
    -   **Documentation**: Updated `docs/components.md` and `AGENTS.md` with DataTable preferences system details

### 2025-01-XX

-   **Authentication Code Refactoring**: Improved code quality, removed duplication, and enhanced separation of concerns

    -   **Created Authentication Helpers**: Added `app/helpers/auth-helpers.php` with centralized authentication helper functions
        -   `getIdentifierFromRequest()` - Centralizes identifier extraction logic (removes duplication)
        -   `setTeamSessionForUser()` - Centralizes team session setting logic (removes duplication)
    -   **Created Permission Helpers**: Added `app/helpers/permission-helpers.php` with centralized permission cache clearing
        -   `clearPermissionCache()` - Centralizes Spatie Permission cache clearing logic used across seeders
    -   **Seeder Refactoring**: Refactored all seeders to use `clearPermissionCache()` helper instead of inline cache clearing
        -   `RoleAndPermissionSeeder`: Separated concerns into `createPermissions()`, `createRoles()`, and `assignPermissionsToSuperAdmin()` methods
        -   `EssentialUserSeeder`: Uses helper for cache clearing
        -   `SampleUserSeeder`: Uses helper for cache clearing
    -   **Removed Duplication**: Eliminated duplicate identifier-to-email mapping logic from `FortifyServiceProvider` (middleware already handles it)
    -   **Code Organization**: Refactored `FortifyServiceProvider` to use helper functions, improving maintainability
    -   **Updated LoginRequest**: Now uses centralized `setTeamSessionForUser()` helper instead of inline logic
    -   **Improved PHPDoc**: Enhanced documentation throughout authentication code for better clarity
    -   **Composer Autoload**: Added `auth-helpers.php` and `permission-helpers.php` to Composer autoload files
    -   **Documentation**: Updated `AGENTS.md` with authentication and permission helpers documentation

-   **Super Admin Gate Pattern**: Implemented Spatie Permissions recommended Super-Admin pattern using `Gate::before()`
    -   **Implementation**: Added `Gate::before()` in `AppServiceProvider::boot()` to grant all permissions to users with `Roles::SUPER_ADMIN` role
    -   **Location**: `app/Providers/AppServiceProvider.php` (in `boot()` method)
    -   **Benefits**: Allows using permission-based controls (`@can()`, `$user->can()`) throughout the app without checking for Super Admin status everywhere
    -   **Best Practice**: Follows Spatie Permissions best practices - primarily check permissions, not roles
    -   **Gate Updates**: Enhanced existing Gate definitions in `TelescopeServiceProvider`, `HorizonServiceProvider`, and `LogViewerServiceProvider` to explicitly check for Super Admin role for clarity
    -   **Documentation**: Updated `docs/spatie-permission.md` and `AGENTS.md` with implementation details
    -   **Important Note**: Direct calls to `hasPermissionTo()`, `hasAnyPermission()`, etc. bypass the Gate and won't get Super Admin access - always use `can()` methods instead
    -   **Constants**: Uses `Roles::SUPER_ADMIN` constant (no hardcoded strings)

### 2025-12-23

-   **Notification System - Session Channel Security Enhancement**:
    -   **Converted session channel to public channel**: Changed from `private-notifications.session.{sessionId}` to `public-notifications.session.{sessionId}` for better security and simplicity
    -   **Security**: Session IDs are cryptographically random (40+ characters) and act as the security mechanism themselves
    -   **No authentication required**: Public channels don't require authentication, eliminating 403 errors on login/auth pages
    -   **No authorization overhead**: Public channels don't need authorization callbacks, making implementation cleaner
    -   **Error handling improvements**: Enhanced error handling for "Component not found" errors during Livewire navigation - these are now silently ignored as expected behavior
    -   **Code cleanup**: Removed all debug logs from production code while maintaining error logging for actual issues
    -   **Production ready**: System is now fully functional and tested, marked as production-ready in documentation
    -   **Updated documentation**: Added session channel security details, updated broadcasting channels section in `docs/notifications.md` and `AGENTS.md`

### 2025-01-XX

-   **Notification System Improvements**:
    -   **Fixed duplicate toast notifications**: Fixed issue where `toastCenter` component was creating duplicate subscriptions when re-initialized (e.g., during Livewire navigation). Changed from cleanup-based approach to idempotent subscription logic - component now checks if already subscribed and returns early instead of cleaning up and re-subscribing.
    -   **Added `toUserTeams()` method**: New method in `NotificationBuilder` to send notifications to all teams a user belongs to. Broadcasts to each team channel separately, or falls back to user channel if user has no teams. Supports persistence for all team members in each team.
    -   **Updated documentation**: Added `toUserTeams()` usage examples and updated broadcasting channels section in `docs/notifications.md` and `AGENTS.md`.

### 2025-01-XX

-   **Asset Management Optimization**: Refactored CSS/JS structure to avoid duplication and optimize bundle sizes
    -   **Created Base CSS**: Created `resources/css/base.css` containing all Tailwind/DaisyUI configuration (shared foundation)
    -   **Modular CSS Structure**:
        -   `app.css` imports `base.css` + `sidebar.css` (for authenticated app layout)
        -   `auth.css` imports only `base.css` (for authentication layout, smaller bundle)
        -   `sidebar.css` contains only component styles (no Tailwind imports)
    -   **Conditional Asset Loading**:
        -   App layout loads: `app.css`, `app.js`, `notification-center.js`
        -   Auth layout loads: `auth.css`, `app.js` (no sidebar styles or notification JS)
    -   **Benefits**:
        -   No CSS duplication (base styles shared via CSS imports)
        -   Smaller bundle sizes (auth pages don't load unnecessary assets)
        -   Maintainable (single source of truth for base styles)
        -   Uses Tailwind CSS v4's automatic `@import` bundling
    -   **Vite Configuration**: Updated entry points to `app.css`, `auth.css`, `app.js`, `notification-center.js`
    -   **Documentation**: Added comprehensive Asset Management section to `AGENTS.md` with file structure, loading patterns, and guidelines for adding new assets

### 2025-12-19

-   **Notification Dropdown Enhancements**: Improved dropdown state management and badge calculation
    -   **Badge Calculation**: Moved badge calculation from Blade template to Livewire computed property `getUnreadBadgeProperty()` (capped at "99+")
    -   **State Management**: Added Alpine.js reactive state (`isOpen` and `wasOpened`) to track dropdown open/close state
    -   **Auto-Mark as Read**: Notifications are now marked as read when the dropdown closes (via `@click.away`), but only if it was actually opened by the user
    -   **Persistent State**: The `dropdown-open` class is managed via Alpine.js `x-bind:class` to maintain state during Livewire updates
    -   **Badge Styling**: Updated badge to use `badge-xs` with `w-4 h-4` fixed size for smaller, cleaner appearance
    -   **Dropdown Component Enhancement**: Updated dropdown component to properly merge Alpine.js `x-bind:class` with static classes using `$attributes->merge()`
    -   **Notification Center**: Updated to use `#[Computed]` attribute instead of `getXxxProperty()` methods for better Livewire 4 compatibility

### 2025-01-XX

-   **Frontend Preferences System Refactoring**: Refactored `FrontendPreferencesService` to use session as single source of truth
    -   **Session-First Architecture**: Session is now always the single source of truth for all preference reads
    -   **Loading Flow**:
        -   Authenticated users: On first read, preferences are loaded from database and synced to session. Subsequent reads come from session.
        -   Guest users: All reads come from session
    -   **Update Flow**:
        -   Authenticated users: Database is updated first, then session is updated
        -   Guest users: Session is updated only
    -   **Benefits**:
        -   Single source of truth simplifies logic
        -   Fast reads from session (no database queries on every read)
        -   Preferences persist in database for authenticated users
        -   Database and session stay in sync for authenticated users
    -   **Implementation**:
        -   Removed `$persistentStore` property, replaced with `$sessionStore`
        -   Added `syncFromDatabaseIfNeeded()` method to sync DB preferences to session on first read
        -   Added `syncUserPreferencesToSession()` method to sync preferences for a specific user
        -   Updated `set()` and `setMany()` to update DB first for authenticated users, then session
        -   All reads now come from session after initial sync
    -   **Login Event Listener**: Created `SyncUserPreferencesOnLogin` listener class using `php artisan make:listener` to sync preferences from DB to session immediately on login
    -   **Tests**: All 21 FrontendPreferencesService tests pass (including login sync test), all 8 PreferencesController tests pass
    -   **Documentation**: Updated `AGENTS.md` with new architecture details, loading/update flows, and login event listener

### 2025-01-XX

-   **Sidebar CSS Cleanup**: Removed unused mobile menu classes

    -   **Removed Classes**: Removed `.sidebar-desktop` and `.sidebar-mobile` CSS classes from `resources/css/sidebar.css`
    -   **Removed from Blade**: Removed `.sidebar-desktop` class from `sidebar.blade.php` component
    -   **Responsive Behavior**: Responsive behavior is now handled entirely by DaisyUI's drawer component (`lg:drawer-open` class)
    -   **Simplified CSS**: CSS now only includes styles for `.sidebar-top-menus`, `.sidebar-bottom-menus`, and `.sidebar-user-menus`
    -   **Documentation**: Updated `AGENTS.md` to reflect removal of mobile menu classes

-   **BladeServiceProvider Refactoring**: Improved View Composer organization and data sharing

    -   **Method Organization**: Split into separate methods (`initLayoutVariables()`, `initPageTitle()`, `initPageSubtitle()`) for better maintainability
    -   **Value-Based Sharing**: Changed from sharing service objects (`$i18n`, `$preferences`, `$menuService`) to sharing specific values (`$htmlLangAttribute`, `$currentTheme`, `$sideBarTopMenus`, etc.)
    -   **Targeted Composers**: Removed wildcard patterns, using more specific view paths for better performance
    -   **Sidebar Menu Data**: Changed from sharing `$menuService` object to sharing specific menu arrays (`$sideBarTopMenus`, `$sideBarBottomMenus`, `$sideBarUserMenus`)
    -   **Layout Templates**: Updated to use specific values (`$htmlLangAttribute`, `$htmlDirAttribute`, `$currentTheme`) instead of service objects
    -   **Documentation**: Updated View Composers section to reflect new structure and shared variables

-   **Auth Layout Simplification**: Streamlined authentication layout structure

    -   **Removed Components**: Deleted `auth/card.blade.php` and `auth/simple.blade.php` components
    -   **Single Layout**: Now only uses `auth/split.blade.php` component for all authentication pages
    -   **Component Structure**: `split.blade.php` changed from full HTML document to component-only (removed DOCTYPE, html, head, body tags)
    -   **Layout Wrapper**: `auth.blade.php` now wraps `split.blade.php` component instead of `simple.blade.php`
    -   **Route Updates**: Changed logo links from `route('home')` to `route('dashboard')` in split layout
    -   **File Structure**:
        -   `resources/views/components/layouts/auth.blade.php` - Main auth layout wrapper
        -   `resources/views/components/layouts/auth/split.blade.php` - Split-screen auth component

-   **App Layout Simplification**: Cleaned up app layout structure

    -   **Removed Nested Main**: Removed nested `<main>` tag from `app.blade.php` (moved to `sidebar.blade.php`)
    -   **Value-Based Variables**: Changed from service objects to specific values (`$htmlLangAttribute`, `$currentTheme`)
    -   **Simplified Structure**: Cleaner component hierarchy

-   **I18nService Enhancement**: Removed default fallback values

    -   **No Defaults**: `getDefaultLocale()` and `getFallbackLocale()` methods no longer have hardcoded fallback values
    -   **Config Required**: These methods now rely entirely on `config('i18n.*')` values
    -   **Better Error Handling**: Ensures configuration is properly set

-   **BasePageComponent Enhancement**: Added subtitle support to page title management system

    -   **Page Subtitle**: `BasePageComponent` now supports optional `$pageSubtitle` property alongside `$pageTitle`
    -   **Automatic Sharing**: Subtitles are automatically shared via `View::share()` in `boot()` method, just like titles
    -   **Translation Support**: Subtitles support translation keys (containing dots) - automatically translated via `__()`
    -   **Header Display**: Header component (`components.layouts.app.header`) now conditionally displays subtitles below the title
    -   **View Composers**: `BladeServiceProvider` updated to share `$pageSubtitle` with header and head partials
    -   **Usage**: Set `public string $pageSubtitle = 'ui.pages.example.description';` property in components (optional)
    -   **Updated Components**: Settings pages (profile, password, two-factor) now use subtitles for better UX
    -   **Documentation**: Updated `AGENTS.md` with subtitle usage examples and requirements

-   **Sidebar Component Refactoring**: Unified sidebar structure for better maintainability
    -   **Unified Component**: Removed separate `desktop-menu.blade.php` and `mobile-menu.blade.php` components
    -   **Single Component**: Created unified `sidebar-menus.blade.php` component (`<x-layouts.app.sidebar-menus />`)
    -   **Removed Mobile Menu Classes**: Removed `.sidebar-desktop` and `.sidebar-mobile` CSS classes - responsive behavior is handled by DaisyUI's drawer component
    -   **File Structure**:
        -   `resources/views/components/layouts/app/sidebar.blade.php` - Main wrapper (`<x-layouts.app.sidebar>`)
        -   `resources/views/components/layouts/app/sidebar-menus.blade.php` - Unified menu component (`<x-layouts.app.sidebar-menus />`)
        -   `resources/views/components/layouts/app/header.blade.php` - Header component (`<x-layouts.app.header />`)
    -   **View Composers**: Sidebar now uses View Composers (in `BladeServiceProvider`) to inject menu data automatically
    -   **No Props Needed**: Removed need to pass menu data as props - data is automatically available via View Composers
    -   **Integrated Navbar**: Mobile menu toggle is now integrated directly into the navbar within `sidebar.blade.php`
    -   **Simplified Structure**: Cleaner component hierarchy with fewer files to maintain
    -   **Updated Documentation**: Updated sidebar component documentation to reflect unified structure with exact file paths and component references

### 2025-01-XX

-   **Automatic Page Title Management**: Implemented automatic page title management system using `$pageTitle` variable
    -   **BasePageComponent**: Created `App\Livewire\BasePageComponent` base class for all full-page Livewire components
    -   **Title Resolution**: Supports static (component property) and controller (view data) methods
    -   **Translations**: Automatic translation support - translation keys (containing dots) are automatically translated via `__()`
    -   **View Composer**: Added View Composer in `BladeServiceProvider` to share `$pageTitle` with `partials.head` and `components.layouts.app.header`
    -   **SPA Navigation**: Full support for `wire:navigate` with automatic title updates via `View::share()` in component's `boot()` method
    -   **Seamless**: Uses `boot()` lifecycle hook - no need to call `parent::mount()`
    -   **Rule**: ALL full-page Livewire components MUST extend `BasePageComponent` (not `Livewire\Component`)
    -   **Usage**: Set `public ?string $pageTitle = 'ui.pages.example';` property in components (use translation keys)
    -   **Translation Files**: Added `ui.pages.*` section to translation files (`lang/en_US/ui.php`, `lang/fr_FR/ui.php`)
    -   **Updated Components**: All existing Livewire page components now extend `BasePageComponent` and use translation keys
    -   **Documentation**: Updated `AGENTS.md` with BasePageComponent requirement, translation usage, and usage examples
    -   **Later Enhanced**: Added `$pageSubtitle` support for optional subtitle text displayed below page titles in header

### 2025-01-XX

-   **Icon Component Refactoring**: Converted icon component from Livewire to Blade component
    -   **Converted to Blade Component**: Changed from Livewire component (`⚡dynamic-icon-island.blade.php`) to regular Blade component (`ui/icon.blade.php`)
    -   **Moved to UI Folder**: Component is now located at `resources/views/components/ui/icon.blade.php`
    -   **Updated Usage**: All references changed from `<livewire:dynamic-icon-island>` to `<x-ui.icon>`
    -   **Added Security**: Implemented input validation and sanitization for icon names, pack names, and CSS classes
    -   **Size Support**: Added support for predefined sizes (xs, sm, md, lg, xl) for backward compatibility
    -   **Performance**: Removed Livewire overhead for static icon rendering (no reactivity needed)
    -   **Dependency Injection**: Uses `@inject` directive to inject `IconPackMapper` service
    -   **Updated Documentation**: Navigation system documentation updated to reflect new icon component usage

### 2025-01-XX

-   **BasePageComponent Enhancement**: Added subtitle support to page title management system

    -   **Page Subtitle**: `BasePageComponent` now supports optional `$pageSubtitle` property alongside `$pageTitle`
    -   **Automatic Sharing**: Subtitles are automatically shared via `View::share()` in `boot()` method, just like titles
    -   **Translation Support**: Subtitles support translation keys (containing dots) - automatically translated via `__()`
    -   **Header Display**: Header component (`components.layouts.app.header`) now conditionally displays subtitles below the title
    -   **View Composers**: `BladeServiceProvider` updated to share `$pageSubtitle` with header and head partials
    -   **Usage**: Set `public string $pageSubtitle = 'ui.pages.example.description';` property in components (optional)
    -   **Updated Components**: Settings pages (profile, password, two-factor) now use subtitles for better UX
    -   **Documentation**: Updated `AGENTS.md` with subtitle usage examples and requirements

-   **Sidebar Component Refactoring**: Unified sidebar structure for better maintainability

    -   **Unified Component**: Removed separate `desktop-menu.blade.php` and `mobile-menu.blade.php` components
    -   **Single Component**: Created unified `sidebar-menus.blade.php` component
    -   **Removed Mobile Menu Classes**: Removed `.sidebar-desktop` and `.sidebar-mobile` CSS classes - responsive behavior is handled by DaisyUI's drawer component
    -   **View Composers**: Sidebar now uses View Composers (in `BladeServiceProvider`) to inject menu data automatically
    -   **No Props Needed**: Removed need to pass menu data as props - data is automatically available via View Composers
    -   **Integrated Navbar**: Mobile menu toggle is now integrated directly into the navbar within `sidebar.blade.php`
    -   **Simplified Structure**: Cleaner component hierarchy with fewer files to maintain
    -   **Updated Documentation**: Updated sidebar component documentation to reflect unified structure

-   **Navigation System Refactoring**: Simplified navigation system and split sidebar components
    -   **Removed form/button support** from `NavigationItem`: Form and button methods (`form()`, `button()`) have been removed. Use static forms in Blade templates for actions like logout.
    -   **Removed class property**: The `class()` method has been removed from `NavigationItem`. Use `attributes(['class' => '...'])` instead.
    -   **Attributes as array**: `NavigationItem` now returns attributes as an array (not a string) for use with `$attributes->merge()` in Blade components.
    -   **Service injection**: Updated to use View Composers instead of `@inject` directive for automatic menu data injection.
    -   **Semantic HTML**: Navigation components now use `<div>` elements instead of `<ul>`/`<li>` for better flexibility.
    -   **Static logout form**: Logout is now handled as a static form in the sidebar components, not through `NavigationItem`.
    -   **Updated tests**: Removed `NavigationItemFormTest.php` as form/button functionality no longer exists. Test count: 24 tests, 52 assertions.

### 2025-01-XX

-   **Livewire 4 Folder Structure Reorganization**: Removed `livewire/` directory to align with Livewire 4 philosophy
    -   Moved auth views from `livewire/auth/` to `pages/auth/` (full-page components)
    -   Moved nested components from `livewire/settings/` to `components/settings/` (reusable components)
    -   Updated `FortifyServiceProvider` to reference new auth view paths (`pages.auth.*`)
    -   Removed `livewire` from `component_locations` in `config/livewire.php`
    -   **New Structure**:
        -   Full-page components: `resources/views/pages/` (use `pages::` namespace)
        -   Nested/reusable Livewire components: `resources/views/components/` (referenced directly, e.g., `livewire:settings.delete-user-form`)
        -   Regular Blade components: `resources/views/components/`
    -   Since Livewire is the default in Livewire 4, no separate `livewire/` folder is needed

### 2025-12-13

-   **Livewire 4 Folder Structure Migration**: Completed migration to Livewire 4 folder structure
    -   Moved full-page components from `livewire/settings/` to `pages/settings/` with `.blade.php` extension
    -   Updated routes to use `pages::settings.*` namespace format
    -   Created Livewire layouts in `resources/views/layouts/` with `@livewireStyles` and `@livewireScripts`
    -   Created Blade component wrappers in `resources/views/components/layouts/` for regular views
    -   Updated `config/livewire.php` to include `pages` in `component_locations` and `component_namespaces`
    -   All single-file components now use `.blade.php` extension (required by Livewire 4)

### 2025-01-XX

-   **Livewire 4 Comprehensive Documentation**: Created comprehensive `docs/livewire-4.md` with AI-friendly indexing system
    -   Added detailed AI-friendly index at the top with quick reference by topic and search keywords
    -   Comprehensive coverage of all Livewire 4 features: Components, Properties, Actions, Forms, Events, Lifecycle Hooks, Nesting, Testing, AlpineJS Integration, Navigation, Islands, Lazy Loading, Loading States, Validation, File Uploads, Pagination, URL Query Parameters, File Downloads, Teleport, Morphing, Hydration, Synthesizers, JavaScript, Troubleshooting, Security, CSP
    -   Each section includes code examples, usage patterns, and cross-references
    -   Search keywords section for AI assistants to quickly locate specific functionality
    -   Organized by core concepts, advanced features, validation & data, UI & interaction, advanced technical, testing & troubleshooting, and security & configuration
    -   **Documentation**: See `docs/livewire-4.md` for complete Livewire 4 documentation with AI-friendly indexing

### 2025-12-13

-   **Logging Configuration**: Configured daily log rotation with level-specific folders and exact level filtering
    -   Each log level (emergency, alert, critical, error, warning, notice, info, debug) now has its own folder: `storage/logs/{level}/laravel-{date}.log`
    -   Daily rotation enabled for all level-specific channels using Monolog's RotatingFileHandler
    -   **Exact level filtering**: Each log file contains ONLY messages of its exact level using Monolog's FilterHandler
    -   Created `App\Logging\LevelSpecificLogChannelFactory` to handle exact level filtering with daily rotation
    -   Deprecated logs configured with daily rotation in `storage/logs/deprecations/laravel-{date}.log`
    -   Default stack channel routes to all level-specific channels
    -   Retention configurable via `LOG_DAILY_DAYS` environment variable (default: 14 days)
-   **Constants and Code Reusability Rule**: Added critical rule for using constants and avoiding duplication
    -   Created `App\Constants\LogLevels` class for log level constants
    -   Created `App\Constants\LogChannels` class for log channel constants
    -   Refactored `config/logging.php` to use constants and helper function to eliminate duplication
    -   Added rule to agent.md: Always use constants instead of hardcoded strings when possible, and always avoid duplication for easy maintenance
-   **Log Clearing Command**: Created `php artisan logs:clear` command
    -   Clears all log files from `storage/logs` directory
    -   Supports `--level` option to clear logs for a specific level only
    -   Uses constants from `LogChannels` class
    -   Provides helpful feedback with Laravel Prompts

### 2025-12-16

-   **Frontend Preferences System**: Implemented centralized frontend preferences service for managing user preferences (locale, theme, timezone)

    -   **Service**: `App\Services\FrontendPreferences\FrontendPreferencesService` (singleton) with session-backed caching
    -   **Storage**: Guest users store preferences in session; authenticated users persist to `users.frontend_preferences` JSON column
    -   **Performance**: First request loads from DB into session cache; subsequent reads use session cache only
    -   **Middleware**: `ApplyFrontendPreferences` automatically applies locale and timezone preferences on each request
    -   **UI Components**: Language and theme switchers (`<x-preferences.locale-switcher />`, `<x-preferences.theme-switcher />`) in app/auth layouts
    -   **Constants**: `App\Constants\Preferences\FrontendPreferences` for session keys, preference keys, defaults, validation
    -   **Database**: Added `frontend_preferences` JSON column to `users` table with array cast
    -   **Removed**: Settings → Appearance page (theme switcher moved to header/sidebar)

-   **Theme Management**: Switched from client-side `localStorage` to server-side `data-theme` attribute
-   **Auto-Detection**: Automatic browser language detection on first visit (server-side only, no JavaScript)
    -   Language detection from `Accept-Language` header using `$request->header('Accept-Language')`
    -   **No theme detection** - Default theme preference is `"light"` for first-time visitors
    -   **No JavaScript required** - All detection is server-side using request headers
    -   **No cookies used** - All preferences stored in session (guests) or database (authenticated users)
    -   Detection only occurs when no preferences are set (first visit)
    -   Detected preferences are automatically saved and persisted
-   **Comprehensive Tests**: 31 tests covering service, middleware, UI components, and auto-detection behavior
-   **Documentation**: Added Frontend Preferences section to `AGENTS.md` and locale switching info to `docs/internationalization.md`

-   **DateTime and Currency Helper Functions**: Created locale-aware helper functions for formatting dates, times, and currency
    -   Created `app/helpers/dateTime.php` with `formatDate()`, `formatTime()`, and `formatDateTime()` functions
    -   Created `app/helpers/currency.php` with `formatCurrency()` function
    -   Updated `config/i18n.php` to include `symbol_position`, `decimal_separator`, and `thousands_separator` for currency configuration
    -   All helpers use `I18nService` internally instead of direct config access
    -   Added comprehensive tests (18 tests for dateTime, 14 tests for currency)
    -   Updated `composer.json` to autoload new helper files
    -   Updated documentation (`docs/internationalization.md`) with helper function usage
-   **I18nService Enhancements**: Enhanced `I18nService` with additional methods for centralized locale management
    -   Added `getSupportedLocales()`, `getDefaultLocale()`, `getFallbackLocale()`
    -   Added `getLocaleMetadata(?string $locale)`, `isLocaleSupported()`, `getValidLocale()`
    -   Updated service to use its own methods internally for consistency
    -   Added comprehensive tests (18 tests)
-   **BladeServiceProvider**: Created dedicated service provider for Blade/view-related functionality
    -   Moved View Composer logic from `AppServiceProvider` to `BladeServiceProvider`
    -   Shares `I18nService` with layout templates via View Composers
    -   Shares `SideBarMenuService` only with sidebar template
    -   Replaced all `@inject` directives with View Composers
    -   Added comprehensive tests (4 tests)
-   **Code Style Rules**: Added new rules to `AGENTS.md`
    -   Always use function guards and early returns
    -   Do NOT use `function_exists()` checks in helper files
    -   Always use `I18nService` for locale-related code
    -   Use View Composers instead of `@inject` for global data

### 2025-12-23

-   **Modal Components (Class-Based + Theme-Aware)**: Converted modal Blade components to class-based components and removed inline Blade `@php` logic
    -   **Base Modal**: `App\View\Components\Ui\BaseModal` + `resources/views/components/ui/base-modal.blade.php`
        -   Theme-aware backdrop (`bg-base-*` + `backdrop-blur-*`)
        -   Single `placement` prop with 9-position grid (`top-left` … `bottom-right`) and responsive default (bottom on mobile, center on `sm+`)
    -   **Confirm Modal**: `App\View\Components\Ui\ConfirmModal` + `resources/views/components/ui/confirm-modal.blade.php`
        -   Keeps event-driven confirmation UX (`confirm-modal` event) while delegating structure to `<x-ui.base-modal>`

### 2025-01-XX

-   **Dual Authentication System**: Implemented email and username login support

    -   **User Model**: Added `findByIdentifier()` method to support lookup by email or username
    -   **Middleware**: Created `MapLoginIdentifier` middleware to map `identifier` field to `email` for Fortify validation compatibility
    -   **Service Provider**: Refactored `FortifyServiceProvider` with separated concerns:
        -   `configureAuthentication()` - Custom authentication logic supporting both email and username
        -   `configureAuthenticationPipeline()` - Custom pipeline with conditional `CanonicalizeUsername` skip for usernames
        -   `getLoginView()` - Environment-based login view (production: text input, development: user dropdown)
        -   `getDevelopmentUsers()` - Helper to fetch users for development dropdown
        -   `formatUserLabel()` - Helper to format user labels for dropdown display
    -   **Rate Limiting**: Enhanced to support both `identifier` and `email` fields
    -   **Team Context**: Automatically sets `team_id` in session on successful login
    -   **Code Quality**: Removed all debug logs, extracted helper methods, improved separation of concerns
    -   **Documentation**: Updated `AGENTS.md` with dual authentication details and middleware documentation

-   **Livewire 4 Upgrade**: Upgraded from Livewire v3 + Volt to Livewire v4 (beta) with built-in single-file components
    -   Updated `composer.json` to require `livewire/livewire:^4.0@beta` and removed `livewire/volt`
    -   Converted all Volt components to Livewire 4 single-file components (replaced `Livewire\Volt\Component` with `Livewire\Component`)
    -   Updated routes from `Volt::route()` to `Route::livewire()` (preferred method in Livewire 4)
    -   Removed `VoltServiceProvider` and updated `bootstrap/providers.php`
    -   **Folder Structure Reorganization**:
        -   Moved full-page components to `resources/views/pages/` with `pages::` namespace
        -   Created `resources/views/layouts/` for Livewire page layouts (with `@livewireStyles`/`@livewireScripts`)
        -   Created Blade component wrappers in `resources/views/components/layouts/` for regular views
        -   Updated `config/livewire.php` with proper `component_locations` and `component_namespaces`
    -   **File Extensions**: All single-file components must use `.blade.php` extension (not `.php`)
    -   Created comprehensive `docs/livewire-4.md` documentation file
    -   Updated agent.md to reflect Livewire 4 patterns and reference documentation
    -   **Documentation**: See `docs/livewire-4.md` for complete Livewire 4 documentation, upgrade guide, and best practices
-   Initial agent.md creation
-   Documented stable configuration patterns
-   Documented Redis client environment-based selection
-   Documented project structure and conventions
-   Added environment helper functions (`app/helpers/app-helpers.php`)
    -   Functions: `appEnv()`, `isProduction()`, `isDevelopment()`, `isStaging()`, `isLocal()`, `isTesting()`, `inEnvironment()`
    -   Updated config files to use helper functions instead of direct config checks
-   **UUID Requirement**: All tables must have a UUID column
    -   Updated all existing migrations to include UUID columns
    -   Added rule for future development: all new tables must include `$table->uuid('uuid')->unique()->index();`
-   **Automatic UUID Generation**: Implemented `HasUuid` trait and base model classes
    -   Created `App\Models\Concerns\HasUuid` trait that automatically generates unique UUIDs
    -   Created `App\Models\Base\BaseModel` base class for regular models (includes HasUuid)
    -   Created `App\Models\Base\BaseUserModel` base class for authenticatable models (includes HasUuid, HasFactory, Notifiable)
    -   Updated User model to extend `BaseUserModel`
    -   UUIDs are generated on model creation and checked for uniqueness
    -   Models using base classes use UUID as route key name
    -   Added comprehensive tests for UUID generation
    -   **Rule**: All new models must extend `App\Models\Base\BaseModel` or `App\Models\Base\BaseUserModel` instead of Eloquent base classes
-   **Soft Delete Requirement**: Implemented soft deletes for all models by default
    -   Added `SoftDeletes` trait to `BaseModel` and `BaseUserModel` base classes
    -   Updated migrations to include `$table->softDeletes();` for: `users`, `teams`, `permissions`, `roles`, `notifications`
    -   Added `SoftDeletes` trait to `Permission` and `Role` models (extend Spatie's models)
    -   **Exceptions**: `PasswordResetToken` model extends `Model` directly (not `BaseModel`) to avoid soft deletes, as password reset tokens are temporary and should be hard deleted
    -   **Rule**: All new models must have soft deletes enabled by default via base classes
    -   **Rule**: All new migrations must include `$table->softDeletes();` unless the table is an exception (temporary tokens, pivot tables, monitoring tables)
-   **Intelephense Helper**: Added rule and documentation for fixing Intelephense errors
    -   Updated `IntelephenseHelper.php` with missing Auth and Session facade methods
    -   Added `logout()`, `login()`, `attempt()` methods to `StatefulGuard` and `Auth` interfaces
    -   Added `Session` facade interface with common methods (`invalidate()`, `regenerateToken()`, etc.)
    -   **Rule**: Always fix Intelephense errors by adding missing method definitions to `IntelephenseHelper.php`
-   **PSR-4 Autoloading Standards**: Added comprehensive PSR-4 autoloading rules
    -   Documented autoload mappings in `composer.json`
    -   **Rule**: Test support classes (models, helpers) MUST be in `tests/Support/` with proper namespaces
    -   **Rule**: Never define classes directly in test files - always create separate files in `tests/Support/`
    -   Moved `TestModel` from test file to `tests/Support/Models/TestModel.php` with namespace `Tests\Support\Models`
    -   Added examples of correct vs incorrect patterns
    -   **Rule**: All classes must comply with PSR-4 autoloading standards to prevent autoloader warnings

---

**Remember**: This file is a living document. Update it as the project evolves!
## Changelog

### 2025-12-22

-   **User Status and Login Tracking**: Implemented user active status and login tracking functionality
    -   **Database Fields**: Added `is_active` (boolean, default: `true`) and `last_login_at` (timestamp, nullable) to `users` table
    -   **BaseUserModel Enhancements**: Moved user status and login tracking methods to `BaseUserModel` for reuse across all authenticatable models
        -   `isActive()` - Check if user is active
        -   `activate()` - Activate the user
        -   `deactivate()` - Deactivate the user
        -   `updateLastLoginAt()` - Update last login timestamp (base implementation)
        -   `scopeActive()` - Query scope for active users
        -   `scopeInactive()` - Query scope for inactive users
    -   **User ID 1 Protection**: Moved boot method to `BaseUserModel` with automatic protection for user ID 1
        -   Prevents deletion of user ID 1
        -   Handles MySQL trigger for user ID 1 updates (requires `@laravel_user_id_1_self_edit` session variable)
        -   Only user ID 1 can edit themselves when authenticated
        -   System updates (like `last_login_at`) automatically bypass protection
    -   **Authentication Integration**:
        -   Inactive users cannot log in - `FortifyServiceProvider` checks `isActive()` before allowing authentication
        -   `last_login_at` is automatically updated on successful login via `SyncUserPreferencesOnLogin` listener
    -   **User Factory**: Added `is_active => true` to default factory state and `inactive()` state method
    -   **User Registration**: New users are created with `is_active => true` by default
    -   **DataTable Integration**:
        -   Added `is_active` filter and sortable field to `UsersDataTableConfig`
        -   Added bulk actions for activate/deactivate users
        -   Updated `UserDataTableTransformer` to include `is_active` and `last_login_at` in transformed data
    -   **Documentation**: Updated `AGENTS.md` with user status management and login tracking details

### 2025-01-XX

-   **DataTable Component Architecture**: Moved all PHP logic directly into component classes (`Datatable` and `Table`)
    -   **Component Classes**: `App\View\Components\Datatable` and `App\View\Components\Table` - All logic in component classes
    -   **Purpose**: Consolidates logic in component classes, providing methods that can be called from Blade templates
    -   **Features**:
        -   All component props accepted via constructor
        -   Service registries initialized once in constructor (DataTableComponentRegistry, DataTableFilterComponentRegistry)
        -   Public methods available for all processing and computed values
        -   On-demand processing in Blade templates using `@php` blocks (for performance)
        -   No pre-processing - rows/columns processed only when iterating
    -   **Key Methods**:
        -   **Datatable Component**: `getColumnsCount()`, `hasActionsPerRow()`, `getBulkActionsCount()`, `showBulkActionsDropdown()`, `hasFilters()`, `hasSelected()`, `showBulkBar()`, `hasPaginator()`, `processFilter()`, `getRowActionModalConfig()`, `getBulkActionModalConfig()`, `getModalStateId()`, `findActionByKey()`, and all getter methods
        -   **Table Component**: `processRow()`, `processColumn()`, `processHeaderColumn()`, `getColumnsCount()`, `hasActionsPerRow()`, and all getter methods
    -   **Updated Components**:
        -   `resources/views/components/datatable.blade.php` - Uses component methods directly, `@php` blocks for filter processing and modal configs
        -   `resources/views/components/table/table.blade.php` - Uses component methods in `@php` blocks for row/column processing
        -   `resources/views/components/table/header.blade.php` - Uses inline closure for header processing, accepts props directly
    -   **Benefits**:
        -   **Performance**: On-demand processing, no unnecessary pre-processing
        -   **Clarity**: All logic in component classes, easy to find
        -   **Standardization**: One pattern, no backward compatibility confusion
        -   **Flexibility**: `@php` blocks allowed for performance-critical loops
    -   **Removed**: `DataTableViewData` service class - all logic moved to component classes
    -   **Documentation**: Updated `docs/components.md` with new component-based architecture

### 2025-01-XX

-   **DataTable Preferences System**: Implemented comprehensive preferences system for DataTable components following the FrontendPreferences pattern
    -   **Service**: `App\Services\DataTable\DataTablePreferencesService` (singleton) with session-backed caching
    -   **Storage Strategy**:
        -   **Guests**: Preferences stored in session only
        -   **Authenticated users**: Preferences stored in `users.frontend_preferences` JSON column under keys like `datatable_preferences.users`, synced to session
        -   **Session as Single Source of Truth**: Session is always the single source of truth for reads (with automatic DB sync for authenticated users)
    -   **Preferences Stored**: All DataTable preferences (search, filters, per_page, sort) are automatically saved and loaded
    -   **Architecture**:
        -   `DataTablePreferencesService`: Main service (same pattern as `FrontendPreferencesService`)
        -   `SessionDataTablePreferencesStore`: Session-based storage
        -   `UserJsonDataTablePreferencesStore`: User JSON column storage
        -   `DataTablePreferencesStore` interface: Contract for storage implementations
    -   **Constants**: `App\Constants\DataTable\DataTable` for session keys, preference keys, and helper methods
    -   **Integration**:
        -   `BaseDataTableComponent` automatically loads preferences on mount
        -   Preferences are automatically saved when search, filters, per_page, or sort change
        -   `SessionService` uses `DataTablePreferencesService` to store all preferences
        -   Login listener (`SyncUserPreferencesOnLogin`) syncs all DataTable preferences from DB to session on login
    -   **Storage Structure**: Preferences stored in user's `frontend_preferences` JSON column:
        ```json
        {
            "locale": "en_US",
            "theme": "light",
            "datatable_preferences.users": {
                "search": "john",
                "per_page": 25,
                "sort": { "column": "name", "direction": "asc" },
                "filters": { "is_active": true, "email_verified_at": true }
            }
        }
        ```
    -   **Documentation**: Updated `docs/components.md` and `AGENTS.md` with DataTable preferences system details

### 2025-01-XX

-   **Authentication Code Refactoring**: Improved code quality, removed duplication, and enhanced separation of concerns

    -   **Created Authentication Helpers**: Added `app/helpers/auth-helpers.php` with centralized authentication helper functions
        -   `getIdentifierFromRequest()` - Centralizes identifier extraction logic (removes duplication)
        -   `setTeamSessionForUser()` - Centralizes team session setting logic (removes duplication)
    -   **Created Permission Helpers**: Added `app/helpers/permission-helpers.php` with centralized permission cache clearing
        -   `clearPermissionCache()` - Centralizes Spatie Permission cache clearing logic used across seeders
    -   **Seeder Refactoring**: Refactored all seeders to use `clearPermissionCache()` helper instead of inline cache clearing
        -   `RoleAndPermissionSeeder`: Separated concerns into `createPermissions()`, `createRoles()`, and `assignPermissionsToSuperAdmin()` methods
        -   `EssentialUserSeeder`: Uses helper for cache clearing
        -   `SampleUserSeeder`: Uses helper for cache clearing
    -   **Removed Duplication**: Eliminated duplicate identifier-to-email mapping logic from `FortifyServiceProvider` (middleware already handles it)
    -   **Code Organization**: Refactored `FortifyServiceProvider` to use helper functions, improving maintainability
    -   **Updated LoginRequest**: Now uses centralized `setTeamSessionForUser()` helper instead of inline logic
    -   **Improved PHPDoc**: Enhanced documentation throughout authentication code for better clarity
    -   **Composer Autoload**: Added `auth-helpers.php` and `permission-helpers.php` to Composer autoload files
    -   **Documentation**: Updated `AGENTS.md` with authentication and permission helpers documentation

-   **Super Admin Gate Pattern**: Implemented Spatie Permissions recommended Super-Admin pattern using `Gate::before()`
    -   **Implementation**: Added `Gate::before()` in `AppServiceProvider::boot()` to grant all permissions to users with `Roles::SUPER_ADMIN` role
    -   **Location**: `app/Providers/AppServiceProvider.php` (in `boot()` method)
    -   **Benefits**: Allows using permission-based controls (`@can()`, `$user->can()`) throughout the app without checking for Super Admin status everywhere
    -   **Best Practice**: Follows Spatie Permissions best practices - primarily check permissions, not roles
    -   **Gate Updates**: Enhanced existing Gate definitions in `TelescopeServiceProvider`, `HorizonServiceProvider`, and `LogViewerServiceProvider` to explicitly check for Super Admin role for clarity
    -   **Documentation**: Updated `docs/spatie-permission.md` and `AGENTS.md` with implementation details
    -   **Important Note**: Direct calls to `hasPermissionTo()`, `hasAnyPermission()`, etc. bypass the Gate and won't get Super Admin access - always use `can()` methods instead
    -   **Constants**: Uses `Roles::SUPER_ADMIN` constant (no hardcoded strings)

### 2025-12-23

-   **Notification System - Session Channel Security Enhancement**:
    -   **Converted session channel to public channel**: Changed from `private-notifications.session.{sessionId}` to `public-notifications.session.{sessionId}` for better security and simplicity
    -   **Security**: Session IDs are cryptographically random (40+ characters) and act as the security mechanism themselves
    -   **No authentication required**: Public channels don't require authentication, eliminating 403 errors on login/auth pages
    -   **No authorization overhead**: Public channels don't need authorization callbacks, making implementation cleaner
    -   **Error handling improvements**: Enhanced error handling for "Component not found" errors during Livewire navigation - these are now silently ignored as expected behavior
    -   **Code cleanup**: Removed all debug logs from production code while maintaining error logging for actual issues
    -   **Production ready**: System is now fully functional and tested, marked as production-ready in documentation
    -   **Updated documentation**: Added session channel security details, updated broadcasting channels section in `docs/notifications.md` and `AGENTS.md`

### 2025-01-XX

-   **Notification System Improvements**:
    -   **Fixed duplicate toast notifications**: Fixed issue where `toastCenter` component was creating duplicate subscriptions when re-initialized (e.g., during Livewire navigation). Changed from cleanup-based approach to idempotent subscription logic - component now checks if already subscribed and returns early instead of cleaning up and re-subscribing.
    -   **Added `toUserTeams()` method**: New method in `NotificationBuilder` to send notifications to all teams a user belongs to. Broadcasts to each team channel separately, or falls back to user channel if user has no teams. Supports persistence for all team members in each team.
    -   **Updated documentation**: Added `toUserTeams()` usage examples and updated broadcasting channels section in `docs/notifications.md` and `AGENTS.md`.

### 2025-01-XX

-   **Asset Management Optimization**: Refactored CSS/JS structure to avoid duplication and optimize bundle sizes
    -   **Created Base CSS**: Created `resources/css/base.css` containing all Tailwind/DaisyUI configuration (shared foundation)
    -   **Modular CSS Structure**:
        -   `app.css` imports `base.css` + `sidebar.css` (for authenticated app layout)
        -   `auth.css` imports only `base.css` (for authentication layout, smaller bundle)
        -   `sidebar.css` contains only component styles (no Tailwind imports)
    -   **Conditional Asset Loading**:
        -   App layout loads: `app.css`, `app.js`, `notification-center.js`
        -   Auth layout loads: `auth.css`, `app.js` (no sidebar styles or notification JS)
    -   **Benefits**:
        -   No CSS duplication (base styles shared via CSS imports)
        -   Smaller bundle sizes (auth pages don't load unnecessary assets)
        -   Maintainable (single source of truth for base styles)
        -   Uses Tailwind CSS v4's automatic `@import` bundling
    -   **Vite Configuration**: Updated entry points to `app.css`, `auth.css`, `app.js`, `notification-center.js`
    -   **Documentation**: Added comprehensive Asset Management section to `AGENTS.md` with file structure, loading patterns, and guidelines for adding new assets

### 2025-12-19

-   **Notification Dropdown Enhancements**: Improved dropdown state management and badge calculation
    -   **Badge Calculation**: Moved badge calculation from Blade template to Livewire computed property `getUnreadBadgeProperty()` (capped at "99+")
    -   **State Management**: Added Alpine.js reactive state (`isOpen` and `wasOpened`) to track dropdown open/close state
    -   **Auto-Mark as Read**: Notifications are now marked as read when the dropdown closes (via `@click.away`), but only if it was actually opened by the user
    -   **Persistent State**: The `dropdown-open` class is managed via Alpine.js `x-bind:class` to maintain state during Livewire updates
    -   **Badge Styling**: Updated badge to use `badge-xs` with `w-4 h-4` fixed size for smaller, cleaner appearance
    -   **Dropdown Component Enhancement**: Updated dropdown component to properly merge Alpine.js `x-bind:class` with static classes using `$attributes->merge()`
    -   **Notification Center**: Updated to use `#[Computed]` attribute instead of `getXxxProperty()` methods for better Livewire 4 compatibility

### 2025-01-XX

-   **Frontend Preferences System Refactoring**: Refactored `FrontendPreferencesService` to use session as single source of truth
    -   **Session-First Architecture**: Session is now always the single source of truth for all preference reads
    -   **Loading Flow**:
        -   Authenticated users: On first read, preferences are loaded from database and synced to session. Subsequent reads come from session.
        -   Guest users: All reads come from session
    -   **Update Flow**:
        -   Authenticated users: Database is updated first, then session is updated
        -   Guest users: Session is updated only
    -   **Benefits**:
        -   Single source of truth simplifies logic
        -   Fast reads from session (no database queries on every read)
        -   Preferences persist in database for authenticated users
        -   Database and session stay in sync for authenticated users
    -   **Implementation**:
        -   Removed `$persistentStore` property, replaced with `$sessionStore`
        -   Added `syncFromDatabaseIfNeeded()` method to sync DB preferences to session on first read
        -   Added `syncUserPreferencesToSession()` method to sync preferences for a specific user
        -   Updated `set()` and `setMany()` to update DB first for authenticated users, then session
        -   All reads now come from session after initial sync
    -   **Login Event Listener**: Created `SyncUserPreferencesOnLogin` listener class using `php artisan make:listener` to sync preferences from DB to session immediately on login
    -   **Tests**: All 21 FrontendPreferencesService tests pass (including login sync test), all 8 PreferencesController tests pass
    -   **Documentation**: Updated `AGENTS.md` with new architecture details, loading/update flows, and login event listener

### 2025-01-XX

-   **Sidebar CSS Cleanup**: Removed unused mobile menu classes

    -   **Removed Classes**: Removed `.sidebar-desktop` and `.sidebar-mobile` CSS classes from `resources/css/sidebar.css`
    -   **Removed from Blade**: Removed `.sidebar-desktop` class from `sidebar.blade.php` component
    -   **Responsive Behavior**: Responsive behavior is now handled entirely by DaisyUI's drawer component (`lg:drawer-open` class)
    -   **Simplified CSS**: CSS now only includes styles for `.sidebar-top-menus`, `.sidebar-bottom-menus`, and `.sidebar-user-menus`
    -   **Documentation**: Updated `AGENTS.md` to reflect removal of mobile menu classes

-   **BladeServiceProvider Refactoring**: Improved View Composer organization and data sharing

    -   **Method Organization**: Split into separate methods (`initLayoutVariables()`, `initPageTitle()`, `initPageSubtitle()`) for better maintainability
    -   **Value-Based Sharing**: Changed from sharing service objects (`$i18n`, `$preferences`, `$menuService`) to sharing specific values (`$htmlLangAttribute`, `$currentTheme`, `$sideBarTopMenus`, etc.)
    -   **Targeted Composers**: Removed wildcard patterns, using more specific view paths for better performance
    -   **Sidebar Menu Data**: Changed from sharing `$menuService` object to sharing specific menu arrays (`$sideBarTopMenus`, `$sideBarBottomMenus`, `$sideBarUserMenus`)
    -   **Layout Templates**: Updated to use specific values (`$htmlLangAttribute`, `$htmlDirAttribute`, `$currentTheme`) instead of service objects
    -   **Documentation**: Updated View Composers section to reflect new structure and shared variables

-   **Auth Layout Simplification**: Streamlined authentication layout structure

    -   **Removed Components**: Deleted `auth/card.blade.php` and `auth/simple.blade.php` components
    -   **Single Layout**: Now only uses `auth/split.blade.php` component for all authentication pages
    -   **Component Structure**: `split.blade.php` changed from full HTML document to component-only (removed DOCTYPE, html, head, body tags)
    -   **Layout Wrapper**: `auth.blade.php` now wraps `split.blade.php` component instead of `simple.blade.php`
    -   **Route Updates**: Changed logo links from `route('home')` to `route('dashboard')` in split layout
    -   **File Structure**:
        -   `resources/views/components/layouts/auth.blade.php` - Main auth layout wrapper
        -   `resources/views/components/layouts/auth/split.blade.php` - Split-screen auth component

-   **App Layout Simplification**: Cleaned up app layout structure

    -   **Removed Nested Main**: Removed nested `<main>` tag from `app.blade.php` (moved to `sidebar.blade.php`)
    -   **Value-Based Variables**: Changed from service objects to specific values (`$htmlLangAttribute`, `$currentTheme`)
    -   **Simplified Structure**: Cleaner component hierarchy

-   **I18nService Enhancement**: Removed default fallback values

    -   **No Defaults**: `getDefaultLocale()` and `getFallbackLocale()` methods no longer have hardcoded fallback values
    -   **Config Required**: These methods now rely entirely on `config('i18n.*')` values
    -   **Better Error Handling**: Ensures configuration is properly set

-   **BasePageComponent Enhancement**: Added subtitle support to page title management system

    -   **Page Subtitle**: `BasePageComponent` now supports optional `$pageSubtitle` property alongside `$pageTitle`
    -   **Automatic Sharing**: Subtitles are automatically shared via `View::share()` in `boot()` method, just like titles
    -   **Translation Support**: Subtitles support translation keys (containing dots) - automatically translated via `__()`
    -   **Header Display**: Header component (`components.layouts.app.header`) now conditionally displays subtitles below the title
    -   **View Composers**: `BladeServiceProvider` updated to share `$pageSubtitle` with header and head partials
    -   **Usage**: Set `public string $pageSubtitle = 'ui.pages.example.description';` property in components (optional)
    -   **Updated Components**: Settings pages (profile, password, two-factor) now use subtitles for better UX
    -   **Documentation**: Updated `AGENTS.md` with subtitle usage examples and requirements

-   **Sidebar Component Refactoring**: Unified sidebar structure for better maintainability
    -   **Unified Component**: Removed separate `desktop-menu.blade.php` and `mobile-menu.blade.php` components
    -   **Single Component**: Created unified `sidebar-menus.blade.php` component (`<x-layouts.app.sidebar-menus />`)
    -   **Removed Mobile Menu Classes**: Removed `.sidebar-desktop` and `.sidebar-mobile` CSS classes - responsive behavior is handled by DaisyUI's drawer component
    -   **File Structure**:
        -   `resources/views/components/layouts/app/sidebar.blade.php` - Main wrapper (`<x-layouts.app.sidebar>`)
        -   `resources/views/components/layouts/app/sidebar-menus.blade.php` - Unified menu component (`<x-layouts.app.sidebar-menus />`)
        -   `resources/views/components/layouts/app/header.blade.php` - Header component (`<x-layouts.app.header />`)
    -   **View Composers**: Sidebar now uses View Composers (in `BladeServiceProvider`) to inject menu data automatically
    -   **No Props Needed**: Removed need to pass menu data as props - data is automatically available via View Composers
    -   **Integrated Navbar**: Mobile menu toggle is now integrated directly into the navbar within `sidebar.blade.php`
    -   **Simplified Structure**: Cleaner component hierarchy with fewer files to maintain
    -   **Updated Documentation**: Updated sidebar component documentation to reflect unified structure with exact file paths and component references

### 2025-01-XX

-   **Automatic Page Title Management**: Implemented automatic page title management system using `$pageTitle` variable
    -   **BasePageComponent**: Created `App\Livewire\BasePageComponent` base class for all full-page Livewire components
    -   **Title Resolution**: Supports static (component property) and controller (view data) methods
    -   **Translations**: Automatic translation support - translation keys (containing dots) are automatically translated via `__()`
    -   **View Composer**: Added View Composer in `BladeServiceProvider` to share `$pageTitle` with `partials.head` and `components.layouts.app.header`
    -   **SPA Navigation**: Full support for `wire:navigate` with automatic title updates via `View::share()` in component's `boot()` method
    -   **Seamless**: Uses `boot()` lifecycle hook - no need to call `parent::mount()`
    -   **Rule**: ALL full-page Livewire components MUST extend `BasePageComponent` (not `Livewire\Component`)
    -   **Usage**: Set `public ?string $pageTitle = 'ui.pages.example';` property in components (use translation keys)
    -   **Translation Files**: Added `ui.pages.*` section to translation files (`lang/en_US/ui.php`, `lang/fr_FR/ui.php`)
    -   **Updated Components**: All existing Livewire page components now extend `BasePageComponent` and use translation keys
    -   **Documentation**: Updated `AGENTS.md` with BasePageComponent requirement, translation usage, and usage examples
    -   **Later Enhanced**: Added `$pageSubtitle` support for optional subtitle text displayed below page titles in header

### 2025-01-XX

-   **Icon Component Refactoring**: Converted icon component from Livewire to Blade component
    -   **Converted to Blade Component**: Changed from Livewire component (`⚡dynamic-icon-island.blade.php`) to regular Blade component (`ui/icon.blade.php`)
    -   **Moved to UI Folder**: Component is now located at `resources/views/components/ui/icon.blade.php`
    -   **Updated Usage**: All references changed from `<livewire:dynamic-icon-island>` to `<x-ui.icon>`
    -   **Added Security**: Implemented input validation and sanitization for icon names, pack names, and CSS classes
    -   **Size Support**: Added support for predefined sizes (xs, sm, md, lg, xl) for backward compatibility
    -   **Performance**: Removed Livewire overhead for static icon rendering (no reactivity needed)
    -   **Dependency Injection**: Uses `@inject` directive to inject `IconPackMapper` service
    -   **Updated Documentation**: Navigation system documentation updated to reflect new icon component usage

### 2025-01-XX

-   **BasePageComponent Enhancement**: Added subtitle support to page title management system

    -   **Page Subtitle**: `BasePageComponent` now supports optional `$pageSubtitle` property alongside `$pageTitle`
    -   **Automatic Sharing**: Subtitles are automatically shared via `View::share()` in `boot()` method, just like titles
    -   **Translation Support**: Subtitles support translation keys (containing dots) - automatically translated via `__()`
    -   **Header Display**: Header component (`components.layouts.app.header`) now conditionally displays subtitles below the title
    -   **View Composers**: `BladeServiceProvider` updated to share `$pageSubtitle` with header and head partials
    -   **Usage**: Set `public string $pageSubtitle = 'ui.pages.example.description';` property in components (optional)
    -   **Updated Components**: Settings pages (profile, password, two-factor) now use subtitles for better UX
    -   **Documentation**: Updated `AGENTS.md` with subtitle usage examples and requirements

-   **Sidebar Component Refactoring**: Unified sidebar structure for better maintainability

    -   **Unified Component**: Removed separate `desktop-menu.blade.php` and `mobile-menu.blade.php` components
    -   **Single Component**: Created unified `sidebar-menus.blade.php` component
    -   **Removed Mobile Menu Classes**: Removed `.sidebar-desktop` and `.sidebar-mobile` CSS classes - responsive behavior is handled by DaisyUI's drawer component
    -   **View Composers**: Sidebar now uses View Composers (in `BladeServiceProvider`) to inject menu data automatically
    -   **No Props Needed**: Removed need to pass menu data as props - data is automatically available via View Composers
    -   **Integrated Navbar**: Mobile menu toggle is now integrated directly into the navbar within `sidebar.blade.php`
    -   **Simplified Structure**: Cleaner component hierarchy with fewer files to maintain
    -   **Updated Documentation**: Updated sidebar component documentation to reflect unified structure

-   **Navigation System Refactoring**: Simplified navigation system and split sidebar components
    -   **Removed form/button support** from `NavigationItem`: Form and button methods (`form()`, `button()`) have been removed. Use static forms in Blade templates for actions like logout.
    -   **Removed class property**: The `class()` method has been removed from `NavigationItem`. Use `attributes(['class' => '...'])` instead.
    -   **Attributes as array**: `NavigationItem` now returns attributes as an array (not a string) for use with `$attributes->merge()` in Blade components.
    -   **Service injection**: Updated to use View Composers instead of `@inject` directive for automatic menu data injection.
    -   **Semantic HTML**: Navigation components now use `<div>` elements instead of `<ul>`/`<li>` for better flexibility.
    -   **Static logout form**: Logout is now handled as a static form in the sidebar components, not through `NavigationItem`.
    -   **Updated tests**: Removed `NavigationItemFormTest.php` as form/button functionality no longer exists. Test count: 24 tests, 52 assertions.

### 2025-01-XX

-   **Livewire 4 Folder Structure Reorganization**: Removed `livewire/` directory to align with Livewire 4 philosophy
    -   Moved auth views from `livewire/auth/` to `pages/auth/` (full-page components)
    -   Moved nested components from `livewire/settings/` to `components/settings/` (reusable components)
    -   Updated `FortifyServiceProvider` to reference new auth view paths (`pages.auth.*`)
    -   Removed `livewire` from `component_locations` in `config/livewire.php`
    -   **New Structure**:
        -   Full-page components: `resources/views/pages/` (use `pages::` namespace)
        -   Nested/reusable Livewire components: `resources/views/components/` (referenced directly, e.g., `livewire:settings.delete-user-form`)
        -   Regular Blade components: `resources/views/components/`
    -   Since Livewire is the default in Livewire 4, no separate `livewire/` folder is needed

### 2025-12-13

-   **Livewire 4 Folder Structure Migration**: Completed migration to Livewire 4 folder structure
    -   Moved full-page components from `livewire/settings/` to `pages/settings/` with `.blade.php` extension
    -   Updated routes to use `pages::settings.*` namespace format
    -   Created Livewire layouts in `resources/views/layouts/` with `@livewireStyles` and `@livewireScripts`
    -   Created Blade component wrappers in `resources/views/components/layouts/` for regular views
    -   Updated `config/livewire.php` to include `pages` in `component_locations` and `component_namespaces`
    -   All single-file components now use `.blade.php` extension (required by Livewire 4)

### 2025-01-XX

-   **Livewire 4 Comprehensive Documentation**: Created comprehensive `docs/livewire-4.md` with AI-friendly indexing system
    -   Added detailed AI-friendly index at the top with quick reference by topic and search keywords
    -   Comprehensive coverage of all Livewire 4 features: Components, Properties, Actions, Forms, Events, Lifecycle Hooks, Nesting, Testing, AlpineJS Integration, Navigation, Islands, Lazy Loading, Loading States, Validation, File Uploads, Pagination, URL Query Parameters, File Downloads, Teleport, Morphing, Hydration, Synthesizers, JavaScript, Troubleshooting, Security, CSP
    -   Each section includes code examples, usage patterns, and cross-references
    -   Search keywords section for AI assistants to quickly locate specific functionality
    -   Organized by core concepts, advanced features, validation & data, UI & interaction, advanced technical, testing & troubleshooting, and security & configuration
    -   **Documentation**: See `docs/livewire-4.md` for complete Livewire 4 documentation with AI-friendly indexing

### 2025-12-13

-   **Logging Configuration**: Configured daily log rotation with level-specific folders and exact level filtering
    -   Each log level (emergency, alert, critical, error, warning, notice, info, debug) now has its own folder: `storage/logs/{level}/laravel-{date}.log`
    -   Daily rotation enabled for all level-specific channels using Monolog's RotatingFileHandler
    -   **Exact level filtering**: Each log file contains ONLY messages of its exact level using Monolog's FilterHandler
    -   Created `App\Logging\LevelSpecificLogChannelFactory` to handle exact level filtering with daily rotation
    -   Deprecated logs configured with daily rotation in `storage/logs/deprecations/laravel-{date}.log`
    -   Default stack channel routes to all level-specific channels
    -   Retention configurable via `LOG_DAILY_DAYS` environment variable (default: 14 days)
-   **Constants and Code Reusability Rule**: Added critical rule for using constants and avoiding duplication
    -   Created `App\Constants\LogLevels` class for log level constants
    -   Created `App\Constants\LogChannels` class for log channel constants
    -   Refactored `config/logging.php` to use constants and helper function to eliminate duplication
    -   Added rule to agent.md: Always use constants instead of hardcoded strings when possible, and always avoid duplication for easy maintenance
-   **Log Clearing Command**: Created `php artisan logs:clear` command
    -   Clears all log files from `storage/logs` directory
    -   Supports `--level` option to clear logs for a specific level only
    -   Uses constants from `LogChannels` class
    -   Provides helpful feedback with Laravel Prompts

### 2025-12-16

-   **Frontend Preferences System**: Implemented centralized frontend preferences service for managing user preferences (locale, theme, timezone)

    -   **Service**: `App\Services\FrontendPreferences\FrontendPreferencesService` (singleton) with session-backed caching
    -   **Storage**: Guest users store preferences in session; authenticated users persist to `users.frontend_preferences` JSON column
    -   **Performance**: First request loads from DB into session cache; subsequent reads use session cache only
    -   **Middleware**: `ApplyFrontendPreferences` automatically applies locale and timezone preferences on each request
    -   **UI Components**: Language and theme switchers (`<x-preferences.locale-switcher />`, `<x-preferences.theme-switcher />`) in app/auth layouts
    -   **Constants**: `App\Constants\Preferences\FrontendPreferences` for session keys, preference keys, defaults, validation
    -   **Database**: Added `frontend_preferences` JSON column to `users` table with array cast
    -   **Removed**: Settings → Appearance page (theme switcher moved to header/sidebar)

-   **Theme Management**: Switched from client-side `localStorage` to server-side `data-theme` attribute
-   **Auto-Detection**: Automatic browser language detection on first visit (server-side only, no JavaScript)
    -   Language detection from `Accept-Language` header using `$request->header('Accept-Language')`
    -   **No theme detection** - Default theme preference is `"light"` for first-time visitors
    -   **No JavaScript required** - All detection is server-side using request headers
    -   **No cookies used** - All preferences stored in session (guests) or database (authenticated users)
    -   Detection only occurs when no preferences are set (first visit)
    -   Detected preferences are automatically saved and persisted
-   **Comprehensive Tests**: 31 tests covering service, middleware, UI components, and auto-detection behavior
-   **Documentation**: Added Frontend Preferences section to `AGENTS.md` and locale switching info to `docs/internationalization.md`

-   **DateTime and Currency Helper Functions**: Created locale-aware helper functions for formatting dates, times, and currency
    -   Created `app/helpers/dateTime.php` with `formatDate()`, `formatTime()`, and `formatDateTime()` functions
    -   Created `app/helpers/currency.php` with `formatCurrency()` function
    -   Updated `config/i18n.php` to include `symbol_position`, `decimal_separator`, and `thousands_separator` for currency configuration
    -   All helpers use `I18nService` internally instead of direct config access
    -   Added comprehensive tests (18 tests for dateTime, 14 tests for currency)
    -   Updated `composer.json` to autoload new helper files
    -   Updated documentation (`docs/internationalization.md`) with helper function usage
-   **I18nService Enhancements**: Enhanced `I18nService` with additional methods for centralized locale management
    -   Added `getSupportedLocales()`, `getDefaultLocale()`, `getFallbackLocale()`
    -   Added `getLocaleMetadata(?string $locale)`, `isLocaleSupported()`, `getValidLocale()`
    -   Updated service to use its own methods internally for consistency
    -   Added comprehensive tests (18 tests)
-   **BladeServiceProvider**: Created dedicated service provider for Blade/view-related functionality
    -   Moved View Composer logic from `AppServiceProvider` to `BladeServiceProvider`
    -   Shares `I18nService` with layout templates via View Composers
    -   Shares `SideBarMenuService` only with sidebar template
    -   Replaced all `@inject` directives with View Composers
    -   Added comprehensive tests (4 tests)
-   **Code Style Rules**: Added new rules to `AGENTS.md`
    -   Always use function guards and early returns
    -   Do NOT use `function_exists()` checks in helper files
    -   Always use `I18nService` for locale-related code
    -   Use View Composers instead of `@inject` for global data

### 2025-12-23

-   **Modal Components (Class-Based + Theme-Aware)**: Converted modal Blade components to class-based components and removed inline Blade `@php` logic
    -   **Base Modal**: `App\View\Components\Ui\BaseModal` + `resources/views/components/ui/base-modal.blade.php`
        -   Theme-aware backdrop (`bg-base-*` + `backdrop-blur-*`)
        -   Single `placement` prop with 9-position grid (`top-left` … `bottom-right`) and responsive default (bottom on mobile, center on `sm+`)
    -   **Confirm Modal**: `App\View\Components\Ui\ConfirmModal` + `resources/views/components/ui/confirm-modal.blade.php`
        -   Keeps event-driven confirmation UX (`confirm-modal` event) while delegating structure to `<x-ui.base-modal>`

### 2025-01-XX

-   **Dual Authentication System**: Implemented email and username login support

    -   **User Model**: Added `findByIdentifier()` method to support lookup by email or username
    -   **Middleware**: Created `MapLoginIdentifier` middleware to map `identifier` field to `email` for Fortify validation compatibility
    -   **Service Provider**: Refactored `FortifyServiceProvider` with separated concerns:
        -   `configureAuthentication()` - Custom authentication logic supporting both email and username
        -   `configureAuthenticationPipeline()` - Custom pipeline with conditional `CanonicalizeUsername` skip for usernames
        -   `getLoginView()` - Environment-based login view (production: text input, development: user dropdown)
        -   `getDevelopmentUsers()` - Helper to fetch users for development dropdown
        -   `formatUserLabel()` - Helper to format user labels for dropdown display
    -   **Rate Limiting**: Enhanced to support both `identifier` and `email` fields
    -   **Team Context**: Automatically sets `team_id` in session on successful login
    -   **Code Quality**: Removed all debug logs, extracted helper methods, improved separation of concerns
    -   **Documentation**: Updated `AGENTS.md` with dual authentication details and middleware documentation

-   **Livewire 4 Upgrade**: Upgraded from Livewire v3 + Volt to Livewire v4 (beta) with built-in single-file components
    -   Updated `composer.json` to require `livewire/livewire:^4.0@beta` and removed `livewire/volt`
    -   Converted all Volt components to Livewire 4 single-file components (replaced `Livewire\Volt\Component` with `Livewire\Component`)
    -   Updated routes from `Volt::route()` to `Route::livewire()` (preferred method in Livewire 4)
    -   Removed `VoltServiceProvider` and updated `bootstrap/providers.php`
    -   **Folder Structure Reorganization**:
        -   Moved full-page components to `resources/views/pages/` with `pages::` namespace
        -   Created `resources/views/layouts/` for Livewire page layouts (with `@livewireStyles`/`@livewireScripts`)
        -   Created Blade component wrappers in `resources/views/components/layouts/` for regular views
        -   Updated `config/livewire.php` with proper `component_locations` and `component_namespaces`
    -   **File Extensions**: All single-file components must use `.blade.php` extension (not `.php`)
    -   Created comprehensive `docs/livewire-4.md` documentation file
    -   Updated agent.md to reflect Livewire 4 patterns and reference documentation
    -   **Documentation**: See `docs/livewire-4.md` for complete Livewire 4 documentation, upgrade guide, and best practices
-   Initial agent.md creation
-   Documented stable configuration patterns
-   Documented Redis client environment-based selection
-   Documented project structure and conventions
-   Added environment helper functions (`app/helpers/app-helpers.php`)
    -   Functions: `appEnv()`, `isProduction()`, `isDevelopment()`, `isStaging()`, `isLocal()`, `isTesting()`, `inEnvironment()`
    -   Updated config files to use helper functions instead of direct config checks
-   **UUID Requirement**: All tables must have a UUID column
    -   Updated all existing migrations to include UUID columns
    -   Added rule for future development: all new tables must include `$table->uuid('uuid')->unique()->index();`
-   **Automatic UUID Generation**: Implemented `HasUuid` trait and base model classes
    -   Created `App\Models\Concerns\HasUuid` trait that automatically generates unique UUIDs
    -   Created `App\Models\Base\BaseModel` base class for regular models (includes HasUuid)
    -   Created `App\Models\Base\BaseUserModel` base class for authenticatable models (includes HasUuid, HasFactory, Notifiable)
    -   Updated User model to extend `BaseUserModel`
    -   UUIDs are generated on model creation and checked for uniqueness
    -   Models using base classes use UUID as route key name
    -   Added comprehensive tests for UUID generation
    -   **Rule**: All new models must extend `App\Models\Base\BaseModel` or `App\Models\Base\BaseUserModel` instead of Eloquent base classes
-   **Soft Delete Requirement**: Implemented soft deletes for all models by default
    -   Added `SoftDeletes` trait to `BaseModel` and `BaseUserModel` base classes
    -   Updated migrations to include `$table->softDeletes();` for: `users`, `teams`, `permissions`, `roles`, `notifications`
    -   Added `SoftDeletes` trait to `Permission` and `Role` models (extend Spatie's models)
    -   **Exceptions**: `PasswordResetToken` model extends `Model` directly (not `BaseModel`) to avoid soft deletes, as password reset tokens are temporary and should be hard deleted
    -   **Rule**: All new models must have soft deletes enabled by default via base classes
    -   **Rule**: All new migrations must include `$table->softDeletes();` unless the table is an exception (temporary tokens, pivot tables, monitoring tables)
-   **Intelephense Helper**: Added rule and documentation for fixing Intelephense errors
    -   Updated `IntelephenseHelper.php` with missing Auth and Session facade methods
    -   Added `logout()`, `login()`, `attempt()` methods to `StatefulGuard` and `Auth` interfaces
    -   Added `Session` facade interface with common methods (`invalidate()`, `regenerateToken()`, etc.)
    -   **Rule**: Always fix Intelephense errors by adding missing method definitions to `IntelephenseHelper.php`
-   **PSR-4 Autoloading Standards**: Added comprehensive PSR-4 autoloading rules
    -   Documented autoload mappings in `composer.json`
    -   **Rule**: Test support classes (models, helpers) MUST be in `tests/Support/` with proper namespaces
    -   **Rule**: Never define classes directly in test files - always create separate files in `tests/Support/`
    -   Moved `TestModel` from test file to `tests/Support/Models/TestModel.php` with namespace `Tests\Support\Models`
    -   Added examples of correct vs incorrect patterns
    -   **Rule**: All classes must comply with PSR-4 autoloading standards to prevent autoloader warnings

---

**Remember**: This file is a living document. Update it as the project evolves!
