<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveRequestIdentifier
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isForgotPasswordRequest($request) || $this->isResetPasswordRequest($request)) {
            // For forgot password and reset password, we must resolve the real email because
            // the PasswordBroker uses standard retrieval which expects a valid email.
            $this->resolveIdentifierToEmail($request);
        }

        return $next($request);
    }

    /**
     * Check if this is a forgot password POST request.
     */
    private function isForgotPasswordRequest(Request $request): bool
    {
        return $request->is('forgot-password') && $request->isMethod('POST');
    }

    /**
     * Check if this is a reset password POST request.
     */
    private function isResetPasswordRequest(Request $request): bool
    {
        return $request->is('reset-password') && $request->isMethod('POST');
    }

    /**
     * Resolve 'identifier' (username/email) to real email address.
     */
    private function resolveIdentifierToEmail(Request $request): void
    {
        if ($request->has('identifier') && ! $request->has('email')) {
            $identifier = $request->input('identifier');

            // Try to find user by identifier
            $user = \App\Models\User::findByIdentifier($identifier)->first();

            if ($user && $user->email) {
                // Found user, use their real email
                $request->merge(['email' => $user->email]);
            } else {
                // User not found, pass identifier as email to trigger "user not found" error
                $request->merge(['email' => $identifier]);
            }
        }
    }
}
