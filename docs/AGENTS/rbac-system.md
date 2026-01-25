# RBAC Permission System

## Overview

The application uses a custom Role-Based Access Control (RBAC) system powered by a **Permission Matrix**. This system is designed to single-source permission definitions and avoid redundancy.

## Core Components

### 1. Permission Matrix (`App\Services\Auth\PermissionMatrix`)
This is the **Single Source of Truth** for all permissions. It maps Entities to Actions.

```php
// PermissionMatrix.php
return [
    PermissionEntity::USERS => [
        PermissionAction::VIEW,
        PermissionAction::CREATE,
        // ...
    ],
    // ...
];
```

### 2. Permissions Class (`App\Constants\Auth\Permissions`)
This class provides the interface for accessing permissions throughout the application. It uses **PHP Magic Methods** (`__callStatic`) to dynamically resolve constants from the matrix.

**Usage:**
```php
// ✅ CORRECT - Dynamic method call
Permissions::VIEW_USERS()

// ❌ INCORRECT - Constant access (Deprecated)
Permissions::VIEW_USERS
```

### 3. Entity & Action Constants
- `App\Constants\Auth\PermissionEntity`: Defines all valid entities (e.g., `USERS`, `ROLES`).
- `App\Constants\Auth\PermissionAction`: Defines all valid actions (e.g., `VIEW`, `CREATE`, `EDIT_BUILDER`).

## Adding New Permissions

1. **Add Entity/Action Constant** (if new):
   - Add new constant to `PermissionEntity.php` or `PermissionAction.php`.
   - **Rule**: No hardcoded strings. Always use constants.

2. **Update Matrix**:
   - Add the entity/action pair to `PermissionMatrix.php`.

3. **Generate PHPDoc** (Optional but recommended):
   - Run `php artisan permissions:generate-phpdoc` to update IDE autocomplete.

## Naming Conventions

- **Entities**: Plural, snake_case (e.g., `users`, `email_templates`).
- **Actions**: Verb, snake_case (e.g., `view`, `edit_builder`).
- **Permission String**: `{action} {entity}` (e.g., `view users`, `edit_builder email_templates`).
- **Permission Method**: `ACTION_ENTITY()` (e.g., `VIEW_USERS()`, `EDIT_BUILDER_EMAIL_TEMPLATES()`).

## Testing

- Unit tests for permission resolution: `tests/Unit/Auth/PermissionsTest.php`
- Feature tests for RBAC enforcement: `tests/Feature/RbacTest.php`
