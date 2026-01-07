<?php

/**
 * Clear role/permission caches.
 *
 * This helper centralizes cache clearing logic used across seeders
 * and other parts of the application. With custom RBAC, this clears
 * Laravel's cache for roles and permissions queries.
 *
 * Note: With our simple RBAC, caching is handled by Laravel's built-in
 * relationship caching. This function is kept for API compatibility
 * with existing code but may be a no-op in most cases.
 */
function clearPermissionCache(): void
{
    // Clear any cached queries for roles/permissions
    // Laravel handles relationship caching automatically
    // This function is kept for backwards compatibility with seeders
}
