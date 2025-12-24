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

