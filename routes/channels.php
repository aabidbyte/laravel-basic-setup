<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Notification channels
Broadcast::channel('private-notifications.user.{userUuid}', function ($user, string $userUuid) {
    if (! $user instanceof User) {
        return false;
    }

    return $user->uuid === $userUuid;
});

Broadcast::channel('private-notifications.team.{teamUuid}', function ($user, string $teamUuid) {
    if (! $user instanceof User) {
        return false;
    }

    $team = Team::where('uuid', $teamUuid)->first();
    if (! $team) {
        return false;
    }

    return $team->users()->where('users.id', $user->id)->exists();
});

Broadcast::channel('private-notifications.global', function ($user) {
    return $user !== null;
});

// Session channel is now a PUBLIC channel (not private)
// Security: Session IDs are cryptographically random and hard to guess
// The session ID itself acts as the security mechanism
// No authorization callback needed for public channels
