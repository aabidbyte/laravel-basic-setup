<?php

declare(strict_types=1);

namespace App\Http\Middleware\Preferences;

use App\Services\FrontendPreferences\FrontendPreferencesService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyFrontendPreferences
{
    public function __construct(
        private readonly FrontendPreferencesService $preferences,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Apply locale (pass request for browser detection on first visit)
        $locale = $this->preferences->getLocale($request);
        app()->setLocale($locale);

        // Note: Timezone preference is for display only (used in date/time formatting helpers)
        // Database storage always uses the application timezone from config/app.php
        // Note: Theme preference uses "system" by default, which lets DaisyUI respect OS preference

        return $next($request);
    }
}
