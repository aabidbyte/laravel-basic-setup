<?php

declare(strict_types=1);

namespace App\Services\DataTable;

use App\Models\User;
use App\Services\DataTable\Stores\SessionDataTablePreferencesStore;
use App\Services\DataTable\Stores\UserJsonDataTablePreferencesStore;
use Illuminate\Http\Request;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Facades\Auth;

/**
 * Service for managing DataTable preferences (filters, per_page, sort, etc.)
 * Follows the same pattern as FrontendPreferencesService for consistency.
 */
class DataTablePreferencesService
{
    private ?SessionDataTablePreferencesStore $sessionStore = null;

    public function __construct(
        private readonly SessionStore $session
    ) {}

    /**
     * Get the session store (single source of truth for reads).
     */
    private function getSessionStore(): SessionDataTablePreferencesStore
    {
        if ($this->sessionStore === null) {
            $this->sessionStore = new SessionDataTablePreferencesStore($this->session);
        }

        return $this->sessionStore;
    }

    /**
     * Sync preferences from user database to session if needed.
     * This ensures session is always the single source of truth.
     */
    private function syncFromDatabaseIfNeeded(string $entityKey, ?Request $request = null): void
    {
        $user = Auth::user();

        // Only sync if user is authenticated
        if (! $user instanceof User) {
            return;
        }

        $sessionStore = $this->getSessionStore();
        $sessionPrefs = $sessionStore->all($entityKey);

        // If session is empty, load from database
        if (empty($sessionPrefs)) {
            $userStore = new UserJsonDataTablePreferencesStore($user);
            $userPrefs = $userStore->all($entityKey);

            if (! empty($userPrefs)) {
                // Sync from DB to session
                $sessionStore->setMany($entityKey, $userPrefs);
            }
        }
    }

    /**
     * Get a preference value for an entity.
     * Session is the single source of truth for reads.
     */
    public function get(string $entityKey, string $key, mixed $default = null, ?Request $request = null): mixed
    {
        // Sync from database if needed (first load for authenticated users)
        $this->syncFromDatabaseIfNeeded($entityKey, $request);

        $sessionStore = $this->getSessionStore();

        return $sessionStore->get($entityKey, $key, $default);
    }

    /**
     * Set a preference value for an entity.
     * For authenticated users: update DB first, then session.
     * For guests: update session only.
     */
    public function set(string $entityKey, string $key, mixed $value): void
    {
        $user = Auth::user();

        // If authenticated, update DB first, then session
        if ($user instanceof User) {
            $userStore = new UserJsonDataTablePreferencesStore($user);
            $userStore->set($entityKey, $key, $value);
        }

        // Always update session (single source of truth)
        $this->getSessionStore()->set($entityKey, $key, $value);
    }

    /**
     * Set multiple preferences at once for an entity.
     * For authenticated users: update DB first, then session.
     * For guests: update session only.
     *
     * @param  array<string, mixed>  $preferences
     */
    public function setMany(string $entityKey, array $preferences): void
    {
        $user = Auth::user();

        // If authenticated, update DB first, then session
        if ($user instanceof User) {
            $userStore = new UserJsonDataTablePreferencesStore($user);
            $userStore->setMany($entityKey, $preferences);
        }

        // Always update session (single source of truth)
        $this->getSessionStore()->setMany($entityKey, $preferences);
    }

    /**
     * Get all preferences for an entity.
     * Session is the single source of truth for reads.
     *
     * @return array<string, mixed>
     */
    public function all(string $entityKey, ?Request $request = null): array
    {
        // Sync from database if needed (first load for authenticated users)
        $this->syncFromDatabaseIfNeeded($entityKey, $request);

        $sessionStore = $this->getSessionStore();

        return $sessionStore->all($entityKey);
    }

    /**
     * Refresh preferences from database to session for an entity.
     * This reloads user preferences from DB and syncs to session.
     */
    public function refresh(string $entityKey): void
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $this->syncUserPreferencesToSession($user, $entityKey);
        }
    }

    /**
     * Sync user preferences from database to session for an entity.
     * This is called on login to ensure preferences are loaded from DB and synced to session.
     */
    public function syncUserPreferencesToSession(User $user, string $entityKey): void
    {
        // Refresh user model to get latest data
        $user->refresh();

        // Load from DB and sync to session
        $userStore = new UserJsonDataTablePreferencesStore($user);
        $userPrefs = $userStore->all($entityKey);

        // Sync to session (single source of truth)
        if (! empty($userPrefs)) {
            $this->getSessionStore()->setMany($entityKey, $userPrefs);
        }
    }

    /**
     * Clear all preferences for an entity.
     */
    public function clear(string $entityKey): void
    {
        $user = Auth::user();

        // If authenticated, clear from DB
        if ($user instanceof User) {
            $userStore = new UserJsonDataTablePreferencesStore($user);
            $userStore->clear($entityKey);
        }

        // Always clear from session
        $this->getSessionStore()->clear($entityKey);
    }
}
