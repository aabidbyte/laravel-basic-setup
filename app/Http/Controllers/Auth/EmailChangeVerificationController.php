<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class EmailChangeVerificationController extends Controller
{
    /**
     * Verify the email change token.
     */
    public function verify(string $token): RedirectResponse
    {
        $hashedToken = hash('sha256', $token);

        /** @var User|null $user */
        $user = User::where('pending_email_token', $hashedToken)->first();

        if (! $user) {
            NotificationBuilder::make()
                ->title('Error')
                ->content('Invalid email verification link.')
                ->error()
                ->send();

            return redirect()->route('login');
        }

        if ($user->isPendingEmailExpired()) {
            NotificationBuilder::make()
                ->title('Error')
                ->content('This email verification link has expired.')
                ->error()
                ->send();

            return redirect()->route('login');
        }

        DB::transaction(function () use ($user) {
            $user->confirmPendingEmail();
        });

        NotificationBuilder::make()
            ->title('Success')
            ->content('Your email has been successfully updated.')
            ->success()
            ->send();

        return redirect()->route('login');
    }
}
