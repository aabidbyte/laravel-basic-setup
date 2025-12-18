<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Notifications\NotificationBuilder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ConvertStatusToNotification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip Livewire requests - they handle their own notifications
        // Livewire requests can be detected by checking for Livewire-specific headers or content type
        if ($this->isLivewireRequest($request)) {
            return $response;
        }

        // Only convert status to notification for authenticated users
        if (! Auth::check()) {
            return $response;
        }

        $status = session('status');

        if (! $status) {
            return $response;
        }

        // Map Fortify status messages to notifications
        $this->convertStatusToNotification($status);

        // Clear the session status after converting to prevent duplicates
        session()->forget('status');

        return $response;
    }

    /**
     * Check if the request is from Livewire.
     */
    protected function isLivewireRequest(Request $request): bool
    {
        // Check for Livewire-specific headers
        if ($request->header('X-Livewire') || $request->header('X-Livewire-Request')) {
            return true;
        }

        // Check if the request path contains /livewire/
        if (str_contains($request->path(), 'livewire')) {
            return true;
        }

        // Check if it's a POST request with Livewire component data (common pattern)
        if ($request->isMethod('POST') && $request->has('components')) {
            return true;
        }

        return false;
    }

    /**
     * Convert session status to notification.
     */
    protected function convertStatusToNotification(string $status): void
    {
        $mappings = [
            'verification-link-sent' => [
                'title' => __('ui.auth.verify_email.resend_success'),
                'type' => 'info',
            ],
            'password-reset' => [
                'title' => __('ui.auth.reset_password.success') ?: __('messages.auth.password_reset'),
                'type' => 'success',
            ],
            'password-reset-link-sent' => [
                'title' => __('ui.auth.forgot_password.success') ?: __('messages.auth.password_reset_link_sent'),
                'type' => 'info',
            ],
        ];

        if (! isset($mappings[$status])) {
            // Default: show as info notification
            NotificationBuilder::make()
                ->title($status)
                ->info()
                ->send();

            return;
        }

        $mapping = $mappings[$status];
        $builder = NotificationBuilder::make()->title($mapping['title']);

        match ($mapping['type']) {
            'success' => $builder->success(),
            'info' => $builder->info(),
            'warning' => $builder->warning(),
            'error' => $builder->error(),
            default => $builder->info(),
        };

        $builder->send();
    }
}
