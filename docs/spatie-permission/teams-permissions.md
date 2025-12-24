## Teams Permissions

Teams permissions are **enabled by default** in this application.

### Configuration

-   **Enabled**: `config/permission.php` → `'teams' => true`
-   **Team Foreign Key**: `'team_id'` (default, can be customized)
-   **Middleware**: `app/Http/Middleware/Teams/TeamsPermission.php`

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

