<?php

declare(strict_types=1);

namespace App\Http\Responses\Fortify;

use App\Services\Notifications\NotificationBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;

class SuccessfulPasswordResetLinkRequestResponse implements SuccessfulPasswordResetLinkRequestResponseContract
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
        $titleKey = \Illuminate\Support\Facades\Lang::has('authentication.forgot_password.success')
            ? 'authentication.forgot_password.success'
            : 'messages.auth.password_reset_link_sent';

        // Send notification to guest user (via session channel)
        NotificationBuilder::make()
            ->title($titleKey)
            ->info()
            ->send();

        $message = __($titleKey);

        return $request->wantsJson()
            ? response()->json(['message' => $message])
            : redirect()->back();
    }
}
