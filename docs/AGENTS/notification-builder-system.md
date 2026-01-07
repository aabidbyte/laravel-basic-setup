## Notification Builder System

The project includes a comprehensive notification system with a fluent API similar to the Navigation Builder pattern. The system supports both toast notifications (temporary UI messages) and persistent notifications (stored in the database), all broadcast via Laravel Reverb for real-time delivery.

**Status**: ✅ **Production Ready** - The notification system is fully functional and tested. All features work correctly including session-based notifications for post-logout scenarios.

**Documentation**: See `docs/notifications.md` for complete documentation, usage examples, and best practices.

### Architecture

```
app/Services/Notifications/
├── NotificationBuilder.php        # Fluent builder for creating notifications
├── ToastPayload.php                # DTO for toast notification data
└── NotificationContent.php         # Content wrapper (string/html/view)

app/Enums/
└── Toast/
    ├── ToastType.php               # Toast type enum (success, info, warning, error, classic)
    ├── ToastPosition.php           # Toast position enum (top-right, top-left, etc.)
    └── ToastAnimation.php          # Toast animation enum (slide, etc.)

app/Events/
└── Notifications/
    └── ToastBroadcasted.php        # Broadcast event for toast notifications

resources/views/
├── components/notifications/
│   └── toast-center.blade.php      # Alpine.js toast UI component
└── pages/notifications/
    └── ⚡index.blade.php            # Notification center page (Livewire 4)
```

### Key Classes

**NotificationBuilder** (`app/Services/Notifications/NotificationBuilder.php`):

-   Fluent builder for creating notifications
-   **Default behavior**: Toast-only, success type, current user channel
-   Methods: `make()`, `title()`, `subtitle()`, `content()`, `html()`, `view()`, `success()`, `info()`, `warning()`, `error()`, `classic()`, `position()`, `animation()`, `persist()`, `toUser()`, `toTeam()`, `toUserTeams()`, `global()`, `link()`, `send()`
-   **Title is required**: Must call `title()` before `send()`
-   **Content support**: String, HTML (trusted), or Blade view via `view()`
-   **Persistence**: Call `persist()` to save to database (creates DatabaseNotification records)
-   **Channels**: Defaults to current user, or use `toUser()`, `toTeam()`, `toUserTeams()`, `global()`

**ToastPayload** (`app/Services/Notifications/ToastPayload.php`):

-   DTO for toast notification data
-   Contains: title, subtitle, content, type, position, animation, link
-   Serializes to array for JSON broadcasting

**ToastBroadcasted** (`app/Events/Notifications/ToastBroadcasted.php`):

-   Implements `ShouldBroadcastNow` for immediate broadcasting
-   Broadcasts to private channels: `private-notifications.user.{uuid}`, `private-notifications.team.{uuid}`, `private-notifications.global`
-   Broadcasts to public channel: `public-notifications.session.{sessionId}` (for session-based notifications)
-   Automatically selects channel type based on channel name (public for session channels, private for others)
-   Event name: `toast.received`

### Translation Support

The builder supports passing translation keys directly to `title()` and `subtitle()`.
- The `lang:sync` command automatically detects these methods.
- You do NOT need to wrap keys in `__()` unless you need complex logic.
- You can pass parameters as the second argument.

```php
// Detected by lang:sync automatically
NotificationBuilder::make()
    ->title('ui.messages.success')
    ->subtitle('ui.messages.details', ['count' => 5])
    ->send();
```

### Usage Examples

```php
use App\Services\Notifications\NotificationBuilder;
use App\Enums\Toast\ToastType;
use App\Enums\Toast\ToastPosition;

// Simple toast notification (toast-only, current user)
NotificationBuilder::make()
    ->title('Task completed')
    ->success()
    ->send();

// Toast with subtitle and content
NotificationBuilder::make()
    ->title('New message')
    ->subtitle('From John Doe')
    ->content('Hello, how are you?')
    ->info()
    ->send();

// Persistent notification with link
NotificationBuilder::make()
    ->title('Payment received')
    ->persist()
    ->link('/payments/123')
    ->toUser($user)
    ->send();

// Team notification
NotificationBuilder::make()
    ->title('Team update')
    ->persist()
    ->toTeam($team)
    ->warning()
    ->send();

// User teams notification (sends to all teams a user belongs to)
NotificationBuilder::make()
    ->title('Update for your teams')
    ->persist()
    ->toUserTeams() // Uses current authenticated user
    ->info()
    ->send();

// User teams notification for specific user
NotificationBuilder::make()
    ->title('Welcome to all your teams')
    ->toUserTeams($user)
    ->success()
    ->send();

// Global notification
NotificationBuilder::make()
    ->title('System maintenance')
    ->persist()
    ->global()
    ->error()
    ->send();

// Using Blade view for content
NotificationBuilder::make()
    ->title('Custom notification')
    ->view('notifications.custom', ['data' => $data])
    ->send();
```

### Toast UI Component

**Toast Center** (`resources/views/components/notifications/toast-center.blade.php`):

-   Alpine.js component that subscribes to Echo private channels
-   Alpine store + helpers live in `resources/js/notification-center.js`
-   Automatically included in authenticated app layout
-   **Idempotent subscriptions**: Uses idempotent subscription logic to prevent duplicate subscriptions when components are re-initialized (e.g., during Livewire navigation)
-   Features:
    -   Subscribes to user, team, and global channels
    -   Renders toasts using DaisyUI alert components
    -   **Client-side icon rendering**: Uses `<x-ui.icon>` with logic-based visibility (`x-show`) for CSP safety
    -   **Safe content rendering**: Uses `x-text` instead of `x-html`
    -   Auto-dismisses after 5 seconds
    -   Supports click-to-navigate via link
    -   Slide animation (enters from right, exits to right)

