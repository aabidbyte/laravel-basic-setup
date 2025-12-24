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

-   **Location**: `app/Http/Middleware/Teams/TeamsPermission.php`
-   **Registration**: Registered in `bootstrap/app.php` web middleware group
-   **Priority**: Set in `AppServiceProvider` to run before `SubstituteBindings`

