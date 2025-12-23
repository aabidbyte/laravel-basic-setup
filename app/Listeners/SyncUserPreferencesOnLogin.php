<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use App\Services\DataTable\DataTablePreferencesService;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;

class SyncUserPreferencesOnLogin
{
    public function __construct(
        private readonly FrontendPreferencesService $preferences,
        private readonly DataTablePreferencesService $dataTablePreferences
    ) {}

    /**
     * Handle the event.
     * Syncs user preferences from database to session immediately after login.
     * Also sets the user's first team ID in session for TeamsPermission middleware.
     * Updates the last_login_at timestamp.
     * Syncs DataTable preferences for all entities.
     */
    public function handle(Login $event): void
    {
        if ($event->user instanceof User) {
            // Sync frontend preferences (locale, theme, timezone)
            $this->preferences->syncUserPreferencesToSession($event->user);

            // Sync DataTable preferences for all entities
            // Get all entity keys from user's frontend_preferences
            // DataTable preferences are stored as keys like "datatable_preferences.users"
            $userPrefs = $event->user->frontend_preferences ?? [];
            $prefix = \App\Constants\DataTable::USER_PREF_KEY_PREFIX.'.';

            foreach (array_keys($userPrefs) as $key) {
                if (str_starts_with($key, $prefix)) {
                    // Extract entity key (e.g., "users" from "datatable_preferences.users")
                    $entityKey = str_replace($prefix, '', $key);
                    $this->dataTablePreferences->syncUserPreferencesToSession($event->user, $entityKey);
                }
            }

            // Set the user's first team ID in session (TeamsPermission middleware expects this)
            $firstTeam = $event->user->teams()->first();
            if ($firstTeam) {
                Session::put('team_id', $firstTeam->id);
            }

            // Update last login timestamp
            $event->user->updateLastLoginAt();
        }
    }
}
