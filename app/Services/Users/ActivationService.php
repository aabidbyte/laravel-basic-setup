<?php

declare(strict_types=1);

namespace App\Services\Users;

use App\Enums\Users\ActivationTokenLifetime;
use App\Models\Team;
use App\Models\User;
use App\Notifications\UserActivatedNotification;
use App\Services\Mail\MailBuilder;
use App\Services\Notifications\NotificationBuilder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Service for user activation operations.
 *
 * Handles:
 * - Activation token generation and validation
 * - User activation with password setting
 * - Welcome email sending
 * - Notifications to creator and team admins (with deduplication)
 */
class ActivationService
{
    /**
     * Generate an activation URL for a user.
     *
     * @param  User  $user  The user to generate URL for
     * @return string The activation URL
     */
    public function generateActivationUrl(User $user): string
    {
        $token = $this->createActivationToken($user);

        return route('auth.activate', ['token' => $token]);
    }

    /**
     * Create an activation token for a user.
     *
     * Uses the password_reset_tokens table.
     *
     * @param  User  $user  The user to create token for
     * @return string The plain text token
     */
    public function createActivationToken(User $user): string
    {
        $selector = (string) Str::uuid();
        $secret = Str::random(64);
        $plainToken = "{$selector}.{$secret}";

        $identifier = $this->identifierFor($user);

        // Delete any existing tokens for this user
        DB::connection('central')->table('password_reset_tokens')
            ->where('identifier', $identifier)
            ->delete();

        DB::connection('central')->table('password_reset_tokens')->insert([
            'identifier' => $identifier,
            'uuid' => $selector,
            'token' => Hash::make($secret),
            'created_at' => now(),
        ]);

        return $plainToken;
    }

    /**
     * Validate an activation token.
     *
     * @param  string  $token  The token to validate
     * @param  string  $identifier  The user identifier (email/username)
     * @return bool True if the token is valid
     */
    public function validateToken(string $token, string $identifier): bool
    {
        $tokenParts = $this->parseActivationToken($token);

        if ($tokenParts === null) {
            return false;
        }

        $record = DB::connection('central')->table('password_reset_tokens')
            ->where('identifier', $identifier)
            ->where('uuid', $tokenParts['selector'])
            ->first();

        if ($record === null) {
            return false;
        }

        if (! Hash::check($tokenParts['secret'], $record->token)) {
            return false;
        }

        if ($this->tokenHasExpired($record->created_at)) {
            return false;
        }

        return true;
    }

    /**
     * Find a user by their activation token.
     *
     * @param  string  $token  The plain text token
     * @return User|null The user if found and token is valid
     */
    public function findUserByToken(string $token): ?User
    {
        $tokenParts = $this->parseActivationToken($token);

        if ($tokenParts === null) {
            return null;
        }

        $record = DB::connection('central')->table('password_reset_tokens')
            ->where('uuid', $tokenParts['selector'])
            ->first();

        if ($record === null) {
            return null;
        }

        if ($this->tokenHasExpired($record->created_at)) {
            return null;
        }

        if (! Hash::check($tokenParts['secret'], $record->token)) {
            return null;
        }

        return $this->userForIdentifier($record->identifier);
    }

    /**
     * Activate a user with a new password.
     *
     * @param  User  $user  The user to activate
     * @param  string  $password  The new password
     * @param  string  $token  The activation token (to be invalidated)
     * @return User The activated user
     */
    public function activateWithPassword(User $user, string $password, string $token): User
    {
        $identifier = $this->identifierFor($user);

        if (! $this->validateToken($token, $identifier)) {
            throw new InvalidArgumentException('The activation token is invalid or expired.');
        }

        return DB::transaction(function () use ($user, $password, $identifier) {
            // Update user password and activate
            $user->update([
                'password' => Hash::make($password),
                'is_active' => true,
                'email_verified_at' => $user->email ? now() : null,
            ]);

            // Delete the used token
            DB::connection('central')->table('password_reset_tokens')
                ->where('identifier', $identifier)
                ->delete();

            // Send welcome email (if user has email)
            $this->sendWelcomeEmail($user);

            // Notify creator and team admins
            $this->notifyCreatorAndAdmins($user);

            return $user->fresh();
        });
    }

