<?php

declare(strict_types=1);

namespace App\Http\Responses\Fortify;

use App\Services\Notifications\NotificationBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;

class PasswordResetResponse implements PasswordResetResponseContract
{
    /**
     * The response status language key.
     */
    protected string $status;

    /**
     * Create a new response instance.
     */
    public function __construct(string $status)
    {
        $this->status = $status;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        $titleKey = \Illuminate\Support\Facades\Lang::has('authentication.reset_password.success')
            ? 'authentication.reset_password.success'
            : 'messages.auth.password_reset';

        // Send notification to guest user (via session channel)
        NotificationBuilder::make()
            ->title($titleKey)
            ->success()
            ->send();

        $message = __($titleKey);

        return $request->wantsJson()
            ? response()->json(['message' => $message])
            : redirect()->route('login');
    }
}
