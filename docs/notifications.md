# Notification System

The application includes a comprehensive notification system that supports both **toast notifications** (temporary UI messages) and **persistent notifications** (stored in the database). All notifications are broadcast via Laravel Reverb for real-time delivery.

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
```

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
use App\Enums\ToastPosition;

NotificationBuilder::make()
    ->title('Bottom Right Notification')
    ->position(ToastPosition::BottomRight)
    ->send();
```

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

### Team Channel

Send notifications to all members of a team:

```php
NotificationBuilder::make()
    ->title('Team Update')
    ->toTeam($teamUuid)
    ->send();
```

### Global Channel

Send notifications to all authenticated users:

```php
NotificationBuilder::make()
    ->title('System Maintenance')
    ->toGlobal()
    ->send();
```

### Specific User Channel

Send notifications to a specific user:

```php
NotificationBuilder::make()
    ->title('Direct Message')
    ->toUser($userUuid)
    ->send();
```

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
-   Filter by read/unread status
-   Sort by creation date (newest first)

## UI Components

### Toast Center

The toast center component (`<x-notifications.toast-center />`) is automatically included in the app layout and handles displaying all toast notifications. It:

-   Subscribes to user, team, and global notification channels
-   Displays toasts with appropriate animations
-   Auto-dismisses toasts after 5 seconds
-   Supports click interactions (if a link is provided)

### Notification Dropdown

The notification dropdown in the header shows:

-   Last 5 notifications (sorted by unread first)
-   Unread count indicator (red dot with ping animation)
-   Quick access to notification center

Component: `<livewire:notifications.dropdown lazy>`

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

The system automatically converts Laravel Fortify status messages to toast notifications via the `ConvertStatusToNotification` middleware. This ensures all authentication-related messages (password reset, email verification, etc.) are displayed as toasts.

Common status messages handled:

-   `verification-link-sent` → Info toast
-   `password-reset` → Success toast
-   `password-reset-link-sent` → Info toast

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
-   **Singleton pattern** for subscription management to prevent duplicate subscriptions

### Automatic Mark as Read

Persistent notifications are automatically marked as read when:

-   User visits the notification center
-   User clicks on a notification
-   Notification comes into viewport (Intersection Observer)

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
