<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\ToastAnimation;
use App\Enums\ToastPosition;
use App\Enums\ToastType;
use App\Events\ToastBroadcasted;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotificationBuilder
{
    protected ?string $title = null;

    protected ?string $subtitle = null;

    protected ?NotificationContent $content = null;

    protected ToastType $type = ToastType::Success;

    protected ToastPosition $position = ToastPosition::TopRight;

    protected ToastAnimation $animation = ToastAnimation::Slide;

    protected bool $persist = false;

    protected ?string $userId = null;

    protected ?string $teamId = null;

    protected bool $global = false;

    protected ?string $link = null;

    /**
     * Create a new notification builder instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a new notification builder instance (factory method).
     */
    public static function make(): static
    {
        return new static;
    }

    /**
     * Set the notification title (required).
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set the notification subtitle (optional).
     */
    public function subtitle(?string $subtitle): static
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Set the notification content as a string.
     */
    public function content(string $content): static
    {
        $this->content = NotificationContent::string($content);

        return $this;
    }

    /**
     * Set the notification content as HTML (trusted).
     */
    public function html(string|\Illuminate\Contracts\Support\Htmlable|\Illuminate\Support\HtmlString $html): static
    {
        $this->content = NotificationContent::html($html);

        return $this;
    }

    /**
     * Set the notification content from a Blade view.
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
     * Set the toast position.
     */
    public function position(ToastPosition $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Set the toast animation.
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
     */
    public function toUser(User|Authenticatable|string $user): static
    {
        $this->userId = $user instanceof User ? $user->uuid : (is_string($user) ? $user : $user->getAuthIdentifier());
        $this->teamId = null;
        $this->global = false;

        return $this;
    }

    /**
     * Send to a specific team.
     */
    public function toTeam(Team|string $team): static
    {
        $this->teamId = $team instanceof Team ? $team->uuid : $team;
        $this->userId = null;
        $this->global = false;

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

        return $this;
    }

    /**
     * Set an optional link URL for the notification.
     */
    public function link(string $link): static
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Send the notification (broadcast toast + optionally persist).
     */
    public function send(): void
    {
        if ($this->title === null || trim($this->title) === '') {
            throw new \InvalidArgumentException('Notification title is required.');
        }

        // Determine the target channel
        $channel = $this->determineChannel();

        // Render icon HTML server-side
        $iconHtml = $this->renderIconForType($this->type);

        // Create toast payload
        $payload = new ToastPayload(
            title: $this->title,
            subtitle: $this->subtitle,
            content: $this->content?->render(),
            type: $this->type,
            position: $this->position,
            animation: $this->animation,
            link: $this->link,
            iconHtml: $iconHtml,
        );

        // Always broadcast the toast
        event(new ToastBroadcasted($payload, $channel));

        // Optionally persist to database
        if ($this->persist) {
            $this->persistToDatabase($payload);
        }
    }

    /**
     * Render icon HTML for the given toast type.
     */
    protected function renderIconForType(ToastType $type): string
    {
        $iconMapper = app(\App\Services\IconPackMapper::class);

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
     * Determine the broadcast channel based on current settings.
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

        // Default to current user
        $user = Auth::user();
        if ($user instanceof User) {
            return "private-notifications.user.{$user->uuid}";
        }

        throw new \RuntimeException('Cannot determine notification channel: no user context available.');
    }

    /**
     * Persist notification to database.
     */
    protected function persistToDatabase(ToastPayload $payload): void
    {
        $notificationData = [
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\GeneralNotification',
            'data' => [
                'title' => $payload->title,
                'subtitle' => $payload->subtitle,
                'content' => $payload->content,
                'type' => $payload->type->value,
                'link' => $payload->link,
            ],
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

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
     */
    protected function persistUser(int|string|User $userId, array $notificationData): void
    {
        $user = $userId instanceof User ? $userId : User::find($userId);
        if (! $user) {
            return;
        }

        $notificationData['notifiable_type'] = User::class;
        $notificationData['notifiable_id'] = $user->id;

        DatabaseNotification::query()->create($notificationData);
    }

    /**
     * Persist notification for all team members.
     */
    protected function persistTeam(array $notificationData): void
    {
        $team = Team::where('uuid', $this->teamId)->first();
        if (! $team) {
            return;
        }

        $userIds = $team->users()->pluck('users.id');

        if ($userIds->isEmpty()) {
            return;
        }

        foreach (array_chunk($userIds->all(), 100) as $chunk) {
            foreach ($chunk as $userId) {
                $notificationData['id'] = (string) Str::uuid();
                $notificationData['notifiable_type'] = User::class;
                $notificationData['notifiable_id'] = $userId;

                DatabaseNotification::query()->create($notificationData);
            }
        }
    }

    /**
     * Persist notification for all users (global).
     */
    protected function persistGlobal(array $notificationData): void
    {
        $userIds = User::pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        foreach (array_chunk($userIds->all(), 100) as $chunk) {
            foreach ($chunk as $userId) {
                $notificationData['id'] = (string) Str::uuid();
                $notificationData['notifiable_type'] = User::class;
                $notificationData['notifiable_id'] = $userId;

                DatabaseNotification::query()->create($notificationData);
            }
        }
    }
}
