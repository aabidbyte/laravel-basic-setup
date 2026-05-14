<?php

declare(strict_types=1);

namespace App\Support\Notifications;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Opaque token for subscribing to public guest notification channels.
 *
 * The token is stored in the session and must not be derived from the session ID.
 */
final class NotificationBroadcastClientId
{
    public const SESSION_KEY = 'notifications_broadcast_client_id';

    public static function current(): string
    {
        $existing = Session::get(self::SESSION_KEY);
        if (\is_string($existing) && $existing !== '') {
            return $existing;
        }

        $token = Str::random(40);
        Session::put(self::SESSION_KEY, $token);

        return $token;
    }

    public static function publicChannelName(): string
    {
        return 'public-notifications.session.' . self::current();
    }
}
