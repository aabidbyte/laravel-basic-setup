# Custom Role-Based Access Control (RBAC) System

This application uses a custom, lightweight RBAC system built on top of Laravel's native Authorization features (Gates & Policies).

## Overview

- **Roles**: Defined in `App\Models\Role`. Assigned to users.
- **Permissions**: Defined in `App\Models\Permission`. Assigned to roles.
- **Users**: Have many roles. Get permissions *through* their roles.
- **Teams**: Teams are handled separately via `App\Models\Team` and the `team_user` pivot table.

## Permission Matrix Architecture

Permissions follow an **entity-action pattern**: `"{action} {entity}"` (e.g., "view users", "edit roles").

### Core Components

| File | Purpose |
|------|---------|
| `App\Constants\Auth\PermissionEntity` | Defines all permissionable entities (users, roles, teams, etc.) |
| `App\Constants\Auth\PermissionAction` | Defines all action types (view, create, edit, delete, etc.) |
| `App\Constants\Auth\Permissions` | Permission constants derived from entity-action pairs |
| `App\Services\Auth\PermissionMatrix` | Centralized service mapping entities to their supported actions |

### Entities & Actions

| Entity | Supported Actions |
|--------|-------------------|
| Users | view, create, edit, delete, activate, export, generate_activation |
| Roles | view, create, edit, delete |
| Teams | view, create, edit, delete |
| Error Logs | view, resolve, delete, export |
| Telescope | view (super_admin only) |
| Horizon | view (super_admin only) |
| Mail Settings | view, configure |

### Usage Examples

```php
// Using permission constants
use App\Constants\Auth\Permissions;

$user->can(Permissions::VIEW_USERS);
$user->can(Permissions::EDIT_ROLES);

// In Blade
@can(Permissions::CREATE_TEAMS) ... @endcan

// Using the matrix service
$matrix = new \App\Services\Auth\PermissionMatrix();
$matrix->getActionsForEntity('users'); // ['view', 'create', 'edit', ...]
$matrix->entitySupportsAction('roles', 'activate'); // false
```

## Models & Traits

- **`App\Models\Role`**: Extends `BaseModel`. Has `permissions()` and `users()`.
- **`App\Models\Permission`**: Extends `BaseModel`. Has `roles()`.
- **`App\Models\Concerns\HasRolesAndPermissions`**: Trait used by `User` model.

## Authorization Logic

Handled in `App\Providers\AccessServiceProvider` via `Gate::before`.

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

## UI Component

The `<x-ui.permission-matrix>` Blade component provides a table-based UI for managing permissions:
- Entities displayed as rows
- Actions displayed as columns  
- Checkboxes at valid intersections
- Dash marks (—) for invalid entity-action pairs
- **Super Admin Only Entities**: Telescope and Horizon are only visible to super_admin users
- **Super Admin Role Protection**: The super_admin role is only visible and manageable by super_admin users

## Best Practices

1. **Use Constants**: Always use `Permissions::VIEW_USERS` instead of hardcoded strings.
2. **Use Permissions, Not Roles**: Check `can('edit users')` not `hasRole('admin')`.
3. **Use Policies**: For model-specific logic (e.g., "can user edit *this* post?").

## Database Schema

- `roles`: `id`, `uuid`, `name`, `display_name`, `description`
- `permissions`: `id`, `uuid`, `name`, `display_name`, `description`
- `role_user`: `user_id`, `role_id`
- `permission_role`: `role_id`, `permission_id`

## Seeding

```bash
php artisan db:seed --class=RoleAndPermissionSeeder
```

The seeder uses `PermissionMatrix` to generate all permissions with display names and descriptions.
