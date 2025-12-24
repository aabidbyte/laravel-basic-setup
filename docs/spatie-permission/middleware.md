## Middleware

### Registering Middleware

In `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);
})
```

### Using Middleware in Routes

```php
use App\Constants\Roles;
use App\Constants\Permissions;

// Single permission
Route::group(['middleware' => ['permission:'.Permissions::PUBLISH_ARTICLE]], function () {
    // ...
});

// Multiple permissions (OR logic)
Route::group(['middleware' => ['permission:'.Permissions::PUBLISH_ARTICLE.'|'.Permissions::EDIT_ARTICLE]], function () {
    // ...
});

// Single role
Route::group(['middleware' => ['role:'.Roles::MANAGER]], function () {
    // ...
});

// Multiple roles (OR logic)
Route::group(['middleware' => ['role:'.Roles::MANAGER.'|'.Roles::WRITER]], function () {
    // ...
});

// Role or permission
Route::group(['middleware' => ['role_or_permission:'.Roles::MANAGER.'|'.Permissions::EDIT_ARTICLE]], function () {
    // ...
});
```

### Using Middleware in Controllers

Laravel 11+ (using `HasMiddleware` interface):

```php
use App\Constants\Roles;
use App\Constants\Permissions;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

public static function middleware(): array
{
    return [
        'role_or_permission:'.Roles::MANAGER.'|'.Permissions::EDIT_ARTICLE,
        new Middleware('role:'.Roles::WRITER, only: ['index']),
        new Middleware(\Spatie\Permission\Middleware\RoleMiddleware::using(Roles::MANAGER), except: ['show']),
    ];
}
```

Laravel 10 and older (in constructor):

```php
public function __construct()
{
    $this->middleware(['role:'.Roles::MANAGER, 'permission:'.Permissions::PUBLISH_ARTICLE.'|'.Permissions::EDIT_ARTICLE]);
}
```

### Middleware Priority

If you get 404 instead of 403, adjust middleware priority. The `TeamsPermission` middleware is already configured to run before `SubstituteBindings` in `AppServiceProvider`.

