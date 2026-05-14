<?php

declare(strict_types=1);

namespace App\Http\Middleware\Tenancy;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenancy route-stack placeholder: forwards the request unchanged.
 *
 * stancl/tenancy registers tenant routes with a predictable middleware name; this class
 * keeps that stack valid for central routes that share the same pipeline shape without
 * applying tenant initialization here.
 */
class UniversalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
