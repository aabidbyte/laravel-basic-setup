<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Stop impersonating and return to the original user.
     */
    public function stop(Request $request): RedirectResponse
    {
        $session = $request->session();

        if (! $session->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $impersonatorId = (int) $session->pull('impersonator_id');

        if ($impersonatorId === (int) Auth::id()) {
            return redirect()->route('dashboard');
        }

        $admin = User::find($impersonatorId);

        if (! $admin instanceof User) {
            Auth::logout();
            $session->invalidate();
            $session->regenerateToken();

            return redirect()->route('login');
        }

        Auth::login($admin);
        $session->regenerate();

        return redirect()->route('dashboard');
    }
}
