<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Log the current user out of the application.
     */
    public function logout(): RedirectResponse
    {
        $this->performLogout();

        return redirect('/');
    }

    /**
     * Perform logout actions without redirecting.
     * Useful when called from Livewire components that handle redirects themselves.
     */
    public function performLogout(): void
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }

    /**
     * Show the activation form.
     */
    public function showActivationForm(string $token)
    {
        $activationService = app(\App\Services\Users\ActivationService::class);
        $user = $activationService->findUserByToken($token);
        $tokenValid = $user !== null;

        return view('pages.auth.activate', compact('token', 'user', 'tokenValid'));
    }

    /**
     * Handle the activation request.
     */
    public function activate(\Illuminate\Http\Request $request, string $token)
    {
        $activationService = app(\App\Services\Users\ActivationService::class);
        $user = $activationService->findUserByToken($token);

        if (! $user) {
            \App\Services\Notifications\NotificationBuilder::make()
                ->title('authentication.activation.invalid_token')
                ->error()
                ->send();

            return back();
        }

        $request->validate([
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ]);

        try {
            $activationService->activateWithPassword($user, $request->password, $token);

            \App\Services\Notifications\NotificationBuilder::make()
                ->title('authentication.activation.success')
                ->success()
                ->send();

            return redirect()->route('login')->with('activated', true);
        } catch (Exception $e) {
            \App\Services\Notifications\NotificationBuilder::make()
                ->title('authentication.activation.error')
                ->content($e->getMessage())
                ->error()
                ->send();

            return back();
        }
    }
}
