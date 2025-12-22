<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MapLoginIdentifier
{
    /**
     * Handle an incoming request.
     *
     * Maps 'identifier' field to 'email' for Fortify compatibility.
     * This must run before Fortify's validation, as Fortify expects
     * the field name to match config('fortify.username') which is 'email'.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isLoginRequest($request)) {
            $this->mapIdentifierToEmail($request);
        }

        return $next($request);
    }

    /**
     * Check if this is a login POST request.
     */
    private function isLoginRequest(Request $request): bool
    {
        return $request->is('login') && $request->isMethod('POST');
    }

    /**
     * Map 'identifier' field to 'email' for Fortify validation compatibility.
     */
    private function mapIdentifierToEmail(Request $request): void
    {
        if ($request->has('identifier') && ! $request->has('email')) {
            $request->merge(['email' => $request->input('identifier')]);
        }
    }
}
