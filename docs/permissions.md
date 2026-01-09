# Permission Matrix System

This document describes the centralized permission matrix architecture.

## Architecture

Permissions use an **entity-action pattern**: `"{action} {entity}"` (e.g., "view users", "edit roles").

### Key Files

| File | Purpose |
|------|---------|
| `app/Constants/Auth/PermissionEntity.php` | Entity constants |
| `app/Constants/Auth/PermissionAction.php` | Action constants |
| `app/Constants/Auth/Permissions.php` | Generated permission constants |
| `app/Services/Auth/PermissionMatrix.php` | Matrix service |
| `resources/views/components/ui/permission-matrix.blade.php` | UI component |
| `lang/en_US/permissions.php` | Translations |

## Adding New Entities/Actions

### 1. Add Entity (if new)

```php
// app/Constants/Auth/PermissionEntity.php
public const MY_ENTITY = 'my_entity';

// Update all() method to include new constant
```

### 2. Add Action (if new)

```php
// app/Constants/Auth/PermissionAction.php
public const MY_ACTION = 'my_action';

// Update all() method to include new constant
```

### 3. Update Matrix

```php
// app/Services/Auth/PermissionMatrix.php
PermissionEntity::MY_ENTITY => [
    PermissionAction::VIEW,
    PermissionAction::CREATE,
    // ... supported actions only
],
```

### 4. Add Permission Constant

```php
// app/Constants/Auth/Permissions.php
public const VIEW_MY_ENTITY = 'view my_entity';
public const CREATE_MY_ENTITY = 'create my_entity';
```

### 5. Add Translations

```php
// lang/en_US/permissions.php
'entities' => [
    'my_entity' => 'My Entity',
],
```

### 6. Run Seeder

```bash
php artisan db:seed --class=RoleAndPermissionSeeder
```

## UI Component

The `<x-ui.permission-matrix>` component renders the permission matrix table.

### Props

| Prop | Type | Description |
|------|------|-------------|
| `permissions` | Collection | Permission models |
| `selectedPermissions` | array | Selected permission IDs |
| `wireModel` | string | Livewire binding name |
| `readonly` | bool | Read-only mode |

### Usage

```blade
{{-- Edit mode --}}
<x-ui.permission-matrix
    :permissions="$permissions"
    :selectedPermissions="$selectedPermissions"
    wireModel="selectedPermissions"
></x-ui.permission-matrix>

{{-- Read-only mode --}}
<x-ui.permission-matrix
    :permissions="$permissions"
    :selectedPermissions="$role->permissions->pluck('id')->toArray()"
    :readonly="true"
></x-ui.permission-matrix>
```

## Extensibility

The system is designed for future database-driven entities. The `PermissionMatrix` service can be extended to load entities/actions from a database table.
