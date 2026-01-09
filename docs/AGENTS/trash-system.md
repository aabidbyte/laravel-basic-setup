# Trash/Restore System

> **Documentation for the unified trash/restore system that manages soft-deleted items across all entities.**

## Overview

The trash system provides a centralized, reusable interface for viewing and managing soft-deleted (trashed) items. It leverages Laravel's SoftDeletes trait and integrates with the permission system for fine-grained access control.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     TrashRegistry                            │
│      (Defines which entities support trash management)       │
├────────────────────────┬────────────────────────────────────┤
│     TrashedContext     │        TrashController              │
│  (Clean URL state)     │   (Handles HTTP requests)           │
├────────────────────────┴────────────────────────────────────┤
│                    TrashDataTable                            │
│         (Unified DataTable for all entities)                 │
└─────────────────────────────────────────────────────────────┘
```

## Key Components

### TrashRegistry

**Location:** `app/Services/Trash/TrashRegistry.php`

Central registry that defines which entities can be managed through the trash system. Each entity configuration includes:

- Model class
- Permission constants (view, restore, force_delete)
- Display labels and icons
- Column definitions for the DataTable

### TrashedContext

**Location:** `app/Services/Trash/TrashedContext.php`

Static context service that enables clean URLs. Instead of using query parameters like `?withTrashed=true`, the middleware sets this context for trash routes.

```php
// In middleware or controller
TrashedContext::enable('users');

// In model queries
if (TrashedContext::isActive()) {
    return User::onlyTrashed()->find($uuid);
}
```

### TrashDataTable

**Location:** `app/Livewire/Tables/TrashDataTable.php`

A dynamic DataTable component that queries any registered entity's trashed items. It:
- Dynamically generates columns based on entity configuration
- Shows restore and force-delete actions with permission checks
- Supports bulk operations

### EnableTrashedContext Middleware

**Location:** `app/Http/Middleware/Trash/EnableTrashedContext.php`

Applied to all trash routes to enable the TrashedContext.

## Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/trash/{entityType}` | `trash.index` | List trashed items |
| GET | `/trash/{entityType}/{uuid}` | `trash.show` | View trashed item |
| POST | `/trash/{entityType}/{uuid}/restore` | `trash.restore` | Restore item |
| DELETE | `/trash/{entityType}/{uuid}/force-delete` | `trash.force-delete` | Permanently delete |

**Entity Types:** `users`, `roles`, `teams`, `error-logs`

## Permissions

Each entity uses its own restore/force_delete permissions:

| Permission | Description |
|------------|-------------|
| `restore users` | Can restore deleted users |
| `force_delete users` | Can permanently delete users |
| `restore roles` | Can restore deleted roles |
| `force_delete roles` | Can permanently delete roles |
| `restore teams` | Can restore deleted teams |
| `force_delete teams` | Can permanently delete teams |
| `restore error_logs` | Can restore deleted error logs |
| `force_delete error_logs` | Can permanently delete error logs |

## Adding a New Entity

1. **Ensure soft deletes:** Model must use `SoftDeletes` trait (included in `BaseModel`)

2. **Add permissions:** Update `PermissionMatrix.php`:
   ```php
   PermissionEntity::NEW_ENTITY => [
       // ... other actions
       PermissionAction::RESTORE,
       PermissionAction::FORCE_DELETE,
   ],
   ```

3. **Add permission constants:** Update `Permissions.php`:
   ```php
   public const RESTORE_NEW_ENTITY = 'restore new_entity';
   public const FORCE_DELETE_NEW_ENTITY = 'force_delete new_entity';
   ```

4. **Register in TrashRegistry:** Update `getEntities()`:
   ```php
   'new-entity' => [
       'model' => NewEntity::class,
       'entity' => PermissionEntity::NEW_ENTITY,
       'labelSingular' => __('types.new_entity'),
       'labelPlural' => __('types.new_entities'),
       'icon' => 'document',
       'showRoute' => 'trash.show',
       'viewPermission' => Permissions::VIEW_NEW_ENTITY,
       'restorePermission' => Permissions::RESTORE_NEW_ENTITY,
       'forceDeletePermission' => Permissions::FORCE_DELETE_NEW_ENTITY,
       'columns' => [
           'name' => __('table.new_entity.name'),
       ],
   ],
   ```

5. **Update routes:** Add entity type to route constraints in `routes/web/auth/trash.php`

6. **Update sidebar:** Add submenu item in `SideBarMenuService.php`

7. **Run seeder:** `php artisan db:seed --class=RoleAndPermissionSeeder`

## Type Confirm Modal

The type-confirm-modal component requires users to type an item's name before permanent deletion, providing an extra layer of protection against accidental data loss.

**Blade Component:** `<x-ui.type-confirm-modal>`

**Alpine Component:** `typeConfirmModal`

```html
<div x-data="typeConfirmModal({
    itemLabel: 'Item Name',
    onConfirm: () => { /* delete action */ }
})">
    <button @click="openModal()">Delete</button>
    <x-ui.type-confirm-modal></x-ui.type-confirm-modal>
</div>
```

## Sidebar Menu

The "Trashed" menu appears in the Administration section with submenus for each entity type. Visibility is controlled by permissions—users only see entity types they have permission to view.
