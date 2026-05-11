<?php

namespace App\Http\Middleware\Teams;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to set the active tenant context for the request.
 *
 * This middleware ensures the tenant_id is available in the session
 * for tenant-based access control throughout the request lifecycle.
 */
class TeamsPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // If no tenant_id in session, set from user's first tenant
            if (! session()->has('tenant_id')) {
                $firstTenant = $user->tenants()->orderBy('tenants.id')->first();
                if ($firstTenant) {
                    session(['tenant_id' => $firstTenant->id]);
                }
            }
        }

        return $next($request);
    }
}
