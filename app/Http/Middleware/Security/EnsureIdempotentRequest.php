<?php

declare(strict_types=1);

namespace App\Http\Middleware\Security;

use App\Services\Notifications\NotificationBuilder;
use App\Services\Security\IdempotencyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure request idempotency.
 *
 * Prevents duplicate request processing by using content-based fingerprinting
 * and Redis locks. Lock is released immediately after request completes
 * (success or failure) to allow legitimate retries.
 *
 * The lock only blocks concurrent duplicate requests (mid-flight).
 * Once the first request completes, subsequent identical requests are allowed.
 */
class EnsureIdempotentRequest
{
    /**
     * The fingerprint of the current request.
     */
    private ?string $fingerprint = null;

    /**
     * Whether the lock was acquired by this request.
     */
    private bool $lockAcquired = false;

    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected IdempotencyService $idempotencyService,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if not applicable
        if (! $this->idempotencyService->shouldProcess($request)) {
            return $next($request);
        }

        // Generate fingerprint
        $this->fingerprint = $this->idempotencyService->generateFingerprint($request);

        // Try to acquire lock
        if (! $this->idempotencyService->acquireLock($this->fingerprint)) {
            // Lock already exists - duplicate request is currently processing
            return $this->handleDuplicateRequest($request);
        }

        $this->lockAcquired = true;

        // Process request and release lock immediately after completion
        try {
            return $next($request);
        } finally {
            // Release lock immediately after request completes (success or failure)
            // This allows legitimate retries once the first request finishes
            $this->releaseLockIfAcquired();
        }
    }

    /**
     * Handle duplicate request detection.
     *
     * For web requests: Show error notification + return 202 Accepted
     * For API requests: Return 409 JSON response
     *
     * @param  Request  $request  The duplicate request
     */
    private function handleDuplicateRequest(Request $request): Response
    {
        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => __('errors.idempotency.api_message'),
                'code' => 'DUPLICATE_REQUEST',
            ], 409);
        }

        // Web request - show notification and redirect back
        NotificationBuilder::make()
            ->title(__('errors.idempotency.title'))
            ->subtitle(__('errors.idempotency.subtitle'))
            ->error()
            ->send();

        return redirect()->back();
    }

    /**
     * Release the lock if it was acquired by this request.
     */
    private function releaseLockIfAcquired(): void
    {
        if ($this->lockAcquired && $this->fingerprint !== null) {
            $this->idempotencyService->releaseLock($this->fingerprint);
            $this->lockAcquired = false;
        }
    }

    /**
     * Perform any final actions for the request lifecycle.
     *
     * This is a backup in case the try-finally block doesn't execute
     * (e.g., process crash). The lock should already be released by
     * the time this is called.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Backup release (should be no-op if already released in handle())
        $this->releaseLockIfAcquired();
    }
}
