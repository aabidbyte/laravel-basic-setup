## Cache Management

### Automatic Cache Refresh

Cache is automatically reset when using built-in functions:

```php
// These automatically clear cache
$role->givePermissionTo(Permissions::EDIT_ARTICLE);
$role->revokePermissionTo(Permissions::EDIT_ARTICLE);
$role->syncPermissions([...]);
$permission->assignRole(Roles::WRITER);
$permission->removeRole(Roles::WRITER);
$permission->syncRoles([...]);
```

**Note**: User-specific assignments (like `$user->assignRole()`) are kept in-memory and don't trigger cache resets since v5.1.0.

### Manual Cache Reset

```php
app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
```

Or via Artisan:

```bash
php artisan permission:cache-reset
```

### Cache Configuration

-   **Expiration Time**: 24 hours (configurable in `config/permission.php`)
-   **Cache Key**: `spatie.permission.cache` (don't change unless necessary)
-   **Cache Store**: Uses Laravel's default cache store (configurable)

### Disabling Cache

For development/testing:

```php
// In config/permission.php
'cache' => [
    'store' => 'array', // Effectively disables caching between requests
],
```

Or set `CACHE_DRIVER=array` in `.env` (don't use in production).

