<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use Illuminate\Auth\Events\Login;

class SyncUserPreferencesOnLogin
{
    public function __construct(
        private readonly FrontendPreferencesService $preferences
    ) {}

    /**
     * Handle the event.
     * Syncs user preferences from database to session immediately after login.
     */
    public function handle(Login $event): void
    {
        if ($event->user instanceof User) {
            $this->preferences->syncUserPreferencesToSession($event->user);
        }
    }
}
