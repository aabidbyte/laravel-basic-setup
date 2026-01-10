<?php

declare(strict_types=1);

namespace App\Listeners\Preferences;

use App\Services\FrontendPreferences\FrontendPreferencesService;
use Illuminate\Auth\Events\Login;

/**
 * Sync user preferences on login.
 *
 * This listener ensures that when a user logs in, their saved preferences
 * (locale, theme, timezone) are applied to the session and application context.
 * Also sets the user's first team ID in session for TeamsPermission middleware.
 */
class SyncUserPreferencesOnLogin
{
    public function __construct(
        private readonly FrontendPreferencesService $preferences,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof \App\Models\User) {
            return;
        }

        // Sync user preferences from database to session
        $this->preferences->syncUserPreferences($user);

        // Set the user's first team ID in session (TeamsPermission middleware expects this)
        if ($user->teams()->exists()) {
            $firstTeam = $user->teams()->first();
            if ($firstTeam) {
                session(['team_id' => $firstTeam->id]);
            }
        }

        // Update last login timestamp
        $user->updateLastLoginAt();
    }
}
