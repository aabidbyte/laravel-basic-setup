## Routing Conventions

### Route File Organization

Routes are organized into subdirectories based on access level and domain:

```
routes/
├── api.php              # API routes
├── channels.php         # Broadcasting channels
├── console.php          # Console commands
├── web.php              # Main router (requires subdirectory files)
└── web/
    ├── auth/            # Authenticated routes (requires auth middleware)
    │   ├── admin.php        # Admin-level routes (error-logs, etc.)
    │   ├── dashboard.php    # Dashboard route only
    │   ├── error-logs.php   # Error log routes
    │   ├── notifications.php # Notification routes
    │   ├── settings.php     # Settings routes
    │   └── users.php        # User CRUD routes
    ├── dev/             # Development-only routes
    │   └── development.php  # Test error routes, debug tools
    └── public/          # Public routes (no auth)
        ├── activation.php   # Account activation
        └── preferences.php  # Theme/locale preferences
```

### Rules for Route Files

#### File Organization
1. **One domain per file** - Each route file should handle a single domain/feature (e.g., `users.php` for user CRUD, `settings.php` for settings)
2. **Group by access level** - Place routes in appropriate directory:
   - `public/` - Routes accessible without authentication (guest routes)
   - `auth/` - Routes requiring authentication (logged-in users)
   - `dev/` - Routes available only in local/development environments
3. **Admin routes in `admin.php`** - Routes requiring admin permissions should be required via `admin.php` or placed in dedicated admin subfiles
4. **Descriptive filenames** - Use plural nouns matching the resource (e.g., `users.php`, `notifications.php`, `settings.php`)

#### Naming Conventions
5. **Route names must match file structure** - Routes in `users.php` should use `users.` prefix (e.g., `users.index`, `users.create`, `users.show`)
6. **Use resource naming** - Follow RESTful conventions: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
7. **Prefix admin routes** - Use `admin.` prefix for admin-level routes (e.g., `admin.errors.index`)

#### Code Standards
8. **Always use named routes** - Never use URL strings directly, always define named routes and use `route()` helper
9. **Use permission constants** - Always use `Permissions::CONSTANT` from `App\Constants\Auth\Permissions`, never hardcoded strings
10. **Document route groups** - Add PHPDoc comment at top of each route file explaining its purpose

### Adding New Routes

#### Adding a new feature domain

1. Create a new file in the appropriate directory (e.g., `routes/web/auth/products.php`)
2. Add PHPDoc header explaining the domain
3. Define routes with consistent naming (e.g., `products.index`, `products.create`)
4. Require the file in `web.php` within the appropriate middleware group

**Example:**
```php
<?php

use App\Constants\Auth\Permissions;
use Illuminate\Support\Facades\Route;

/**
 * Product Management Routes
 *
 * CRUD routes for product management.
 * All routes require authentication and appropriate permissions.
 */
Route::view('/products', 'pages.products.index')
    ->middleware('can:' . Permissions::VIEW_PRODUCTS)
    ->name('products.index');

Route::livewire('/products/create', 'pages::products.create')
    ->middleware('can:' . Permissions::CREATE_PRODUCTS)
    ->name('products.create');
```

#### Adding development-only routes

Add routes to `routes/web/dev/development.php`. These are only loaded when:
```php
app()->environment('local', 'development')
```

#### Adding public routes

Add routes to a file in `routes/web/public/`. These are loaded without any middleware and accessible to guests.

### Route Types

| Method | Usage | Example |
|--------|-------|---------|
| `Route::livewire()` | Livewire full-page components | `Route::livewire('/users/create', 'pages::users.create')` |
| `Route::view()` | Static Blade views | `Route::view('/users', 'pages.users.index')` |
| `Route::get/post/etc.` | Controller actions | `Route::post('/preferences/theme', [PreferencesController::class, 'updateTheme'])` |
| `Route::redirect()` | Redirects | `Route::redirect('settings', 'settings/account')` |

### Middleware Usage

- **Authentication**: Applied at group level in `web.php` for all `auth/` routes
- **Permissions**: Applied per-route using `->middleware('can:' . Permissions::CONSTANT)`
- **Password Confirmation**: Use `->middleware(['password.confirm'])` for sensitive actions

### Best Practices

1. **Keep `web.php` minimal** - It should only require subdirectory files and define middleware groups
2. **Don't nest requires** - Only `admin.php` should require other files; avoid deep nesting
3. **Use Livewire routes** - Prefer `Route::livewire()` for interactive pages
4. **Test routes** - Run `php artisan route:list` after changes to verify registration

---

## Route Collision Prevention

### Automated Tests

The `tests/Feature/Routes/RoutesIntegrityTest.php` file contains tests that catch routing issues:

| Test | What it catches |
|------|-----------------|
| `has no duplicate route names` | Two routes with the same name |
| `has no route URI collisions for same HTTP method` | Two different routes with same URI pattern |
| `all routes have names` | Routes without names (all routes should be named) |
| `route names follow naming conventions` | Names not using lowercase.dot.notation |
| `protected routes have appropriate middleware` | Auth routes missing middleware |

### Running Route Tests

```bash
# Run all route tests
php artisan test --filter=RoutesIntegrity

# Run specific test
php artisan test --filter="has no duplicate route names"
```

### Common Collision Scenarios

**Scenario 1: Duplicate route names**
```php
// ❌ Both routes have name 'users.show'
Route::get('/users/{user}', ...)->name('users.show');
Route::get('/users/{uuid}', ...)->name('users.show');  // Collision!

// ✅ Use unique names
Route::get('/users/{user}', ...)->name('users.show');
Route::get('/users/{uuid}/profile', ...)->name('users.profile');
```

**Scenario 2: URI pattern collision**
```php
// ❌ Both match /users/anything
Route::get('/users/{user}', ...);  // Matches /users/123
Route::get('/users/{id}', ...);    // Also matches /users/123! Collision!

// ✅ Use different URI patterns or order carefully (specific first)
Route::get('/users/create', ...);  // Specific - put first
Route::get('/users/{user}', ...);  // Wildcard - put after
```

**Scenario 3: Method collision**
```php
// ✅ Different methods, same URI is OK
Route::get('/users/{user}', ...)->name('users.show');
Route::put('/users/{user}', ...)->name('users.update');
Route::delete('/users/{user}', ...)->name('users.destroy');
```

### Prevention Rules

1. **Always run tests after adding routes** - `php artisan test --filter=RoutesIntegrity`
2. **Use unique, descriptive names** - Follow `domain.action` pattern
3. **Order routes correctly** - Specific routes before wildcards
4. **Check route:list** - `php artisan route:list --name=users` to verify

