<?php

declare(strict_types=1);

namespace App\Http\Middleware\Tenancy;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Symfony\Component\HttpFoundation\Response;

/**
 * Initializes tenancy for non-central domains while letting central domains pass through.
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
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        if (tenancy()->initialized || \in_array($host, $centralDomains, true)) {
            return $next($request);
        }

        /** @var InitializeTenancyByDomain $middleware */
        $middleware = app(InitializeTenancyByDomain::class);

        return $middleware->handle($request, $next);
    }
}
