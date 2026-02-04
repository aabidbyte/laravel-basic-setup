<?php

declare(strict_types=1);

namespace App\Services\Users;

use App\Constants\Auth\Roles;
use App\Mail\UserActivationMail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Services\Mail\MailBuilder;
use App\Support\Users\UserData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

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
     * @return User The created user
     *
     * @throws InvalidArgumentException If email is required for activation but not provided
     */
    public function createUser(UserData $userData): User
    {
        $data = $userData->attributes;
        $sendActivation = $userData->sendActivation;
        $roleUuids = $userData->roleUuids ?? [];
        $teamUuids = $userData->teamUuids ?? [];
        $permissionUuids = $userData->permissionUuids ?? [];
        // Validate: if sending activation, email is required
        if ($sendActivation && empty($data['email'])) {
            throw new InvalidArgumentException('Email is required when sending activation email.');
        }

        return DB::transaction(function () use ($userData) {
            $data = $userData->attributes;
            $sendActivation = $userData->sendActivation;
            $roleUuids = $userData->roleUuids ?? [];
            $teamUuids = $userData->teamUuids ?? [];
            $permissionUuids = $userData->permissionUuids ?? [];
            // Get the creator (current authenticated user)
            /** @var User|null $creator */
            $creator = Auth::user();

            // Prepare user data
            $userData = [
                'name' => $data['name'],
                'username' => $data['username'] ?? null,
                'email' => $data['email'] ?? null,
                'password' => $this->generatePassword($data['password'] ?? null, $sendActivation),
                'created_by_user_id' => $creator?->id,
                'is_active' => false, // Users are inactive by default until activated
                'frontend_preferences' => $this->buildFrontendPreferences($data),
            ];

            // Create the user
            $user = User::create($userData);

            // Assign roles (if provided) - lookup by UUID
            if (! empty($roleUuids)) {
                $roleIds = Role::whereIn('uuid', $roleUuids)->pluck('id')->toArray();
                $user->syncRoles($roleIds);
            }

            // Assign teams (if provided) - lookup by UUID, then sync with pivot UUID
            if (! empty($teamUuids)) {
                $teamIds = Team::whereIn('uuid', $teamUuids)->pluck('id')->toArray();
                $this->syncTeamsWithUuid($user, $teamIds);
            }

            // Assign direct permissions (if provided) - lookup by UUID
            if (! empty($permissionUuids)) {
                $permissionIds = Permission::whereIn('uuid', $permissionUuids)->pluck('id')->toArray();
                $user->syncPermissions($permissionIds);
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
     * @return User The updated user
     */
    public function updateUser(User $user, UserData $userData): User
    {
        $data = $userData->attributes;
        $roleUuids = $userData->roleUuids;
        $teamUuids = $userData->teamUuids;
        $permissionUuids = $userData->permissionUuids;

        return DB::transaction(function () use ($user, $userData) {
            $data = $userData->attributes;
            $roleUuids = $userData->roleUuids;
            $teamUuids = $userData->teamUuids;
            $permissionUuids = $userData->permissionUuids;
            // Build update data
            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }

            if (array_key_exists('username', $data)) {
                $updateData['username'] = $data['username'];
            }

            if (array_key_exists('email', $data)) {
                $newEmail = $data['email'];
                $currentEmail = $user->email;

                // If email is being changed and user has a verified email
                if ($newEmail !== $currentEmail && $user->hasVerifiedEmail()) {
                    // Store in pending_email instead of email
                    $this->initiateEmailChange($user, $newEmail);
                } elseif ($newEmail !== $currentEmail) {
                    // User has no verified email, update directly
                    $updateData['email'] = $newEmail;
                }
            }

            // Password can only be updated by super_admin
            if (! empty($data['password'])) {
                /** @var User|null $currentUser */
                $currentUser = Auth::user();
                if ($currentUser && $currentUser->hasRole(Roles::SUPER_ADMIN)) {
                    $updateData['password'] = Hash::make($data['password']);
                }
                // Silently ignore password updates from non-super-admins
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

            // Update roles if provided - lookup by UUID
            if ($roleUuids !== null) {
                $roleIds = Role::whereIn('uuid', $roleUuids)->pluck('id')->toArray();
                $user->syncRoles($roleIds);
            }

            // Update teams if provided - lookup by UUID, then sync with pivot UUID
            if ($teamUuids !== null) {
                $teamIds = Team::whereIn('uuid', $teamUuids)->pluck('id')->toArray();
                $this->syncTeamsWithUuid($user, $teamIds);
            }

            // Update direct permissions if provided - lookup by UUID
            if ($permissionUuids !== null) {
                $permissionIds = Permission::whereIn('uuid', $permissionUuids)->pluck('id')->toArray();
                $user->syncPermissions($permissionIds);
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
            ->mailable((new UserActivationMail($user, $activationUrl))->locale(config('app.locale')))
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

    /**
     * Initiate an email change for a user.
     *
     * This stores the new email in pending_email, sends a verification email
     * to the new address, and sends a security notification to the old address.
     *
     * @param  User  $user  The user changing their email
     * @param  string  $newEmail  The new email address
     */
    public function initiateEmailChange(User $user, string $newEmail): void
    {
        $token = Str::random(64);
        $expiresAt = now()->addDays(7);

        // Update user with pending email
        $user->update([
            'pending_email' => $newEmail,
            'pending_email_token' => hash('sha256', $token),
            'pending_email_expires_at' => $expiresAt,
        ]);

        // Generate verification URL
        $verificationUrl = route('email.change.verify', ['token' => $token]);

        // Send verification email to new address
        MailBuilder::make()
            ->to($newEmail)
            ->template('Email Change Verification', [
                'user' => $user,
            ], [
                'action_url' => $verificationUrl,
            ])
            ->send();

        // Send security notification to old address
        if (! empty($user->email)) {
            MailBuilder::make()
                ->to($user->email)
                ->template('Security Email Change', [
                    'user' => $user,
                ], [
                    'new_email' => $newEmail,
                    'support_email' => config('mail.support_email', config('mail.from.address')),
                ])
                ->send();
        }
    }

    /**
     * Cancel a pending email change for a user.
     *
     * @param  User  $user  The user to cancel the email change for
     * @return bool True if successful
     */
    public function cancelPendingEmailChange(User $user): bool
    {
        return $user->cancelPendingEmailChange();
    }

    /**
     * Send password reset email to a user.
     *
     * Uses Laravel's built-in password reset flow.
     *
     * @param  User  $user  The user to send reset email to
     *
     * @throws InvalidArgumentException If user has no email address
     * @throws RuntimeException If failed to send reset link
     */
    public function sendPasswordResetEmail(User $user): void
    {
        if (empty($user->email)) {
            throw new InvalidArgumentException('User must have an email address to send password reset email.');
        }

        $status = Password::broker()->sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new RuntimeException(__($status));
        }
    }
}
