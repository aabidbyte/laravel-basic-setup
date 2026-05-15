<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\Notifications\NotificationObserver;
use App\Services\Notifications\NotificationContent;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification as LaravelDatabaseNotification;

#[ObservedBy([NotificationObserver::class])]
class Notification extends Model
{
    use SoftDeletes;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'central';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the corresponding Laravel DatabaseNotification instance.
     * This is useful if we need to interact with Laravel's notification system directly.
     */
    public function toLaravelNotification(): LaravelDatabaseNotification
    {
        return new LaravelDatabaseNotification($this->attributes);
    }

    /**
     * Label for the model.
     */
    public function label(): string
    {
        return $this->resolved_title;
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead()
    {
        $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
    }

    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread()
    {
        $this->forceFill(['read_at' => null])->save();
    }

    /**
     * Determine if a notification has been read.
     *
     * @return bool
     */
    public function read()
    {
        return $this->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     *
     * @return bool
     */
    public function unread()
    {
        return $this->read_at === null;
    }

    /**
     * Get the notifiable entity that the notification belongs to.
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Get the resolved title (translated if key exists).
     */
    public function getResolvedTitleAttribute(): string
    {
        $data = $this->data;

        if (isset($data['titleKey'])) {
            return __($data['titleKey'], $data['titleParams'] ?? []);
        }

        return $data['title'] ?? 'Notification';
    }

    /**
     * Get the resolved subtitle (translated if key exists).
     */
    public function getResolvedSubtitleAttribute(): ?string
    {
        $data = $this->data;

        if (isset($data['subtitleKey'])) {
            return __($data['subtitleKey'], $data['subtitleParams'] ?? []);
        }

        return $data['subtitle'] ?? null;
    }

    /**
     * Get the resolved content (rendered from storable if exists).
     */
    public function getResolvedContentAttribute(): ?string
    {
        $data = $this->data;

        if (isset($data['contentStorable'])) {
            return NotificationContent::fromStorable($data['contentStorable']);
        }

        return $data['content'] ?? null;
    }

    /**
     * Get the notification type.
     */
    public function getNotificationTypeAttribute(): string
    {
        return $this->data['type'] ?? 'classic';
    }

    /**
     * Get the notification link.
     */
    public function getNotificationLinkAttribute(): ?string
    {
        return $this->data['link'] ?? null;
    }

    /**
     * Check if notification has a link.
     */
    public function getHasLinkAttribute(): bool
    {
        return ! empty($this->data['link']);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Check if notification is read.
     */
    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }
}