    /**
     * Send welcome email to the activated user.
     *
     * @param  User  $user  The activated user
     */
    public function sendWelcomeEmail(User $user): void
    {
        if (empty($user->email)) {
            return;
        }

        MailBuilder::make()
            ->to($user)
            ->template('User Welcome', [
                'user' => $user,
            ], [
                'action_url' => route('dashboard'),
                'login_url' => route('login'),
            ])
            ->send();
    }

    /**
     * Notify the creator and team admins about user activation.
     *
     * Implements deduplication to prevent the same user from receiving
     * multiple notifications for the same activation event.
     *
     * @param  User  $user  The activated user
     */
    public function notifyCreatorAndAdmins(User $user): void
    {
        // Collect users to notify (with deduplication)
        $notifiedUserIds = new Collection();

        // 1. Notify the creator
        $creator = $user->createdBy;
        if ($creator !== null && $creator->id !== $user->id) {
            $this->sendActivationNotification($creator, $user);
            $notifiedUserIds->push($creator->id);
        }

        // 2. Notify team admins (with deduplication)
        // Teams and team memberships are central, so keep raw pivot lookups on the central connection.
        $tenants = $user->tenants;

        foreach ($tenants as $tenant) {
            $tenant->run(function () use ($user, $notifiedUserIds) {
                $teamIds = DB::connection('central')->table('team_user')
                    ->where('user_id', $user->id)
                    ->pluck('team_id');

                if ($teamIds->isEmpty()) {
                    return;
                }

                $teams = Team::whereIn('id', $teamIds)->with('users')->get();

                foreach ($teams as $team) {
                    foreach ($team->users as $teamMember) {
                        // Skip the activated user themselves
                        if ($teamMember->id === $user->id) {
                            continue;
                        }

                        // Skip if already notified
                        if ($notifiedUserIds->contains($teamMember->id)) {
                            continue;
                        }

                        // Check if team member is an admin (has admin permissions)
                        if ($this->isTeamAdmin($teamMember)) {
                            $this->sendActivationNotification($teamMember, $user);
                            $notifiedUserIds->push($teamMember->id);
                        }
                    }
                }
            });
        }
    }

    /**
     * Send activation notification to a user.
     *
     * @param  User  $recipient  The user to notify
     * @param  User  $activatedUser  The user who was activated
     */
    protected function sendActivationNotification(User $recipient, User $activatedUser): void
    {
        // Send via Laravel notification
        $recipient->notify(new UserActivatedNotification($activatedUser));

        // Also send a toast notification
        NotificationBuilder::make()
            ->title('messages.notifications.user_activated.toast_title')
            ->subtitle('messages.notifications.user_activated.toast_subtitle', ['name' => $activatedUser->name])
            ->success()
            ->toUser($recipient)
            ->link(route('users.show', $activatedUser->uuid))
            ->send();
    }

    /**
     * Check if a user is an admin of a team.
     *
     * @param  User  $user  The user to check
     * @return bool True if the user is a team admin
     */
    protected function isTeamAdmin(User $user): bool
    {
        // Check if user has any admin-like role
        // This can be customized based on your role structure
        return $user->hasAnyRole(['super-admin', 'admin', 'team-admin', 'manager']);
    }

    protected function identifierFor(User $user): string
    {
        return $user->email ?? $user->username ?? (string) $user->id;
    }

    /**
     * @return array{selector: string, secret: string}|null
     */
    protected function parseActivationToken(string $token): ?array
    {
        $parts = \explode('.', $token, 2);

        if (\count($parts) !== 2) {
            return null;
        }

        [$selector, $secret] = $parts;

        if (! Str::isUuid($selector) || $secret === '') {
            return null;
        }

        return [
            'selector' => $selector,
            'secret' => $secret,
        ];
    }

    protected function tokenHasExpired(string $createdAt): bool
    {
        return Carbon::parse($createdAt)
            ->lessThan(now()->subDays(ActivationTokenLifetime::Activation->value));
    }

    protected function userForIdentifier(string $identifier): ?User
    {
        return User::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->orWhere('id', $identifier)
            ->first();
    }

    /**
     * Get the token expiration in days.
     *
     * @return int Days until token expires
     */
    public function getTokenExpirationDays(): int
    {
        return ActivationTokenLifetime::Activation->value;
    }
}
