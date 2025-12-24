## Events

Events are **disabled by default**. Enable in `config/permission.php`:

```php
'events_enabled' => true,
```

### Available Events

-   `\Spatie\Permission\Events\RoleAttached::class`
-   `\Spatie\Permission\Events\RoleDetached::class`
-   `\Spatie\Permission\Events\PermissionAttached::class`
-   `\Spatie\Permission\Events\PermissionDetached::class`

