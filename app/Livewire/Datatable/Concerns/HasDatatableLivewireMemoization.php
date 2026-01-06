<?php

declare(strict_types=1);

namespace App\Livewire\DataTable\Concerns;

use App\Models\User;
use Closure;

/**
 * Provides request-scoped memoization for expensive DataTable computations.
 *
 * Values are cached for the duration of a single Livewire request and
 * automatically cleared during dehydration.
 */
trait HasDatatableLivewireMemoization
{
    /**
     * Request-scoped cache storage.
     *
     * @var array<string, mixed>
     */
    protected array $memoized = [];

    /**
     * Memoize a computation by key.
     *
     * The closure is only executed once per request. Subsequent calls
     * with the same key return the cached result.
     */
    protected function memoize(string $key, Closure $callback): mixed
    {
        return $this->memoized[$key] ??= $callback();
    }

    /**
     * Clear memoized cache.
     *
     * Called automatically by Livewire during component dehydration.
     */
    public function dehydrate(): void
    {
        $this->memoized = [];
    }

    /**
     * Get the authenticated user with permissions pre-loaded (memoized).
     *
     * Loads permissions once per request to avoid N+1 queries.
     */
    protected function cachedUser(): ?User
    {
        return $this->memoize('auth:user', function () {
            $user = auth()->user();

            if ($user instanceof User) {
                // Pre-load permissions to avoid lazy loading
                $user->loadMissing('permissions', 'roles.permissions');
            }

            return $user;
        });
    }
}
