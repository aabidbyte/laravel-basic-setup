# Notification System

The application includes a comprehensive notification system that supports both **toast notifications** (temporary UI messages) and **persistent notifications** (stored in the database). All notifications are broadcast via Laravel Reverb for real-time delivery.

**Status**: ✅ **Production Ready** - The notification system is fully functional and tested. All features work correctly including session-based notifications for post-logout scenarios.

## Overview

The notification system provides:

-   **Toast Notifications**: Temporary UI messages that appear and auto-dismiss after 5 seconds
-   **Persistent Notifications**: Stored in the database and displayed in the notification center
-   **Real-time Broadcasting**: All notifications are broadcast via Laravel Reverb for instant delivery
-   **Multiple Channels**: Support for user-specific, team-specific, and global notification channels
-   **Customizable Appearance**: Toast type (success, info, warning, error, classic), position, and animations

## Architecture

### Notification Builder

The `NotificationBuilder` class provides a fluent API for creating and sending notifications:

```php
use App\Services\Notifications\NotificationBuilder;

// Simple toast notification
NotificationBuilder::make()
    ->title('Profile updated successfully')
    ->success()
    ->send();

// Toast with subtitle and content
NotificationBuilder::make()
    ->title('New Message')
    ->subtitle('From John Doe')
    ->content('You have a new message waiting for you')
    ->info()
    ->send();

// Persistent notification (stored in database)
NotificationBuilder::make()
    ->title('Task Assigned')
    ->subtitle('You have been assigned a new task')
    ->content('Please review the task details')
    ->success()
    ->persist()
    ->send();

// Custom position and animation
NotificationBuilder::make()
    ->title('Notification')
    ->position(ToastPosition::BottomCenter)
    ->animation(ToastAnimation::Slide)
    ->send();

// Silent notification (no sound)
NotificationBuilder::make()
    ->title('Background Task')
    ->enableSound(false)
    ->send();
```

### Available Methods

The `NotificationBuilder` provides the following fluent methods:

-   **`title(string $title)`**: Set the notification title (required)
-   **`subtitle(?string $subtitle)`**: Set optional subtitle
-   **`content(string $content)`**: Set plain text content
-   **`html(string|\Illuminate\Contracts\Support\Htmlable $html)`**: Set HTML content (trusted)
-   **`view(string $view, array $data = [])`**: Set content from a Blade view
-   **`success()`**: Set toast type to success (green)
-   **`info()`**: Set toast type to info (blue)
-   **`warning()`**: Set toast type to warning (yellow)
-   **`error()`**: Set toast type to error (red)
-   **`classic()`**: Set toast type to classic (default styling)
-   **`position(ToastPosition $position)`**: Set toast position on screen
-   **`animation(ToastAnimation $animation)`**: Set toast animation type
-   **`persist()`**: Enable persistence to database
-   **`toUser(User|string $user)`**: Send to specific user
-   **`toTeam(Team|string $team)`**: Send to specific team
-   **`toUserTeams(User|string|null $user = null)`**: Send to all teams of a user
-   **`global()`**: Send to all authenticated users
-   **`link(string $link)`**: Set optional link URL
-   **`enableSound(bool $enable = true)`**: Enable or disable sound playback (default: enabled)
-   **`send()`**: Send the notification (broadcast + optionally persist)

## Toast Types

The system supports five toast types, each with its own icon and styling:

-   **Success** (`success`): Green, check-circle icon (default)
-   **Info** (`info`): Blue, information-circle icon
-   **Warning** (`warning`): Yellow, exclamation-triangle icon
-   **Error** (`error`): Red, x-circle icon
-   **Classic** (`classic`): Default styling, bell icon

```php
NotificationBuilder::make()->title('Success')->success()->send();
NotificationBuilder::make()->title('Info')->info()->send();
NotificationBuilder::make()->title('Warning')->warning()->send();
NotificationBuilder::make()->title('Error')->error()->send();
NotificationBuilder::make()->title('Classic')->classic()->send();
```

### Icon Rendering

Icons are **rendered server-side only** using the `IconPackMapper` service and included in the toast payload as HTML. This ensures:

-   **Consistent rendering**: Icons are rendered using the same service as the rest of the application
-   **No client-side binding issues**: Avoids Alpine.js binding conflicts with Blade components
-   **Immediate display**: Icons are ready when the toast is received, no client-side rendering needed
-   **Server-side only**: All icons must be rendered on the server - no client-side fallback is available

The icon mapping is handled automatically by `NotificationBuilder::renderIconForType()`, which uses `IconPackMapper` to render the appropriate Heroicon for each toast type.

## Toast Positions

Toasts can be positioned anywhere on the screen:

