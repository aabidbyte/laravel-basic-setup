<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

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
