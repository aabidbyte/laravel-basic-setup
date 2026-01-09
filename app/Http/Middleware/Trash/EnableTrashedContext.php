<?php

declare(strict_types=1);

namespace App\Http\Middleware\Trash;

use App\Services\Trash\TrashedContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enable the TrashedContext for trash routes.
 *
 * This middleware sets the static TrashedContext to indicate we're viewing
 * trashed items, allowing existing show pages to use withTrashed() queries
 * without URL changes.
 */
class EnableTrashedContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get entity type from route parameter
        $entityType = $request->route('entityType');

        // Enable the trashed context
        TrashedContext::enable($entityType);

        return $next($request);
    }
}
