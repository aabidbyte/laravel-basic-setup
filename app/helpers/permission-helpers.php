<?php

/**
 * Clear Spatie Permission cache.
 *
 * This helper centralizes permission cache clearing logic used across seeders
 * and other parts of the application. It ensures the permission cache is cleared
 * after role/permission modifications to prevent stale data.
 */
function clearPermissionCache(): void
{
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
}
