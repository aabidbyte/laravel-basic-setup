<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Constants\Auth\Permissions;
use App\Models\MailSettings;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Hierarchical mail credential resolver.
 *
 * Resolves mail settings in this order:
 * 1. User settings (if user has CONFIGURE_MAIL_SETTINGS permission)
 * 2. Team settings (if team has mail config)
 * 3. App settings (from mail_settings table where settable_type = 'app')
 * 4. Environment variables (returns null, uses Laravel defaults)
 */
class MailCredentialResolver
{
    /**
     * Resolve mail settings for the given context.
     *
     * @param  User|null  $forUser  The user to check for custom settings
     * @param  Team|null  $forTeam  The team to check for custom settings
     * @return MailSettings|null The resolved mail settings, or null to use environment defaults
     */
    public function resolve(?User $forUser = null, ?Team $forTeam = null): ?MailSettings
    {
        // 1. Try user settings (if user has permission)
        $settings = $this->resolveFromUser($forUser);
        if ($settings !== null) {
            return $settings;
        }

        // 2. Try team settings
        $settings = $this->resolveFromTeam($forTeam, $forUser);
        if ($settings !== null) {
            return $settings;
        }

        // 3. Try app-level settings
        $settings = $this->resolveFromApp();
        if ($settings !== null) {
            return $settings;
        }

        // 4. Return null to use environment defaults
        return null;
    }

    /**
     * Resolve mail settings from user.
     *
     * @param  User|null  $user  The user to check
     * @return MailSettings|null The user's mail settings if they have permission and settings
     */
    protected function resolveFromUser(?User $user): ?MailSettings
    {
        if ($user === null) {
            return null;
        }

        // Check if user has permission to use custom mail settings
        if (! $user->can(Permissions::CONFIGURE_MAIL_SETTINGS)) {
            return null;
        }

        // Get active mail settings for this user
        return MailSettings::getForUser($user);
    }

    /**
     * Resolve mail settings from team.
     *
     * @param  Team|null  $team  The team to check
     * @param  User|null  $user  The user to get team from if team is null
     * @return MailSettings|null The team's mail settings if configured
     */
    protected function resolveFromTeam(?Team $team, ?User $user = null): ?MailSettings
    {
        // If no team provided, try to get from user's first team
        if ($team === null && $user !== null) {
            $team = $user->teams()->first();
        }

        // If still no team, try the current authenticated user's first team
        if ($team === null) {
            /** @var User|null $authUser */
            $authUser = Auth::user();
            $team = $authUser?->teams()->first();
        }

        if ($team === null) {
            return null;
        }

        return MailSettings::getForTeam($team);
    }

    /**
     * Resolve mail settings from app-level configuration.
     *
     * @return MailSettings|null The app-level mail settings if configured
     */
    protected function resolveFromApp(): ?MailSettings
    {
        return MailSettings::getForApp();
    }

    /**
     * Check if custom mail settings are available for the context.
     *
     * @param  User|null  $forUser  The user context
     * @param  Team|null  $forTeam  The team context
     * @return bool True if custom settings are available
     */
    public function hasCustomSettings(?User $forUser = null, ?Team $forTeam = null): bool
    {
        return $this->resolve($forUser, $forTeam) !== null;
    }

    /**
     * Get the source of resolved settings (for debugging/logging).
     *
     * @param  User|null  $forUser  The user context
     * @param  Team|null  $forTeam  The team context
     * @return string The source name ('user', 'team', 'app', 'environment')
     */
    public function getSettingsSource(?User $forUser = null, ?Team $forTeam = null): string
    {
        if ($this->resolveFromUser($forUser) !== null) {
            return 'user';
        }

        if ($this->resolveFromTeam($forTeam, $forUser) !== null) {
            return 'team';
        }

        if ($this->resolveFromApp() !== null) {
            return 'app';
        }

        return 'environment';
    }
}
