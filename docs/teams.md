# Teams & Data Isolation

This application implements a row-level multi-tenancy system based on Teams.

## Core Concepts

- **No Primary Team**: Users can belong to multiple teams. There is no concept of a "current" or "primary" team stored on the user model.
- **Many-to-Many**: Users and Teams are linked via the `team_user` pivot table.
- **Data Isolation**: Models using the `HasTeamAccess` trait are automatically scoped to the user's teams.

## Implementation

### `HasTeamAccess` Trait

Models that belong to a team should use `App\Models\Concerns\HasTeamAccess`.

```php
use App\Models\Concerns\HasTeamAccess;

class Project extends BaseModel
{
    use HasTeamAccess;
}
```

This trait:
1.  Adds a `team()` relationship (BelongsTo Team).
2.  Applies the `App\Models\Scopes\TeamScope` global scope.

### `TeamScope`

The `TeamScope` automatically filters queries to only show records belonging to teams the current user is a member of.

```php
// If User A is in Team 1 and Team 2:
Project::all();
// SQL: select * from projects where team_id in (1, 2)
```

### Default Team (Super Team)

There is a "Default Team" or "Super Team" (conceptually) configured in `config/teams.php`.
Members of this team typically bypass team scoping rules, allowing them to see all data across all teams.

**Configuration**:
`config/teams.php`:
```php
'default_team_id' => 1,
```

**Bypass Logic**:
Inside `TeamScope`, if the user is a member of the default team, the scope is not applied.

## User-Team Management

- **Assigning Teams**:
  ```php
  $user->teams()->attach($teamId, ['uuid' => Str::uuid()]);
  ```
- **Checking Membership**:
  ```php
  if ($user->teams->contains($teamId)) { ... }
  ```

## Frontend

The UI handles team selection via multi-select inputs. Users can be added to unlimited teams.