-   `ToastPosition::TopRight` (default)
-   `ToastPosition::TopLeft`
-   `ToastPosition::TopCenter`
-   `ToastPosition::BottomRight`
-   `ToastPosition::BottomLeft`
-   `ToastPosition::BottomCenter`
-   `ToastPosition::Center`

```php
use App\Enums\Toast\ToastPosition;

NotificationBuilder::make()
    ->title('Bottom Right Notification')
    ->position(ToastPosition::BottomRight)
    ->send();
```

## Sound Support

Toast notifications support optional sound playback. Sound is **enabled by default** and can be controlled via the `enableSound()` method:

```php
// Sound enabled by default (will play)
NotificationBuilder::make()
    ->title('Task completed')
    ->success()
    ->send();

// Explicitly enable sound
NotificationBuilder::make()
    ->title('New message')
    ->enableSound(true)
    ->send();

// Disable sound
NotificationBuilder::make()
    ->title('Silent notification')
    ->enableSound(false)
    ->send();
```

The sound is played automatically when a toast notification is received, but only if `enableSound` is `true`. The sound URL is configured in the client-side JavaScript (`resources/js/notification-center.js`).

## Toast Animations

Currently, the system supports slide animations for all positions. The animation direction is automatically determined based on the toast position:

-   **Top positions**: Slide down from top
-   **Bottom positions**: Slide up from bottom
-   **Left positions**: Slide in from left
-   **Right positions**: Slide in from right
-   **Center position**: Scale in/out

## Broadcasting Channels

Notifications can be sent to different channels:

### User Channel (Default)

Notifications are sent to the current authenticated user by default:

```php
NotificationBuilder::make()
    ->title('Personal Notification')
    ->send(); // Sent to current user
```

### Specific User Channel

Send notifications to a specific user:

```php
NotificationBuilder::make()
    ->title('Direct Message')
    ->toUser($userUuid)
    ->send();
```

### Team Channel

Send notifications to all members of a team:

```php
NotificationBuilder::make()
    ->title('Team Update')
    ->toTeam($teamUuid)
    ->send();
```

### User Teams Channel

Send notifications to all teams that a user belongs to. This broadcasts to each team channel the user is a member of. If the user has no teams, it falls back to the user's personal channel:

```php
// Send to all teams of the current authenticated user
NotificationBuilder::make()
    ->title('Update for Your Teams')
    ->toUserTeams()
    ->send();

// Send to all teams of a specific user
NotificationBuilder::make()
    ->title('Update for Your Teams')
    ->toUserTeams($user)
    ->send();
```

**Note**: When using `toUserTeams()`, the notification is broadcast to each team channel separately. If persistence is enabled, the notification is persisted for each team member in each team.

### Global Channel

Send notifications to all authenticated users:

```php
NotificationBuilder::make()
    ->title('System Maintenance')
    ->global()
    ->send();
```

### Session Channel (Default Fallback)

The session channel is automatically used as a fallback when no user context is available (e.g., after logout or user deletion). This ensures notifications can still be delivered to the current browser session even when the user is not authenticated.

**Automatic Behavior**: If no specific channel is set (no `toUser()`, `toTeam()`, or `global()`), and no authenticated user is available, the notification will automatically use the current session channel.

```php
// After user deletion (no user context available)
NotificationBuilder::make()
    ->title('Account deleted successfully')
    ->info()
    ->send(); // Automatically uses session channel (public)
```

**Channel Type**: The session channel uses a **public channel** (`public-notifications.session.{sessionId}`) instead of a private channel. This is more secure and simpler because:

- **Security**: Session IDs are cryptographically random (40+ characters) and extremely hard to guess
- **No Authentication Required**: Public channels don't require authentication, making them perfect for post-logout scenarios
- **Session ID as Security**: The session ID itself acts as the security mechanism - only the browser with that specific session ID can subscribe
- **No Authorization Overhead**: No need for custom authorization routes or middleware

**Session Fallback Storage**: When a notification is sent to the session channel, it is also stored in the session as a fallback mechanism. This ensures notifications aren't lost during redirects, since WebSocket broadcasts are real-time and may be missed if the client subscribes after the broadcast.

- Notifications are stored in `session('pending_toast_notifications')` as an array
- On page load, pending notifications are automatically retrieved and displayed
- After display, pending notifications are cleared from the session using `session()->pull()`

**Frontend Subscription**:
- **Auth pages** (login, register, etc.): Only subscribe to session channel (public)
- **App pages** (authenticated): Subscribe to user, team, global (private), and session (public) channels
- **Pending Notifications**: On page load, any pending notifications from the session are automatically processed and displayed

This ensures notifications are delivered correctly in both authenticated and non-authenticated contexts, even during redirects.

## Persistent Notifications

Persistent notifications are stored in the database and displayed in the notification center. They are automatically marked as read when the user interacts with them (visits, clicks, or views in viewport).

### Creating Persistent Notifications

