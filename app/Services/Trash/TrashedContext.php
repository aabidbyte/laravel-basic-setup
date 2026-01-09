<?php

declare(strict_types=1);

namespace App\Services\Trash;

/**
 * Static context service for trash viewing state.
 *
 * This service allows clean URLs by maintaining the "viewing trashed" state
 * internally via a static variable. Middleware enables this context when
 * accessing trash routes, allowing existing show pages to use withTrashed()
 * queries without URL changes.
 *
 * Usage:
 * - Middleware calls TrashedContext::enable() on trash routes
 * - Show pages check TrashedContext::isActive() to decide whether to include trashed
 * - Context is automatically reset for each request (static, not session)
 */
class TrashedContext
{
    /**
     * Whether we're currently viewing trashed items.
     */
    private static bool $viewingTrashed = false;

    /**
     * The entity type being viewed (e.g., 'users', 'roles').
     */
    private static ?string $entityType = null;

    /**
     * Enable trash viewing context.
     */
    public static function enable(?string $entityType = null): void
    {
        self::$viewingTrashed = true;
        self::$entityType = $entityType;
    }

    /**
     * Disable trash viewing context.
     */
    public static function disable(): void
    {
        self::$viewingTrashed = false;
        self::$entityType = null;
    }

    /**
     * Check if trash viewing is active.
     */
    public static function isActive(): bool
    {
        return self::$viewingTrashed;
    }

    /**
     * Get the current entity type being viewed.
     */
    public static function getEntityType(): ?string
    {
        return self::$entityType;
    }

    /**
     * Reset the context (useful for testing).
     */
    public static function reset(): void
    {
        self::$viewingTrashed = false;
        self::$entityType = null;
    }
}
