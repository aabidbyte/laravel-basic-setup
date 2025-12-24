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