```php
NotificationBuilder::make()
    ->title('Task Completed')
    ->subtitle('Your task has been marked as complete')
    ->content('Task details...')
    ->success()
    ->persist() // Makes it persistent
    ->send();
```

### Automatic Cleanup

Persistent notifications are automatically pruned 30 days after being marked as read. This is handled by a scheduled command:

```bash
php artisan notifications:prune
```

## Notification Center

The notification center displays all persistent notifications for the authenticated user. Access it via:

-   **Route**: `/notifications`
-   **Navigation**: Notification dropdown in the header (shows last 5 notifications) or direct link to notification center

### Features

-   View all notifications
-   Mark notifications as read (automatic on interaction)
-   Mark all notifications as read
-   Refreshes in real-time when new notifications are broadcast
-   Filter by read/unread status
-   Sort by creation date (newest first)

## UI Components

### Toast Center

The toast center component (`<x-notifications.toast-center />`) is automatically included in the app layout and handles displaying all toast notifications. It:

-   Subscribes to user, team, and global notification channels
-   Displays toasts with appropriate animations
-   Auto-dismisses toasts after 5 seconds
-   Supports click interactions (if a link is provided)
-   Uses Alpine helpers from `resources/js/notification-center.js`

### Notification Dropdown

The notification dropdown uses a **split architecture** for better UX during SPA navigation:

-   Last 5 notifications (sorted by unread first)
-   Unread count badge (shows count up to 99, displays "99+" if over 99)
-   Quick access to notification center
-   Automatically marks visible notifications as read when closed

**Components:**

1.  **Static Trigger** (`x-notifications.dropdown-trigger`): Pure Blade wrapper with bell icon and badge (stays visible during navigation)
2.  **Lazy Content** (`livewire:notifications.dropdown-content lazy`): Livewire SFC with notification list (lazy-loaded)

**Key Features:**

-   **No Flicker During Navigation**: Trigger button stays in DOM during `wire:navigate`
-   **Server-Rendered Badge**: Initial count from PHP, updated via events
-   **State Management**: Uses `notificationDropdownTrigger` and `notificationDropdownContent` Alpine components

### Realtime UI Configuration (Centralized)

The realtime UI (toast center, notification dropdown refresh, notification center refresh) is configured via:

-   A View Composer shared value: `$notificationRealtimeConfig` (user UUID + team UUIDs)
-   An Alpine store initialized once in the app layout: `$store.notifications.init(...)`
-   A single Echo subscription fan-out in `resources/js/notification-center.js`

### Database-Backed Refresh Events

UI refreshes for the dropdown + notification center are driven by **database notification model changes**, not toast broadcasts.

-   **Event**: `App\Events\Notifications\DatabaseNotificationChanged` (broadcast name: `notification.changed`)
-   **Observer**: `App\Observers\Notifications\NotificationObserver` (hooks into `App\Models\Notification`)
-   **Registration**: Observer is registered in `App\Providers\AppServiceProvider`

This ensures UI stays in sync when notifications are created, updated (ex: marked read), or deleted.

## Usage Examples

### Profile Update

```php
use App\Services\Notifications\NotificationBuilder;

public function updateProfileInformation(): void
{
    // Update profile...
    $user->save();

    NotificationBuilder::make()
        ->title(__('ui.settings.profile.save_success'))
        ->success()
        ->send();
}
```

### Silent Notification (No Sound)

```php
NotificationBuilder::make()
    ->title('Background Task Completed')
    ->subtitle('Task finished silently')
    ->success()
    ->enableSound(false) // Disable sound for this notification
    ->send();
```

### Password Reset

```php
NotificationBuilder::make()
    ->title(__('ui.auth.reset_password.success'))
    ->success()
    ->send();
```

### Task Assignment (Persistent)

```php
NotificationBuilder::make()
    ->title('New Task Assigned')
    ->subtitle('Task: Review document')
    ->content('You have been assigned a new task. Please review it at your earliest convenience.')
    ->info()
    ->persist()
    ->link(route('tasks.show', $task))
    ->send();
```

### User Teams Notification

Send a notification to all teams a user belongs to:

```php
// Notify all teams of the current user about a project update
NotificationBuilder::make()
    ->title('Project Update')
    ->subtitle('New milestone reached')
    ->content('The project has reached a new milestone. Check it out!')
    ->success()
    ->persist()
    ->toUserTeams() // Sends to all teams of current user
    ->link(route('projects.show', $project))
    ->send();

// Notify all teams of a specific user
NotificationBuilder::make()
    ->title('Welcome to All Your Teams')
    ->subtitle('You have been added to multiple teams')
    ->info()
    ->toUserTeams($user)
    ->send();
```

### Error Notification

```php
try {
    // Some operation...
} catch (\Exception $e) {
    NotificationBuilder::make()
        ->title('Operation Failed')
        ->subtitle('An error occurred')
        ->content($e->getMessage())
        ->error()
        ->send();
}
```

