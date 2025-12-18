<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;

class SyncUserPreferencesOnLogin
{
    public function __construct(
        private readonly FrontendPreferencesService $preferences
    ) {}

    /**
     * Handle the event.
     * Syncs user preferences from database to session immediately after login.
     * Also sets the user's first team ID in session for TeamsPermission middleware.
     */
    public function handle(Login $event): void
    {
        if ($event->user instanceof User) {
            $this->preferences->syncUserPreferencesToSession($event->user);

            // Set the user's first team ID in session (TeamsPermission middleware expects this)
            $firstTeam = $event->user->teams()->first();
            if ($firstTeam) {
                Session::put('team_id', $firstTeam->id);
            }
        }
    }
}
