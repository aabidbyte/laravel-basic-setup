<?php

declare(strict_types=1);

namespace App\Services\Users;

use App\Mail\UserActivationMail;
use App\Models\Role;
use App\Models\User;
use App\Services\Mail\MailBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Service for user CRUD operations.
 *
 * Handles user creation, updates, and related operations like
 * sending activation emails and tracking who created users.
 */
class UserService
{
    public function __construct(
        protected ActivationService $activationService,
    ) {}

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $data  User data (name, email, username, password, etc.)
     * @param  bool  $sendActivation  Whether to send an activation email
     * @param  array<int>  $roleIds  Role IDs to assign
     * @param  array<int>  $teamIds  Team IDs to assign
     * @return User The created user
     *
     * @throws InvalidArgumentException If email is required for activation but not provided
     */
    public function createUser(
        array $data,
        bool $sendActivation = false,
        array $roleIds = [],
        array $teamIds = [],
    ): User {
        // Validate: if sending activation, email is required
        if ($sendActivation && empty($data['email'])) {
            throw new InvalidArgumentException('Email is required when sending activation email.');
        }

        return DB::transaction(function () use ($data, $sendActivation, $roleIds, $teamIds) {
            // Get the creator (current authenticated user)
            /** @var User|null $creator */
            $creator = Auth::user();

            // Prepare user data
            $userData = [
                'name' => $data['name'],
                'username' => $data['username'] ?? null,
                'email' => $data['email'] ?? null,
                'password' => $this->generatePassword($data['password'] ?? null, $sendActivation),
                'team_id' => $data['team_id'] ?? null,
                'created_by_user_id' => $creator?->id,
                'is_active' => false, // Users are inactive by default until activated
                'frontend_preferences' => $this->buildFrontendPreferences($data),
            ];

            // Create the user
            $user = User::create($userData);

            // Assign roles (if provided) - use syncRoles for proper team context handling
            if (! empty($roleIds)) {
                $roles = Role::whereIn('id', $roleIds)->get();
                $user->syncRoles($roles);
            }

            // Assign teams (if provided) - manually attach with UUID
            if (! empty($teamIds)) {
                $this->syncTeamsWithUuid($user, $teamIds);
            }

            // Send activation email if requested
            if ($sendActivation) {
                $this->sendActivationEmail($user);
            }

            return $user;
        });
    }

    /**
     * Update an existing user.
     *
     * @param  User  $user  The user to update
     * @param  array<string, mixed>  $data  User data to update
     * @param  array<int>|null  $roleIds  Role IDs to assign (null = don't change)
     * @param  array<int>|null  $teamIds  Team IDs to assign (null = don't change)
     * @return User The updated user
     */
    public function updateUser(
        User $user,
        array $data,
        ?array $roleIds = null,
        ?array $teamIds = null,
    ): User {
        return DB::transaction(function () use ($user, $data, $roleIds, $teamIds) {
            // Build update data
            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }

            if (array_key_exists('username', $data)) {
                $updateData['username'] = $data['username'];
            }

            if (array_key_exists('email', $data)) {
                $updateData['email'] = $data['email'];
            }

            if (! empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            if (isset($data['team_id'])) {
                $updateData['team_id'] = $data['team_id'];
            }

            if (isset($data['is_active'])) {
                $updateData['is_active'] = $data['is_active'];
            }

            // Update frontend preferences if provided
            if (isset($data['timezone']) || isset($data['locale'])) {
                $preferences = $user->frontend_preferences ?? [];

                if (isset($data['timezone'])) {
                    $preferences['timezone'] = $data['timezone'];
                }

                if (isset($data['locale'])) {
                    $preferences['locale'] = $data['locale'];
                }

                $updateData['frontend_preferences'] = $preferences;
            }

            // Update user
            if (! empty($updateData)) {
                $user->update($updateData);
            }

            // Update roles if provided - use syncRoles for proper team context handling
            if ($roleIds !== null) {
                $roles = Role::whereIn('id', $roleIds)->get();
                $user->syncRoles($roles);
            }

            // Update teams if provided - manually attach with UUID
            if ($teamIds !== null) {
                $this->syncTeamsWithUuid($user, $teamIds);
            }

            return $user->fresh();
        });
    }

    /**
     * Send activation email to a user.
     *
     * @param  User  $user  The user to send activation email to
     *
     * @throws InvalidArgumentException If user has no email address
     */
    public function sendActivationEmail(User $user): void
    {
        if (empty($user->email)) {
            throw new InvalidArgumentException('User must have an email address to send activation email.');
        }

        // Generate activation token and URL
        $activationUrl = $this->activationService->generateActivationUrl($user);

        // Send the email using MailBuilder
        MailBuilder::make()
            ->to($user)
            ->mailable(new UserActivationMail($user, $activationUrl))
            ->send();
    }

    /**
     * Generate an activation link for a user (for users without email).
     *
     * @param  User  $user  The user to generate link for
     * @return string The activation URL
     */
    public function generateActivationLink(User $user): string
    {
        return $this->activationService->generateActivationUrl($user);
    }

    /**
     * Generate password for new user.
     *
     * @param  string|null  $password  Provided password
     * @param  bool  $isActivationFlow  Whether this is an activation flow
     * @return string The password hash
     */
    protected function generatePassword(?string $password, bool $isActivationFlow): string
    {
        // If activation flow, generate a random password (user will set it during activation)
        if ($isActivationFlow && empty($password)) {
            return Hash::make(Str::random(32));
        }

        // If password provided, use it
        if (! empty($password)) {
            return Hash::make($password);
        }

        // Generate a random password as fallback
        return Hash::make(Str::random(32));
    }

    /**
     * Build frontend preferences from data.
     *
     * @param  array<string, mixed>  $data  The input data
     * @return array<string, mixed> The frontend preferences
     */
    protected function buildFrontendPreferences(array $data): array
    {
        $preferences = [];

        if (isset($data['timezone'])) {
            $preferences['timezone'] = $data['timezone'];
        }

        if (isset($data['locale'])) {
            $preferences['locale'] = $data['locale'];
        }

        return $preferences;
    }

    /**
     * Deactivate a user.
     *
     * @param  User  $user  The user to deactivate
     * @return bool True if successful
     */
    public function deactivateUser(User $user): bool
    {
        return $user->deactivate();
    }

    /**
     * Activate a user (without password change).
     *
     * @param  User  $user  The user to activate
     * @return bool True if successful
     */
    public function activateUser(User $user): bool
    {
        return $user->activate();
    }

    /**
     * Sync teams with UUID generation for the pivot table.
     *
     * The team_user pivot table requires a uuid column, so we can't use
     * the standard sync() method. This manually handles the sync with UUID.
     *
     * @param  User  $user  The user to sync teams for
     * @param  array<int>  $teamIds  The team IDs to sync
     */
    protected function syncTeamsWithUuid(User $user, array $teamIds): void
    {
        // Get current team IDs
        $currentTeamIds = $user->teams()->pluck('teams.id')->toArray();

        // Determine teams to add and remove
        $toAdd = array_diff($teamIds, $currentTeamIds);
        $toRemove = array_diff($currentTeamIds, $teamIds);

        // Remove teams that are no longer assigned
        if (! empty($toRemove)) {
            $user->teams()->detach($toRemove);
        }

        // Add new teams with UUID
        foreach ($toAdd as $teamId) {
            $user->teams()->attach($teamId, [
                'uuid' => (string) Str::uuid(),
            ]);
        }
    }
}
