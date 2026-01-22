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
                ->title(__('actions.error'))
                ->content(__('authentication.verify_email.invalid_token'))
                ->error()
                ->send();

            return redirect()->route('login');
        }

        if ($user->isPendingEmailExpired()) {
            NotificationBuilder::make()
                ->title(__('actions.error'))
                ->content(__('authentication.verify_email.expired_token'))
                ->error()
                ->send();

            return redirect()->route('login');
        }

        DB::transaction(function () use ($user) {
            $user->confirmPendingEmail();
        });

        NotificationBuilder::make()
            ->title(__('actions.success'))
            ->content(__('authentication.verify_email.success'))
            ->success()
            ->send();

        return redirect()->route('login');
    }
}
