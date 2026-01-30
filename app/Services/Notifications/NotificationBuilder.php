<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\Toast\ToastAnimation;
use App\Enums\Toast\ToastPosition;
use App\Enums\Toast\ToastType;
use App\Events\Notifications\ToastBroadcasted;
use App\Models\Notification;
use App\Models\Team;
use App\Models\User;
use App\Services\IconPackMapper;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class NotificationBuilder
{
    /**
     * Title can be a plain string or ['key' => 'translation.key', 'params' => [...]]
     */
    protected string|array|null $title = null;

    /**
     * Subtitle can be a plain string or ['key' => 'translation.key', 'params' => [...]]
     */
    protected string|array|null $subtitle = null;

    protected ?NotificationContent $content = null;

    protected ToastType $type = ToastType::Success;

    protected ToastPosition $position = ToastPosition::TopRight;

    protected ToastAnimation $animation = ToastAnimation::Slide;

    protected bool $persist = false;

    protected ?string $userId = null;

    protected ?string $teamId = null;

    protected bool $global = false;

    protected bool $userTeams = false;

    protected ?string $link = null;

    protected bool $enableSound = false;

    protected bool $sticky = false;

    /**
     * Create a new notification builder instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a new notification builder instance (factory method).
     *
     * @return static A new notification builder instance
     */
    public static function make(): static
    {
        return new static;
    }

    /**
     * Set the notification title (required).
     * Auto-detects translation keys: if params provided or Lang::has() is true, stores for deferred translation.
     *
     * @param  string  $title  The notification title or translation key
     * @param  array<string, mixed>|null  $params  Translation parameters (if provided, title is treated as a key)
     */
    public function title(string $title, ?array $params = null): static
    {
        $this->title = $this->resolveTranslatableValue($title, $params);

        return $this;
    }

    /**
     * Set the notification subtitle (optional).
     * Auto-detects translation keys: if params provided or Lang::has() is true, stores for deferred translation.
     *
     * @param  string|null  $subtitle  The notification subtitle or translation key
     * @param  array<string, mixed>|null  $params  Translation parameters (if provided, subtitle is treated as a key)
     */
    public function subtitle(?string $subtitle, ?array $params = null): static
    {
        $this->subtitle = $subtitle !== null ? $this->resolveTranslatableValue($subtitle, $params) : null;

        return $this;
    }

    /**
     * Set the notification content as a string.
     * Auto-detects translation keys: if params provided or Lang::has() is true, stores for deferred translation.
     *
     * @param  string  $content  The notification content or translation key
     * @param  array<string, mixed>|null  $params  Translation parameters (if provided, content is treated as a key)
     */
    public function content(string $content, ?array $params = null): static
    {
        $resolved = $this->resolveTranslatableValue($content, $params);
        $this->content = NotificationContent::string($resolved);

        return $this;
    }

    /**
     * Resolve a value to string or translatable array.
     * If params provided, it's definitely a translation key.
     * If no params but Lang::has() returns true, treat as translation key.
     * Otherwise, treat as plain string.
     *
     * @param  array<string, mixed>|null  $params
     * @return string|array{key: string, params: array<string, mixed>}
     */
    protected function resolveTranslatableValue(string $value, ?array $params): string|array
    {
        // If params provided, definitely a translation key
        if ($params !== null) {
            return ['key' => $value, 'params' => $params];
        }

        // Check if it looks like a translation key (Lang::has)
        if (Lang::has($value)) {
            return ['key' => $value, 'params' => []];
        }

        // Plain string
        return $value;
    }

    /**
     * Get the translated/resolved value for a field.
     */
    protected function resolveValue(string|array|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (\is_array($value) && isset($value['key'])) {
            return __($value['key'], $value['params'] ?? []);
        }

        return $value;
    }

    /**
     * Set the notification content as HTML (trusted).
     *
     * @param  string|Htmlable|HtmlString  $html  The notification content as HTML
     */
    public function html(string|Htmlable|HtmlString $html): static
    {
        $this->content = NotificationContent::html($html);

        return $this;
    }

    /**
     * Set the notification content from a Blade view.
     *
     * @param  string  $view  The Blade view name
     * @param  array<string, mixed>  $data  Data to pass to the view
     */
    public function view(string $view, array $data = []): static
    {
        $this->content = NotificationContent::view($view, $data);

        return $this;
    }

    /**
     * Set the notification type to success.
     */
    public function success(): static
    {
        $this->type = ToastType::Success;

        return $this;
    }

    /**
     * Set the notification type to info.
     */
    public function info(): static
    {
        $this->type = ToastType::Info;

        return $this;
    }

    /**
     * Set the notification type to warning.
     */
    public function warning(): static
    {
        $this->type = ToastType::Warning;

        return $this;
    }

    /**
     * Set the notification type to error.
     */
    public function error(): static
    {
        $this->type = ToastType::Error;

        return $this;
    }

    /**
     * Set the notification type to classic.
     */
    public function classic(): static
    {
        $this->type = ToastType::Classic;

        return $this;
    }

    /**
     * Make the toast sticky (won't auto-dismiss, user must click X).
     *
     * Useful for error messages that require user acknowledgment.
     */
    public function sticky(): static
    {
        $this->sticky = true;

        return $this;
    }

    /**
     * Set the toast position.
     *
     * @param  ToastPosition  $position  The toast position on the screen
     */
    public function position(ToastPosition $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Set the toast animation.
     *
     * @param  ToastAnimation  $animation  The toast animation type
     */
    public function animation(ToastAnimation $animation): static
    {
        $this->animation = $animation;

        return $this;
    }

    /**
     * Enable persistence to database (creates DatabaseNotification).
     */
    public function persist(): static
    {
        $this->persist = true;

        return $this;
    }

    /**
     * Send to a specific user.
     *
     * @param  User|Authenticatable|string  $user  The user instance, authenticatable, or UUID string
     */
    public function toUser(User|Authenticatable|string $user): static
    {
        $this->userId = $user instanceof User ? $user->uuid : (\is_string($user) ? $user : $user->getAuthIdentifier());
        $this->teamId = null;
        $this->global = false;
        $this->userTeams = false;

        return $this;
    }

    /**
     * Send to a specific team.
     *
     * @param  Team|string  $team  The team instance or UUID string
     */
    public function toTeam(Team|string $team): static
    {
        $this->teamId = $team instanceof Team ? $team->uuid : $team;
        $this->userId = null;
        $this->global = false;
        $this->userTeams = false;

        return $this;
    }

    /**
     * Send globally (all users).
     */
    public function global(): static
    {
        $this->global = true;
        $this->userId = null;
        $this->teamId = null;
        $this->userTeams = false;

        return $this;
    }

    /**
     * Send to all teams of a user.
     * If no user is provided, uses the current authenticated user.
     *
     * @param  User|Authenticatable|string|null  $user  The user instance, authenticatable, or UUID string. If null, uses the current authenticated user.
     *
     * @throws RuntimeException If no user is provided and no authenticated user is available
     */
    public function toUserTeams(User|Authenticatable|string|null $user = null): static
    {
        if ($user === null) {
            $user = Auth::user();
            if (! $user instanceof User) {
                throw new RuntimeException('Cannot send to user teams: no authenticated user available.');
            }
        }

        $this->userId = $user instanceof User ? $user->uuid : (\is_string($user) ? $user : $user->getAuthIdentifier());
        $this->teamId = null;
        $this->global = false;
        $this->userTeams = true;

        return $this;
    }

    /**
     * Set an optional link URL for the notification.
     *
     * @param  string  $link  The URL to link to when the notification is clicked
     */
    public function link(string $link): static
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Enable or disable notification sound.
     *
     * @param  bool  $enable  Whether to enable sound (default: true)
     */
    public function enableSound(bool $enable = true): static
    {
        $this->enableSound = $enable;

        return $this;
    }

    /**
     * Get whether notification sound is enabled.
     *
     * @return bool True if sound is enabled, false otherwise
     */
    protected function getEnableNotificationSound(): bool
    {
        return $this->enableSound;
    }

    /**
     * Send the notification (broadcast toast + optionally persist).
     *
     * @throws InvalidArgumentException If the notification title is missing or empty
     * @throws RuntimeException If the notification channel cannot be determined
     */
    public function send(): void
    {
        // Resolve title to string for validation
        $resolvedTitle = $this->resolveValue($this->title);
        if ($resolvedTitle === null || \trim($resolvedTitle) === '') {
            throw new InvalidArgumentException('Notification title is required.');
        }

        // Render icon HTML server-side
        $iconHtml = $this->renderIconForType($this->type);

        // Create toast payload with resolved values (strings for immediate display)
        $payload = new ToastPayload(
            title: $resolvedTitle,
            subtitle: $this->resolveValue($this->subtitle),
            content: $this->content?->render(),
            type: $this->type,
            position: $this->position,
            animation: $this->animation,
            link: $this->link,
            iconHtml: $iconHtml,
            enableSound: $this->getEnableNotificationSound(),
            sticky: $this->sticky,
        );

        // Handle user teams (multiple teams)
        if ($this->userTeams) {
            $this->sendToUserTeams($payload);

            return;
        }

        // Determine the target channel for single target
        $channel = $this->determineChannel();

        // Always broadcast the toast
        event(new ToastBroadcasted($payload, $channel));

        // For session channel, also store in session as fallback
        // This ensures notifications aren't lost during redirects (WebSocket messages are real-time)
        if (\str_starts_with($channel, 'public-notifications.session.')) {
            session()->push('pending_toast_notifications', $payload->toArray());
        }

        // Optionally persist to database
        if ($this->persist) {
            $this->persistToDatabase($payload);
        }
    }

    /**
     * Render icon HTML for the given toast type.
     *
     * @param  ToastType  $type  The toast type
     * @return string The rendered icon HTML
     */
    protected function renderIconForType(ToastType $type): string
    {
        $iconMapper = app(IconPackMapper::class);

        $iconNames = [
            ToastType::Success->value => 'check-circle',
            ToastType::Info->value => 'information-circle',
            ToastType::Warning->value => 'exclamation-triangle',
            ToastType::Error->value => 'x-circle',
            ToastType::Classic->value => 'bell',
        ];

        $iconName = $iconNames[$type->value] ?? 'bell';

        return $iconMapper->renderIcon($iconName, 'heroicons', 'h-6 w-6');
    }

    /**
     * Send notification to all teams of a user.
     *
     * @param  ToastPayload  $payload  The toast payload to send
     *
     * @throws RuntimeException If the user ID is not available or the user is not found
     */
    protected function sendToUserTeams(ToastPayload $payload): void
    {
        $targetUserId = $this->userId ?? Auth::id();
        if (! $targetUserId) {
            throw new RuntimeException('Cannot send to user teams: no user ID available.');
        }

        $user = \is_string($targetUserId) ? User::where('uuid', $targetUserId)->first() : User::find($targetUserId);
        if (! $user) {
            throw new RuntimeException('Cannot send to user teams: user not found.');
        }

        // Get all teams for the user
        $teams = $user->teams()->get();

        if ($teams->isEmpty()) {
            // If user has no teams, fall back to user channel
            $channel = "private-notifications.user.{$user->uuid}";
            event(new ToastBroadcasted($payload, $channel));

            if ($this->persist) {
                $this->persistUser($user, $this->prepareNotificationData($payload));
            }

            return;
        }

        // Prepare notification data once if persistence is enabled
        $notificationData = $this->persist ? $this->prepareNotificationData($payload) : null;

        // Broadcast to each team channel
        foreach ($teams as $team) {
            $channel = "private-notifications.team.{$team->uuid}";
            event(new ToastBroadcasted($payload, $channel));

            // Persist to each team if needed
            if ($this->persist && $notificationData !== null) {
                $this->persistTeamForUserTeams($team, $notificationData);
            }
        }
    }

    /**
     * Determine the broadcast channel based on current settings.
     *
     * @return string The broadcast channel name
     *
     * @throws RuntimeException If no session ID is available
     */
    protected function determineChannel(): string
    {
        if ($this->global) {
            return 'private-notifications.global';
        }

        if ($this->teamId) {
            return "private-notifications.team.{$this->teamId}";
        }

        if ($this->userId) {
            return "private-notifications.user.{$this->userId}";
        }

        // Default to current user if authenticated
        $user = Auth::user();
        if ($user instanceof User) {
            return "private-notifications.user.{$user->uuid}";
        }

        // Fallback to current session (default when no user context is available)
        // Always get current session ID dynamically
        // This ensures it works even after session invalidation (new session will be created)
        // Uses PUBLIC channel - session ID itself acts as security mechanism (cryptographically random)
        $currentSessionId = session()->getId();
        if (! $currentSessionId) {
            throw new RuntimeException('Cannot determine notification channel: no session ID available.');
        }

        return "public-notifications.session.{$currentSessionId}";
    }

    /**
     * Persist notification to database.
     *
     * @param  ToastPayload  $payload  The toast payload to persist
     */
    protected function persistToDatabase(ToastPayload $payload): void
    {
        $notificationData = $this->prepareNotificationData($payload);

        if ($this->global) {
            // For global notifications, notify all users
            $this->persistGlobal($notificationData);
        } elseif ($this->teamId) {
            // For team notifications, notify all team members
            $this->persistTeam($notificationData);
        } else {
            // For user notifications
            $targetUserId = $this->userId ?? Auth::id();
            if ($targetUserId) {
                $this->persistUser($targetUserId, $notificationData);
            }
        }
    }

    /**
     * Persist notification for a specific user.
     *
     * @param  int|string|User  $userId  The user ID, UUID, or User instance
     * @param  array<string, mixed>  $notificationData  The notification data array
     */
    protected function persistUser(int|string|User $userId, array $notificationData): void
    {
        $user = $userId instanceof User ? $userId : User::find($userId);
        if (! $user) {
            return;
        }

        $notificationData['notifiable_type'] = User::class;
        $notificationData['notifiable_id'] = $user->id;

        Notification::query()->create($notificationData);
    }

    /**
     * Persist notification for all team members.
     *
     * @param  array<string, mixed>  $notificationData  The notification data array
     */
    protected function persistTeam(array $notificationData): void
    {
        $team = Team::where('uuid', $this->teamId)->first();
        if (! $team) {
            return;
        }

        $this->persistTeamForUserTeams($team, $notificationData);
    }

    /**
     * Persist notification for a specific team instance.
     *
     * @param  Team  $team  The team instance
     * @param  array<string, mixed>  $notificationData  The notification data array
     */
    protected function persistTeamForUserTeams(Team $team, array $notificationData): void
    {
        $userIds = $team->users()->pluck('users.id');

        if ($userIds->isEmpty()) {
            return;
        }

        foreach (array_chunk($userIds->all(), 100) as $chunk) {
            foreach ($chunk as $userId) {
                // For manual creation, we generate a UUID for the 'uuid' column
                // The 'id' column will be auto-generated by the database
                $notificationData['uuid'] = (string) Str::uuid();
                $notificationData['notifiable_type'] = User::class;
                $notificationData['notifiable_id'] = $userId;
                unset($notificationData['id']); // Ensure we don't try to set the ID column

                // Manually create the notification
                Notification::query()->create($notificationData);
            }
        }
    }

    /**
     * Prepare notification data array from payload.
     * Stores translatable data for deferred translation at render time.
     *
     * @param  ToastPayload  $payload  The toast payload (used for type and link)
     * @return array<string, mixed> The prepared notification data array
     */
    protected function prepareNotificationData(ToastPayload $payload): array
    {
        $data = [
            'type' => $payload->type->value,
            'link' => $payload->link,
        ];

        // Store title - either as translatable array or plain string
        if (\is_array($this->title) && isset($this->title['key'])) {
            $data['title'] = __($this->title['key'], $this->title['params'] ?? []); // For backwards compatibility
            $data['titleKey'] = $this->title['key'];
            $data['titleParams'] = $this->title['params'];
        } else {
            $data['title'] = $this->title;
        }

        // Store subtitle - either as translatable array or plain string
        if (\is_array($this->subtitle) && isset($this->subtitle['key'])) {
            $data['subtitle'] = __($this->subtitle['key'], $this->subtitle['params'] ?? []);
            $data['subtitleKey'] = $this->subtitle['key'];
            $data['subtitleParams'] = $this->subtitle['params'];
        } else {
            $data['subtitle'] = $this->subtitle;
        }

        // Store content - use storable format for deferred rendering
        if ($this->content) {
            $storable = $this->content->toStorable();
            $data['content'] = $this->content->render(); // For backwards compatibility
            $data['contentStorable'] = $storable;
        } else {
            $data['content'] = null;
        }

        return [
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\GeneralNotification',
            'data' => $data,
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Persist notification for all users (global).
     *
     * @param  array<string, mixed>  $notificationData  The notification data array
     */
    protected function persistGlobal(array $notificationData): void
    {
        $userIds = User::pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        foreach (array_chunk($userIds->all(), 100) as $chunk) {
            foreach ($chunk as $userId) {
                // For manual creation, we generate a UUID for the 'uuid' column
                // The 'id' column will be auto-generated by the database
                $notificationData['uuid'] = (string) Str::uuid();
                $notificationData['notifiable_type'] = User::class;
                $notificationData['notifiable_id'] = $userId;
                unset($notificationData['id']); // Ensure we don't try to set the ID column

                Notification::query()->create($notificationData);
            }
        }
    }
}