## Integration with Laravel Fortify

The system sends toast notifications directly from custom Fortify response classes instead of using session status messages. This ensures all authentication-related messages (password reset, email verification, etc.) are displayed as toasts for both authenticated and guest users.

Custom response classes handle:

-   **Email Verification Link Sent** (`EmailVerificationNotificationSentResponse`) → Info toast
-   **Password Reset** (`PasswordResetResponse`) → Success toast
-   **Password Reset Link Sent** (`PasswordResetLinkSentResponse`) → Info toast

These response classes are registered in `FortifyServiceProvider` and send notifications via the session channel, making them available to guest users as well.

## Technical Details

### Broadcast Event

All notifications are broadcast via the `ToastBroadcasted` event, which:

-   Implements `ShouldBroadcastNow` for immediate broadcasting
-   Uses private channels for security
-   Broadcasts with the event name `toast.received`

### Database Storage

Persistent notifications use Laravel's built-in notification system and are stored in the `notifications` table. Each notification:

-   Has a unique UUID
-   Is associated with a user (via `notifiable`)
-   Stores notification data as JSON
-   Tracks read status via `read_at` timestamp

### Client-Side Implementation

The toast center uses:

-   **Alpine.js** for UI reactivity and transitions
-   **Laravel Echo** for WebSocket subscriptions
-   **Idempotent subscriptions**: The toast center uses idempotent subscription logic to prevent duplicate subscriptions when components are re-initialized (e.g., during Livewire navigation)
-   **Server-rendered icons**: Icons are rendered server-side only and included in the toast payload as HTML, avoiding client-side binding issues
-   **Sound playback**: Optional sound playback when notifications are received (enabled by default, controlled via `enableSound()`)

### Icon Rendering Architecture

Icons are rendered **server-side only** in the `NotificationBuilder` before broadcasting:

1.  **Server-side rendering**: `NotificationBuilder::renderIconForType()` uses `IconPackMapper` to render the appropriate icon HTML
2.  **Payload inclusion**: The rendered icon HTML is included in the `ToastPayload` as the `iconHtml` property
3.  **Client-side display**: The toast center component uses `x-html` to display the server-rendered icon HTML directly
4.  **No fallback**: Icons must be rendered on the server - there is no client-side fallback rendering

This architecture ensures consistent icon rendering across the application and avoids issues with Alpine.js binding to Blade components. All icon rendering logic has been removed from the client-side JavaScript.

### Automatic Mark as Read

Persistent notifications are automatically marked as read when:

-   **Notification Dropdown**: When the dropdown is closed (via `@click.away`), all visible notifications are marked as read, but only if the dropdown was actually opened by the user
-   **Notification Center**: When a notification comes into viewport (Intersection Observer) or when user clicks on it
-   **Individual Interactions**: User clicks on a notification link

## Testing

The notification system includes comprehensive tests covering:

-   Notification builder functionality
-   Toast broadcasting
-   Persistent notification storage
-   Channel routing
-   Auto-cleanup of old notifications

Run tests with:

```bash
php artisan test --filter=Notification
```

## History

### Session Channel Security Enhancement (2025-12-23)

- **Converted session channel to public channel**: Changed from `private-notifications.session.{sessionId}` to `public-notifications.session.{sessionId}` for better security and simplicity
- **Security**: Session IDs are cryptographically random (40+ characters) and act as the security mechanism themselves
- **No authentication required**: Public channels don't require authentication, eliminating 403 errors on login/auth pages
- **No authorization overhead**: Public channels don't need authorization callbacks, making implementation cleaner
- **Error handling improvements**: Enhanced error handling for "Component not found" errors during Livewire navigation
- **Production ready**: System is now fully functional and tested, marked as production-ready

### Notification System Improvements (2025-01-XX)

- **Fixed duplicate toast notifications**: Fixed issue where `toastCenter` component was creating duplicate subscriptions when re-initialized (e.g., during Livewire navigation). Changed from cleanup-based approach to idempotent subscription logic.
- **Added `toUserTeams()` method**: New method in `NotificationBuilder` to send notifications to all teams a user belongs to. Broadcasts to each team channel separately, or falls back to user channel if user has no teams.

### Notification Dropdown Enhancements (2025-12-19)

- **Badge Calculation**: Moved badge calculation from Blade template to Livewire computed property `getUnreadBadgeProperty()` (capped at "99+")
- **State Management**: Added Alpine.js reactive state (`isOpen` and `wasOpened`) to track dropdown open/close state
- **Auto-Mark as Read**: Notifications are now marked as read when the dropdown closes (via `@click.away`), but only if it was actually opened by the user
- **Notification Center**: Updated to use `#[Computed]` attribute instead of `getXxxProperty()` methods for better Livewire 4 compatibility
