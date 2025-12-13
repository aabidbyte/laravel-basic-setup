<?php

namespace App\Auth;

use App\Models\PasswordResetToken;
use Illuminate\Auth\Passwords\DatabaseTokenRepository as BaseDatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword;

/**
 * Custom database token repository that uses the PasswordResetToken model
 * to automatically generate UUIDs via model events.
 */
class DatabaseTokenRepository extends BaseDatabaseTokenRepository
{
    /**
     * Create a new token record.
     *
     * @return string
     */
    public function create(CanResetPassword $user)
    {
        $email = $user->getEmailForPasswordReset();

        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();

        // Use the model to create the record, which will trigger the HasUuid trait
        // to automatically generate the UUID via model events
        PasswordResetToken::updateOrCreate(
            ['email' => $email],
            [
                'email' => $email,
                'token' => $this->hasher->make($token),
                'created_at' => now(),
            ]
        );

        return $token;
    }
}
