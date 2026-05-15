<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseDomainAuthGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = tenancy()->initialized ? 'tenant' : 'web';
        $passwordBroker = tenancy()->initialized ? 'tenant_users' : 'users';

        config([
            'auth.defaults.guard' => $guard,
            'auth.defaults.passwords' => $passwordBroker,
            'fortify.guard' => $guard,
            'fortify.passwords' => $passwordBroker,
        ]);

        return $next($request);
    }
}
