<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Stop impersonating and return to the original user.
     */
    public function stop(): RedirectResponse
    {
        if (! session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $adminId = session()->pull('impersonator_id');
        $admin = User::find($adminId);

        if ($admin) {
            Auth::login($admin);
        } else {
            Auth::logout();

            return redirect()->route('login');
        }

        return redirect()->route('dashboard');
    }
}
