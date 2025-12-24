## Artisan Commands

### Creating Roles and Permissions

```bash
# Create role
php artisan permission:create-role writer

# Create permission
php artisan permission:create-permission "edit articles"

# With specific guard
php artisan permission:create-role writer web
php artisan permission:create-permission "edit articles" web

# Create role with permissions
php artisan permission:create-role writer web "create articles|edit articles"

# With team ID (when teams enabled)
php artisan permission:create-role --team-id=1 writer
```

### Displaying Roles and Permissions

```bash
php artisan permission:show
```

### Resetting Cache

```bash
php artisan permission:cache-reset
```

