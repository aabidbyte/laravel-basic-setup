<?php

namespace App\Http\Middleware\Teams;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
    public function handle(Request $request, Closure $next, ?string $permissions = null): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Set tenant context if not present
            if (! session()->has('tenant_id')) {
                $firstTenant = $user->tenants()->orderBy('tenants.id')->first();
                if ($firstTenant) {
                    session(['tenant_id' => $firstTenant->id]);
                }
            }

            // Check permissions if provided
            if ($permissions) {
                $permissionList = explode('|', str_replace(',', '|', $permissions));

                $hasPermission = collect($permissionList)->some(function ($permission) {
                    return Gate::allows(trim($permission));
                });

                if (! $hasPermission) {
                    abort(403, 'Unauthorized.');
                }
            }
        }

        return $next($request);
    }
}
