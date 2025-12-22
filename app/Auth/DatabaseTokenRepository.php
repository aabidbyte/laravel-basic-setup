<?php

namespace App\Auth;

use App\Models\PasswordResetToken;
use Illuminate\Auth\Passwords\DatabaseTokenRepository as BaseDatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * Custom database token repository that uses the PasswordResetToken model
 * to automatically generate UUIDs via model events.
 */
class DatabaseTokenRepository extends BaseDatabaseTokenRepository
{
    /**
     * Delete all existing reset tokens for the given user.
     */
    protected function deleteExisting(CanResetPassword $user): void
    {
        $identifier = $user->getEmailForPasswordReset();
        PasswordResetToken::where('identifier', $identifier)->delete();
    }

    /**
     * Determine if a token record exists and is valid.
     */
    public function exists(CanResetPasswordContract $user, #[\SensitiveParameter] $token): bool
    {
        $identifier = $user->getEmailForPasswordReset();
        $record = PasswordResetToken::where('identifier', $identifier)->first();

        if (! $record) {
            return false;
        }

        return $this->hasher->check($token, $record->token) &&
               ! $this->tokenExpired($record->created_at);
    }

    /**
     * Create a new token record.
     *
     * @return string
     */
    public function create(CanResetPassword $user)
    {
        // Get email for password reset (required for email notifications)
        // The identifier column in the table supports both email and username
        // for future flexibility, but password reset currently requires email
        $identifier = $user->getEmailForPasswordReset();

        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();

        // Use the model to create the record, which will trigger the HasUuid trait
        // to automatically generate the UUID via model events
        PasswordResetToken::updateOrCreate(
            ['identifier' => $identifier],
            [
                'identifier' => $identifier,
                'token' => $this->hasher->make($token),
                'created_at' => now(),
            ]
        );

        return $token;
    }
}
