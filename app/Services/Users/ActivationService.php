<?php

declare(strict_types=1);

namespace App\Services\Users;

use App\Mail\WelcomeMail;
use App\Models\User;
use App\Notifications\UserActivatedNotification;
use App\Services\Mail\MailBuilder;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
     * Token expiration in days.
     */
    protected const TOKEN_EXPIRATION_DAYS = 7;

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
        // Generate a random token
        $plainToken = Str::random(64);

        // Use email or username as identifier
        $identifier = $user->email ?? $user->username ?? (string) $user->id;

        // Delete any existing tokens for this user
        DB::table('password_reset_tokens')
            ->where('identifier', $identifier)
            ->delete();

        // Store the hashed token
        DB::table('password_reset_tokens')->insert([
            'identifier' => $identifier,
            'uuid' => (string) Str::uuid(),
            'token' => Hash::make($plainToken),
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
        $record = DB::table('password_reset_tokens')
            ->where('identifier', $identifier)
            ->first();

        if ($record === null) {
            return false;
        }

        // Check if token matches
        if (! Hash::check($token, $record->token)) {
            return false;
        }

        // Check if token has expired
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->diffInDays(now()) > self::TOKEN_EXPIRATION_DAYS) {
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
        // Get all non-expired tokens
        $cutoff = now()->subDays(self::TOKEN_EXPIRATION_DAYS);

        $records = DB::table('password_reset_tokens')
            ->where('created_at', '>=', $cutoff)
            ->get();

        foreach ($records as $record) {
            if (Hash::check($token, $record->token)) {
                // Token matches, find the user
                return User::query()
                    ->where('email', $record->identifier)
                    ->orWhere('username', $record->identifier)
                    ->first();
            }
        }

        return null;
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
        return DB::transaction(function () use ($user, $password) {
            // Update user password and activate
            $user->update([
                'password' => Hash::make($password),
                'is_active' => true,
                'email_verified_at' => $user->email ? now() : null,
            ]);

            // Delete the used token
            $identifier = $user->email ?? $user->username ?? (string) $user->id;
            DB::table('password_reset_tokens')
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
            ->mailable(new WelcomeMail($user))
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
        $notifiedUserIds = new Collection;

        // 1. Notify the creator
        $creator = $user->createdBy;
        if ($creator !== null && $creator->id !== $user->id) {
            $this->sendActivationNotification($creator, $user);
            $notifiedUserIds->push($creator->id);
        }

        // 2. Notify team admins (with deduplication)
        $teams = $user->teams()->with('users')->get();

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
                // You can customize this logic based on your admin detection
                if ($this->isTeamAdmin($teamMember, $team)) {
                    $this->sendActivationNotification($teamMember, $user);
                    $notifiedUserIds->push($teamMember->id);
                }
            }
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
            ->title(__('messages.notifications.user_activated.toast_title'))
            ->subtitle(__('messages.notifications.user_activated.toast_subtitle', ['name' => $activatedUser->name]))
            ->success()
            ->toUser($recipient)
            ->link(route('users.show', $activatedUser->uuid))
            ->send();
    }

    /**
     * Check if a user is an admin of a team.
     *
     * @param  User  $user  The user to check
     * @param  mixed  $team  The team
     * @return bool True if the user is a team admin
     */
    protected function isTeamAdmin(User $user, mixed $team): bool
    {
        // Check if user has any admin-like role
        // This can be customized based on your role structure
        return $user->hasAnyRole(['super-admin', 'admin', 'team-admin', 'manager']);
    }

    /**
     * Get the token expiration in days.
     *
     * @return int Days until token expires
     */
    public function getTokenExpirationDays(): int
    {
        return self::TOKEN_EXPIRATION_DAYS;
    }
}
