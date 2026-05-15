<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CentralImpersonationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        abort_unless(\in_array($request->getHost(), config('tenancy.central_domains', []), true), 403);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
