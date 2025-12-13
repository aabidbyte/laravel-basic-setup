# Laravel Spatie Permission Documentation

This document contains all rules, best practices, and guidelines for using the Spatie Permission package in this application.

## Table of Contents

-   [Overview](#overview)
-   [Configuration](#configuration)
-   [Best Practices](#best-practices)
-   [Roles vs Permissions](#roles-vs-permissions)
-   [No Hardcoded Strings Rule](#no-hardcoded-strings-rule)
-   [Model Policies](#model-policies)
-   [Teams Permissions](#teams-permissions)
-   [Performance Tips](#performance-tips)
-   [Testing](#testing)
-   [Database Seeding](#database-seeding)
-   [Cache Management](#cache-management)
-   [Events](#events)
-   [Middleware](#middleware)
-   [Defining Super-Admin](#defining-super-admin)
-   [Multiple Guards](#multiple-guards)
-   [Artisan Commands](#artisan-commands)
-   [Extending Models](#extending-models)
-   [Exceptions](#exceptions)

## Overview

This application uses **Spatie Permission v6.23** for role and permission management. The package is configured to work with UUID-based User models and includes teams permissions support.

### Key Features

-   **UUID Support**: Configured to use `model_uuid` instead of `model_id` for UUID-based User models
-   **Teams Permissions**: Enabled by default for multi-tenant scenarios
-   **User Model**: `App\Models\User` includes the `HasRoles` trait
-   **Configuration**: `config/permission.php`
-   **Migration**: Modified to support UUIDs in pivot tables

### Important Constraints

⚠️ **CRITICAL**: The User model must NOT have:

-   `role` or `roles` property/relation/method
-   `permission` or `permissions` property/relation/method

These will interfere with the package's functionality.

## Configuration

### Config File

Location: `config/permission.php`

Key settings:

-   `teams`: `true` (enabled)
-   `model_morph_key`: `'model_uuid'` (for UUID support)
-   `team_foreign_key`: `'team_id'` (default, can be customized)
-   `cache.expiration_time`: `\DateInterval::createFromDateString('24 hours')`
-   `events_enabled`: `false` (by default)

### Middleware

-   **Location**: `app/Http/Middleware/TeamsPermission.php`
-   **Registration**: Registered in `bootstrap/app.php` web middleware group
-   **Priority**: Set in `AppServiceProvider` to run before `SubstituteBindings`

## Best Practices

### Roles vs Permissions

**CRITICAL RULE**: Always check for **permissions**, not roles, whenever possible.

#### The Hierarchy

```
Users → Roles → Permissions
```

-   **Users have roles** - Roles group users by sets of permissions
-   **Roles have permissions** - Permissions are assigned to roles
-   **App checks permissions** - Always check for specific permissions, not roles

#### Why This Matters

1. **Granular Control**: Permissions like `'view document'` and `'edit document'` allow fine-grained access control
2. **Flexibility**: You can change role names without breaking your application logic
3. **Laravel Integration**: Works seamlessly with Laravel's `@can` and `can()` directives
4. **View Control**: Easier to show/hide UI elements based on specific permissions

#### Examples

✅ **GOOD** - Checking permissions:

```php
// In views
@can('view member addresses')
    // Show address section
@endcan

@can('edit document')
    <button>Edit</button>
@endcan

// In controllers
if ($user->can('edit posts')) {
    // Allow editing
}

// In policies
public function update(User $user, Post $post): bool
{
    return $user->can('edit posts');
}
```

❌ **BAD** - Checking roles:

```php
// Don't do this in views/controllers
if ($user->hasRole('Editor')) {
    // This is less flexible
}
```

#### When to Check Roles

Roles should only be checked in:

-   **Middleware** (sometimes)
-   **Route groups** (sometimes)
-   **Gate::before()** rules (for Super-Admin)
-   **Policy::before()** methods (for Super-Admin)

**Summary**:

-   Users have roles
-   Roles have permissions
-   App always checks for permissions (as much as possible), not roles
-   Views check permission-names
-   Policies check permission-names
-   Model policies check permission-names
-   Controller methods check permission-names
-   Middleware check permission names, or sometimes role-names
-   Routes check permission-names, or maybe role-names if you need to code that way

## No Hardcoded Strings Rule

⚠️ **CRITICAL RULE**: **NO HARDCODED STRINGS ARE ALLOWED** for role and permission names.

### Always Use Constants

All role and permission names must be defined as constants in designated classes.

### Creating Permission Constants Class

Create a dedicated class for permission constants:

```php
<?php

namespace App\Constants;

class Permissions
{
    // Document permissions
    public const VIEW_DOCUMENT = 'view document';
    public const EDIT_DOCUMENT = 'edit document';
    public const DELETE_DOCUMENT = 'delete document';
    public const PUBLISH_DOCUMENT = 'publish document';
    public const UNPUBLISH_DOCUMENT = 'unpublish document';

    // Article permissions
    public const CREATE_ARTICLE = 'create articles';
    public const EDIT_ARTICLE = 'edit articles';
    public const DELETE_ARTICLE = 'delete articles';
    public const PUBLISH_ARTICLE = 'publish articles';
    public const UNPUBLISH_ARTICLE = 'unpublish articles';
    public const VIEW_UNPUBLISHED_ARTICLE = 'view unpublished articles';
    public const EDIT_ALL_ARTICLES = 'edit all articles';
    public const EDIT_OWN_ARTICLES = 'edit own articles';
    public const DELETE_ANY_ARTICLE = 'delete any post';
    public const DELETE_OWN_ARTICLES = 'delete own posts';

    // Member permissions
    public const VIEW_MEMBER_ADDRESSES = 'view member addresses';

    // Post permissions
    public const RESTORE_POSTS = 'restore posts';
    public const FORCE_DELETE_POSTS = 'force delete posts';
    public const CREATE_POST = 'create a post';
    public const UPDATE_POST = 'update a post';
    public const DELETE_POST = 'delete a post';
    public const VIEW_ALL_POSTS = 'view all posts';
    public const VIEW_POST = 'view a post';

    /**
     * Get all permission constants as an array.
     */
    public static function all(): array
    {
        return [
            self::VIEW_DOCUMENT,
            self::EDIT_DOCUMENT,
            self::DELETE_DOCUMENT,
            self::PUBLISH_DOCUMENT,
            self::UNPUBLISH_DOCUMENT,
            self::CREATE_ARTICLE,
            self::EDIT_ARTICLE,
            self::DELETE_ARTICLE,
            self::PUBLISH_ARTICLE,
            self::UNPUBLISH_ARTICLE,
            self::VIEW_UNPUBLISHED_ARTICLE,
            self::EDIT_ALL_ARTICLES,
            self::EDIT_OWN_ARTICLES,
            self::DELETE_ANY_ARTICLE,
            self::DELETE_OWN_ARTICLES,
            self::VIEW_MEMBER_ADDRESSES,
            self::RESTORE_POSTS,
            self::FORCE_DELETE_POSTS,
            self::CREATE_POST,
            self::UPDATE_POST,
            self::DELETE_POST,
            self::VIEW_ALL_POSTS,
            self::VIEW_POST,
        ];
    }
}
```

### Creating Role Constants Class

Create a dedicated class for role constants:

```php
<?php

namespace App\Constants;

class Roles
{
    public const SUPER_ADMIN = 'Super Admin';
    public const ADMIN = 'admin';
    public const WRITER = 'writer';
    public const EDITOR = 'editor';
    public const MODERATOR = 'moderator';
    public const READER = 'reader';
    public const REVIEWER = 'reviewer';
    public const MANAGER = 'manager';
    public const VIEWER = 'viewer';
    public const MEMBER = 'Member';
    public const ACTIVE_MEMBER = 'ActiveMember';

    /**
     * Get all role constants as an array.
     */
    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::WRITER,
            self::EDITOR,
            self::MODERATOR,
            self::READER,
            self::REVIEWER,
            self::MANAGER,
            self::VIEWER,
            self::MEMBER,
            self::ACTIVE_MEMBER,
        ];
    }
}
```

### Usage Examples

✅ **GOOD** - Using constants:

```php
use App\Constants\Permissions;
use App\Constants\Roles;

// Creating permissions
Permission::create(['name' => Permissions::EDIT_ARTICLE]);

// Creating roles
Role::create(['name' => Roles::WRITER]);

// Assigning permissions to roles
$role->givePermissionTo(Permissions::EDIT_ARTICLE);

// Assigning roles to users
$user->assignRole(Roles::WRITER);

// Checking permissions
if ($user->can(Permissions::EDIT_ARTICLE)) {
    // ...
}

// In views
@can(Permissions::VIEW_DOCUMENT)
    // ...
@endcan

// In policies
public function update(User $user, Post $post): bool
{
    return $user->can(Permissions::EDIT_ARTICLE);
}

// In middleware
Route::group(['middleware' => ['permission:'.Permissions::PUBLISH_ARTICLE]], function () {
    // ...
});
```

❌ **BAD** - Using hardcoded strings:

```php
// Don't do this!
Permission::create(['name' => 'edit articles']);
$user->assignRole('writer');
if ($user->can('edit articles')) { }
@can('view document')
```

### Benefits

1. **Type Safety**: IDE autocomplete and type checking
2. **Refactoring**: Easy to rename permissions/roles across the codebase
3. **Documentation**: Constants serve as documentation
4. **Consistency**: Prevents typos and inconsistencies
5. **Maintainability**: Single source of truth for permission/role names

## Model Policies

**Best Practice**: Use Laravel's Model Policies for access control.

### Example Policy

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Constants\Permissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function view(?User $user, Post $post): bool
    {
        if ($post->published) {
            return true;
        }

        // visitors cannot view unpublished items
        if ($user === null) {
            return false;
        }

        // admin overrides published status
        if ($user->can(Permissions::VIEW_UNPUBLISHED_ARTICLE)) {
            return true;
        }

        // authors can view their own unpublished posts
        return $user->id == $post->user_id;
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::CREATE_ARTICLE);
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->can(Permissions::EDIT_ALL_ARTICLES)) {
            return true;
        }

        if ($user->can(Permissions::EDIT_OWN_ARTICLES)) {
            return $user->id == $post->user_id;
        }

        return false;
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->can(Permissions::DELETE_ANY_ARTICLE)) {
            return true;
        }

        if ($user->can(Permissions::DELETE_OWN_ARTICLES)) {
            return $user->id == $post->user_id;
        }

        return false;
    }
}
```

## Teams Permissions

Teams permissions are **enabled by default** in this application.

### Configuration

-   **Enabled**: `config/permission.php` → `'teams' => true`
-   **Team Foreign Key**: `'team_id'` (default, can be customized)
-   **Middleware**: `app/Http/Middleware/TeamsPermission.php`

### Setting Team ID

On login, set the team ID in session:

```php
session(['team_id' => $team->id]);
```

The middleware automatically sets the team ID from session on each request.

### Creating Roles with Teams

```php
use App\Constants\Roles;

// Global role (can be assigned to any team)
Role::create(['name' => Roles::WRITER, 'team_id' => null]);

// Team-specific role
Role::create(['name' => Roles::READER, 'team_id' => 1]);

// Role without team_id uses default global team_id
Role::create(['name' => Roles::REVIEWER]);
```

### Switching Teams

When switching teams, always unset cached relations:

```php
use App\Constants\Roles;
use App\Constants\Permissions;

// Set active global team_id
setPermissionsTeamId($new_team_id);

// Unset cached model relations so new team relations will get reloaded
$user->unsetRelation('roles')->unsetRelation('permissions');

// Now you can check:
$roles = $user->roles;
$hasRole = $user->hasRole(Roles::WRITER);
$user->hasPermissionTo(Permissions::EDIT_ARTICLE);
$user->can(Permissions::VIEW_DOCUMENT);
```

## Performance Tips

### Small Apps

On small apps, most performance optimizations are unnecessary.

### Large Apps

1. **Lookup Permission First**: Instead of `$role->givePermissionTo()`, try `$permission->assignRole($role)` for better performance when frequently adding/removing permissions.

2. **Use `make()` + `saveOrFail()`**: For large datasets, instead of:

    ```php
    Permission::create([...]);
    ```

    Use:

    ```php
    $permission = Permission::make([...]);
    $permission->saveOrFail();
    ```

3. **Bulk Inserts**: For seeding large quantities, use `insert()` instead of `create()`:

    ```php
    $permissions = collect($permissionNames)->map(function ($name) {
        return ['name' => $name, 'guard_name' => 'web'];
    });

    Permission::insert($permissions->toArray());
    ```

    ⚠️ **Remember**: After using `insert()`, manually flush the cache:

    ```php
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    ```

## Testing

### Clear Cache During Tests

In your test `setUp()` method:

```php
protected function setUp(): void
{
    parent::setUp();

    // Re-register permissions to avoid cache issues
    $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
}
```

### Clear Cache When Using Seeders

If using `LazilyRefreshDatabase` trait:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\Events\DatabaseRefreshed;

Event::listen(DatabaseRefreshed::class, function () {
    $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
});
```

### Bypassing Cache When Testing

1. **Use Array Cache**: Set `CACHE_DRIVER=array` in `phpunit.xml`
2. **Short Cache Lifetime**: In test `TestCase`:
    ```php
    config(['permission.cache.expiration_time' => \DateInterval::createFromDateString('1 seconds')]);
    ```

### Testing Using Factories

If you need to create roles/permissions in tests:

1. Extend the Role/Permission models into your app namespace
2. Add `HasFactory` trait
3. Define model factories
4. Use factories in tests

## Database Seeding

### Flush Cache Before/After Seeding

Always flush the cache before and after seeding:

```php
use App\Constants\Permissions;
use App\Constants\Roles;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions BEFORE seeding
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions using constants
        Permission::create(['name' => Permissions::EDIT_ARTICLE]);
        Permission::create(['name' => Permissions::DELETE_ARTICLE]);
        Permission::create(['name' => Permissions::PUBLISH_ARTICLE]);
        Permission::create(['name' => Permissions::UNPUBLISH_ARTICLE]);

        // Update cache to know about newly created permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles and assign permissions using constants
        $role = Role::create(['name' => Roles::WRITER]);
        $role->givePermissionTo(Permissions::EDIT_ARTICLE);

        $role = Role::create(['name' => Roles::MODERATOR])
            ->givePermissionTo([Permissions::PUBLISH_ARTICLE, Permissions::UNPUBLISH_ARTICLE]);

        $role = Role::create(['name' => Roles::SUPER_ADMIN]);
        $role->givePermissionTo(Permission::all());
    }
}
```

### User Seeding with Factories

Using Factory States:

```php
// In Factory
public function active(): static
{
    return $this->state(fn (array $attributes) => [
        'status' => 1,
    ])
    ->afterCreating(function (User $user) {
        $user->assignRole(Roles::ACTIVE_MEMBER);
    });
}

// In Seeder
User::factory(4)->active()->create();
```

Without Factory States:

```php
User::factory()
    ->count(50)
    ->create()
    ->each(function ($user) {
        $user->assignRole(Roles::MEMBER);
    });
```

### Speeding Up Seeding for Large Data Sets

Use `insert()` for bulk operations:

```php
use App\Constants\Permissions;
use Illuminate\Support\Facades\DB;

$permissions = collect([
    Permissions::EDIT_ARTICLE,
    Permissions::DELETE_ARTICLE,
    // ...
])->map(function ($permission) {
    return ['name' => $permission, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()];
});

Permission::insert($permissions->toArray());

// Flush cache after direct DB operations
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

⚠️ **CAUTION**: When using direct DB queries, always manually flush the cache afterward.

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

## Defining Super-Admin

**Best Practice**: Use `Gate::before()` to handle Super-Admin functionality.

### Gate::before Approach

In `AppServiceProvider::boot()`:

```php
use App\Constants\Roles;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    // Implicitly grant "Super Admin" role all permissions
    Gate::before(function ($user, $ability) {
        return $user->hasRole(Roles::SUPER_ADMIN) ? true : null;
    });
}
```

⚠️ **Important**: Return `null` (not `false`) to allow normal policy operation.

### Policy::before() Alternative

In individual Policy classes:

```php
use App\Constants\Roles;

public function before(User $user, string $ability): ?bool
{
    if ($user->hasRole(Roles::SUPER_ADMIN)) {
        return true;
    }

    return null; // Must return null, not false
}
```

### Gate::after Alternative

For cases where Super Admin shouldn't bypass certain rules:

```php
Gate::after(function ($user, $ability) {
    return $user->hasRole(Roles::SUPER_ADMIN); // Returns boolean
});
```

## Multiple Guards

### Default Behavior

When using multiple guards, each guard acts as a namespace for permissions and roles.

⚠️ **Downside**: You must register the same permission/role name for each guard.

### Forcing Single Guard

If all guards share the same roles/permissions, override in User model:

```php
protected string $guard_name = 'web';

protected function getDefaultGuardName(): string
{
    return $this->guard_name;
}
```

### Creating Permissions/Roles for Specific Guards

```php
use App\Constants\Roles;
use App\Constants\Permissions;

// Admin guard
$role = Role::create(['guard_name' => 'admin', 'name' => Roles::MANAGER]);
$permission = Permission::create(['guard_name' => 'admin', 'name' => Permissions::PUBLISH_ARTICLE]);

// Web guard
$permission = Permission::create(['guard_name' => 'web', 'name' => Permissions::PUBLISH_ARTICLE]);
```

### Checking Permissions for Specific Guard

```php
$user->hasPermissionTo(Permissions::PUBLISH_ARTICLE, 'admin');
```

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

## Extending Models

### Adding Fields to Role/Permission Tables

1. Create migration:

```bash
php artisan make:migration add_description_to_permissions_tables
```

2. In migration:

```php
public function up(): void
{
    Schema::table('permissions', function (Blueprint $table) {
        $table->string('description')->nullable();
    });

    Schema::table('roles', function (Blueprint $table) {
        $table->string('description')->nullable();
    });
}
```

### Extending Role and Permission Models

1. Create extended models:

```php
<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Add custom methods/properties
}
```

2. Update `config/permission.php`:

```php
'models' => [
    'permission' => \App\Models\Permission::class,
    'role' => \App\Models\Role::class,
],
```

## Exceptions

### Handling UnauthorizedException

In `bootstrap/app.php`:

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
        return response()->json([
            'responseMessage' => 'You do not have the required authorization.',
            'responseStatus' => 403,
        ], 403);
    });
})
```

## Basic Usage

### Assigning Roles

```php
use App\Constants\Roles;

$user->assignRole(Roles::WRITER);
$user->assignRole(Roles::WRITER, Roles::ADMIN);
$user->assignRole([Roles::WRITER, Roles::ADMIN]);
```

### Checking Roles

```php
use App\Constants\Roles;

$user->hasRole(Roles::WRITER);
$user->hasAnyRole([Roles::EDITOR, Roles::MODERATOR]);
$user->hasAllRoles([Roles::WRITER, Roles::EDITOR]);
$user->hasExactRoles([Roles::WRITER]);
```

### Assigning Permissions to Roles

```php
use App\Constants\Roles;
use App\Constants\Permissions;

$role->givePermissionTo(Permissions::EDIT_ARTICLE);
$role->hasPermissionTo(Permissions::EDIT_ARTICLE);
$role->revokePermissionTo(Permissions::EDIT_ARTICLE);
$role->syncPermissions([Permissions::EDIT_ARTICLE, Permissions::DELETE_ARTICLE]);
```

### Assigning Direct Permissions to Users

```php
use App\Constants\Permissions;

$user->givePermissionTo(Permissions::DELETE_ARTICLE);
$user->hasDirectPermission(Permissions::DELETE_ARTICLE);
$user->hasAllDirectPermissions([Permissions::EDIT_ARTICLE, Permissions::DELETE_ARTICLE]);
$user->hasAnyDirectPermission([Permissions::CREATE_ARTICLE, Permissions::DELETE_ARTICLE]);
```

### Getting Permissions

```php
// Direct permissions
$user->getDirectPermissions();
$user->permissions;

// Permissions via roles
$user->getPermissionsViaRoles();

// All permissions
$user->getAllPermissions();
$user->getPermissionNames(); // Collection of name strings
```

### Scopes

```php
use App\Constants\Roles;
use App\Constants\Permissions;

// Users with specific role
$users = User::role(Roles::WRITER)->get();

// Users without specific role
$nonEditors = User::withoutRole(Roles::EDITOR)->get();

// Users with specific permission
$users = User::permission(Permissions::EDIT_ARTICLE)->get();

// Users without specific permission
$users = User::withoutPermission(Permissions::EDIT_ARTICLE)->get();
```

## Summary

### Key Rules

1. ✅ **Always use constants** - Never hardcode role/permission names
2. ✅ **Check permissions, not roles** - Use `can()` and `@can` with permissions
3. ✅ **Users → Roles → Permissions** - Follow the hierarchy
4. ✅ **Use Model Policies** - For access control logic
5. ✅ **Flush cache** - When seeding or using direct DB operations
6. ✅ **Use Gate::before()** - For Super-Admin functionality
7. ✅ **Unset relations** - When switching teams

### Constants Classes

-   `App\Constants\Permissions` - All permission constants
-   `App\Constants\Roles` - All role constants

### Important Files

-   `config/permission.php` - Package configuration
-   `app/Http/Middleware/TeamsPermission.php` - Teams middleware
-   `app/Providers/AppServiceProvider.php` - Middleware priority
-   `docs/spatie-permission.md` - This documentation file

---

**For more details, see**: [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission)
