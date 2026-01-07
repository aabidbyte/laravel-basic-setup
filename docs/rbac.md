# Custom Role-Based Access Control (RBAC) System

This application uses a custom, lightweight RBAC system built on top of Laravel's native Authorization features (Gates & Policies). It replaces the `spatie/laravel-permission` package.

## Overview

- **Roles**: Defined in `App\Models\Role`. Assigned to users.
- **Permissions**: Defined in `App\Models\Permission`. Assigned to roles.
- **Users**: Have many roles. Get permissions *through* their roles.
- **Teams**: Teams are handled separately via `App\Models\Team` and the `team_user` pivot table.

## core Components

### Models & Traits

- **`App\Models\Role`**: Extends `BaseModel`. Has `permissions()` and `users()`.
- **`App\Models\Permission`**: Extends `BaseModel`. Has `roles()`.
- **`App\Models\Concerns\HasRolesAndPermissions`**: Trait used by `User` model.
    - `roles()`: BelongsToMany relationship.
    - `hasRole($role)`: Check if user has a role.
    - `hasPermissionTo($permission)`: Check if user has a permission (via roles).
    - `assignRole($role)`: Assign a role to user.
    - `syncRoles($roles)`: Sync roles.

### Authorization Logic

Authorization is handled in `App\Providers\AppServiceProvider` via `Gate::before`.

```php
Gate::before(function ($user, $ability) {
    // 1. Super Admin Bypass
    if ($user->hasRole(Roles::SUPER_ADMIN)) {
        return true;
    }

    // 2. Check Permissions via Roles
    if ($user->hasPermissionTo($ability)) {
        return true;
    }

    // 3. Fallback to Policies
    return null;
});
```

### Usage

#### Checking Permissions

Use standard Laravel authorization methods:

```php
// In Controllers/Code
if ($user->can('view_users')) { ... }
if (Gate::allows('view_users')) { ... }

// In Blade
@can('view_users') ... @endcan
```

#### Checking Roles

```php
if ($user->hasRole('admin')) { ... }
```

#### Assigning Roles

```php
$user->assignRole('editor');
$user->syncRoles(['editor', 'viewer']);
```

## Best Practices

1.  **Use Permissions, Not Roles**: Always check for permissions (`can('edit_post')`) rather than roles (`hasRole('editor')`) in your application logic. This allows for flexible role definitions.
2.  **Define Constants**: Use `App\Constants\Auth\Permissions` and `App\Constants\Auth\Roles` to avoid hardcoded strings.
3.  ** policies**: Use Model Policies for logic involving specific model instances (e.g., "can user edit *this* post?").

## Database Schema

- `roles`: `id`, `name`, `display_name`, ...
- `permissions`: `id`, `name`, `display_name`, ...
- `role_user`: `user_id`, `role_id`
- `permission_role`: `role_id`, `permission_id`

## Seeding

Use `RoleAndPermissionSeeder` to define roles and permissions.

```bash
php artisan db:seed --class=RoleAndPermissionSeeder
```
