<?php

declare(strict_types=1);

namespace App\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Service for managing request idempotency.
 *
 * Handles fingerprint generation, lock acquisition/release, and configuration
 * to prevent duplicate request processing.
 */
class IdempotencyService
{
    /**
     * Generate a unique fingerprint for the request.
     *
     * The fingerprint is based on:
     * - User ID or session ID (identity)
     * - HTTP method (POST, PUT, etc.)
     * - Request path
     * - Request body (excluding CSRF token)
     *
     * @param  Request  $request  The incoming request
     * @return string SHA-256 hash of request content
     */
    public function generateFingerprint(Request $request): string
    {
        // Get user ID from request's user() method or fallback to session
        $user = $request->user();
        $identity = $user?->id ?? session()->getId();

        $components = [
            $identity,
            $request->method(),
            $request->path(),
            json_encode($request->except(['_token', '_method'])),
        ];

        return hash('sha256', implode('|', $components));
    }

    /**
     * Attempt to acquire a lock for the given fingerprint.
     *
     * Uses Redis atomic lock with a fallback TTL to prevent orphaned locks
     * if the process crashes before terminate() runs.
     *
     * @param  string  $fingerprint  The request fingerprint
     * @return bool True if lock was acquired, false if already locked
     */
    public function acquireLock(string $fingerprint): bool
    {
        // Default to application default cache driver (null) if not configured
        // This ensures tests use 'array' driver as defined in phpunit.xml
        $cacheStore = config('idempotency.cache_store');
        $fallbackTtl = config('idempotency.fallback_ttl', 60);

        $key = $this->getLockKey($fingerprint);

        // Try to acquire lock atomically (SETNX)
        return Cache::store($cacheStore)->add($key, true, $fallbackTtl);
    }

    /**
     * Release the lock for the given fingerprint.
     *
     * Called from middleware's terminate() method after response is sent.
     *
     * @param  string  $fingerprint  The request fingerprint
     */
    public function releaseLock(string $fingerprint): void
    {
        $cacheStore = config('idempotency.cache_store');
        $key = $this->getLockKey($fingerprint);

        Cache::store($cacheStore)->forget($key);
    }

    /**
     * Check if the idempotency system is enabled.
     *
     * @return bool True if enabled
     */
    public function isEnabled(): bool
    {
        return config('idempotency.enabled', true);
    }

    /**
     * Check if the request should be processed for idempotency.
     *
     * Excludes:
     * - GET, HEAD, OPTIONS requests (naturally idempotent)
     * - Excluded paths (horizon, telescope, debugbar, etc.)
     *
     * @param  Request  $request  The incoming request
     * @return bool True if request should be processed
     */
    public function shouldProcess(Request $request): bool
    {
        // Skip if disabled
        if (! $this->isEnabled()) {
            return false;
        }

        // Skip excluded methods
        $excludedMethods = config('idempotency.excluded_methods', ['GET', 'HEAD', 'OPTIONS']);
        if (in_array($request->method(), $excludedMethods, true)) {
            return false;
        }

        // Skip excluded paths
        $excludedPaths = config('idempotency.excluded_paths', []);
        foreach ($excludedPaths as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the cache key for the lock.
     *
     * @param  string  $fingerprint  The request fingerprint
     * @return string Cache key
     */
    private function getLockKey(string $fingerprint): string
    {
        return "idempotency:lock:{$fingerprint}";
    }
}
