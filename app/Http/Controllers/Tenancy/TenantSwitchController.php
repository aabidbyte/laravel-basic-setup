<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Constants\Auth\Roles;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Tenancy\UserImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantSwitchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Tenant $tenant): RedirectResponse
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403, 'You do not have access to this organization.');
        }

        if (! $user->hasRole(Roles::SUPER_ADMIN) && ! $user->tenants->contains($tenant)) {
            abort(403, 'You do not have access to this organization.');
        }

        // Use Impersonation for seamless switch without re-login
        $impersonation = app(UserImpersonationService::class)->execute($user, $user, $tenant);

        if ($impersonation['type'] === 'tenant') {
            return redirect($impersonation['url']);
        }

        return redirect('/dashboard');
    }
}