### Notification Dropdown Component (Split Architecture)

The notification dropdown uses a **split architecture** for better UX during navigation:

1. **Static Blade Trigger** (`resources/views/components/notifications/dropdown-trigger.blade.php`):
   - Pure Blade component that stays visible during navigation
   - Contains bell icon button and unread badge
   - Initial badge count injected from PHP (server-rendered)
   - Uses `notificationDropdownTrigger` Alpine component for state

2. **Lazy-Loaded Content** (`resources/views/components/notifications/⚡dropdown-content.blade.php`):
   - Livewire 4 SFC (lazy-loaded inside the dropdown)
   - Component: `<livewire:notifications.dropdown-content lazy>`
   - Shows last 5 notifications (sorted by unread first, then by creation date)
   - Marks notifications as read when dropdown closes
   - Uses `notificationDropdownContent` Alpine component

**Usage in Layout:**
```blade
<x-notifications.dropdown-trigger></x-notifications.dropdown-trigger>
```

**Benefits:**
- Bell icon and badge remain visible during SPA navigation
- Content is lazy-loaded only when needed
- Better UX without loading placeholder flicker

### Notification Center Page

**Notification Center** (`resources/views/pages/notifications/⚡index.blade.php`):

-   Livewire 4 single-file component extending `BasePageComponent`
-   Route: `/notifications` (named: `notifications.index`)
-   Features:
    -   Lists all user notifications (shows 10 newest by default, with "Load more" button)
    -   Uses `#[Computed]` attribute for computed properties (`notifications()`, `unreadCount()`, `totalCount()`)
    -   Auto-marks as read on viewport intersection (`x-intersect.once`)
    -   Marks as read on click
    -   "Mark all as read" button
    -   "Clear all" button (shows total count)
    -   Shows unread badge for unread notifications
    -   Displays notification type icons, title, subtitle, content, link, and timestamp
    -   Refreshes in real-time when new notifications are broadcast (via Alpine notifications store fan-out in `resources/js/notification-center.js`)

### Broadcast Channels

Channels are defined in `routes/channels.php`:

-   **User channel** (`private-notifications.user.{userUuid}`): Authorized for matching user UUID
-   **Team channel** (`private-notifications.team.{teamUuid}`): Authorized for team members
-   **User teams channel**: When using `toUserTeams()`, broadcasts to each team channel the user belongs to (or falls back to user channel if user has no teams)
-   **Global channel** (`private-notifications.global`): Authorized for any authenticated user
-   **Session channel** (`public-notifications.session.{sessionId}`): **Public channel** - Session ID acts as security mechanism (cryptographically random, 40+ characters). No authentication required, perfect for post-logout scenarios. Used as default fallback when no user context is available.

### Database Notification Refresh

For real-time UI refresh when the `notifications` table changes, the app broadcasts a dedicated event:

-   `App\Events\Notifications\DatabaseNotificationChanged` (broadcast name: `notification.changed`)
-   Triggered via `App\Observers\Notifications\NotificationObserver` observing `App\Models\Notification`

## Channels

-   **Toast channel**: Dispatches `ToastBroadcasted` event (Pusher)
-   **User channel**: Creates single `Notification` for target user
-   **Team channel**: Creates `Notification` for each team member
-   **User teams channel**: Creates `Notification` for each team member in each team the user belongs to (or single notification for user if no teams)
-   **Global channel**: Creates `Notification` for all users (batched inserts)
-   **Session channel**: Stores notification in session as fallback (not persisted to database, as there's no user to associate with)

Notifications are stored in Laravel's standard `notifications` table with:

-   `id`: UUID primary key
-   `type`: Notification class name (string)
-   `notifiable_type` / `notifiable_id`: Polymorphic relationship to User
-   `data`: JSON containing title, subtitle, content, type, link
-   `read_at`: Timestamp when marked as read (null if unread)

### Pruning

-   Command: `php artisan notifications:prune-read` (default: 30 days)
-   Scheduled: Runs daily (configured in `routes/console.php`)
-   Behavior: Deletes read notifications where `read_at < now()->subDays(30)`
-   Unread notifications are never pruned

### Teams Integration

-   All users are automatically assigned to a personal team on registration
-   Team UUID is available for team channel notifications
-   Team ID is stored in session on login (for TeamsPermission middleware compatibility)

### Rules & Best Practices

-   **Always use NotificationBuilder**: Don't manually create DatabaseNotification records
-   **Title required**: Must always call `title()` before `send()`
-   **Toast-first**: All notifications broadcast toasts; persistence is optional
-   **Channel selection**: Default to current user unless you need team/global
-   **Session fallback**: Session channel is automatically used when no user context is available (e.g., after logout/deletion)
-   **Content rendering**: Use `view()` for complex content, `html()` for trusted HTML, `content()` for plain strings
-   **Persistence**: Only use `persist()` when notifications need to be reviewable later. Note: Session channel notifications are not persisted to database (stored in session only).
-   **Security**: Session channel uses public channel with session ID as security mechanism - session IDs are cryptographically random and extremely hard to guess
-   **Testing**: Use `Event::fake([ToastBroadcasted::class])` to test notifications without broadcasting

