<?php

declare(strict_types=1);

namespace App\Http\Responses\Fortify;

use App\Services\Notifications\NotificationBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse as EmailVerificationNotificationSentResponseContract;

class EmailVerificationNotificationSentResponse implements EmailVerificationNotificationSentResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        // Send notification to guest user (via session channel)
        NotificationBuilder::make()
            ->title(__('ui.auth.verify_email.resend_success'))
            ->info()
            ->send();

        return $request->wantsJson()
            ? response()->json(['message' => __('ui.auth.verify_email.resend_success')])
            : redirect()->back();
    }
}
