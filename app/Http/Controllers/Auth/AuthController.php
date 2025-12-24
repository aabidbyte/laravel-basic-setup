<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
}
