## Configuration Philosophy

### Stable Configurations

The project uses **stable, environment-aware configurations** that minimize `.env` dependencies:

1. **Redis Client Selection**:

    - Automatically uses `predis` in `local`/`development` environments
    - Automatically uses `phpredis` in `production`/`staging` environments
    - Configured in `config/database.php`

2. **Session Configuration**:

    - Driver: `redis` (stable)
    - Encryption: `true` (stable)
    - Secure cookies: Uses `isProduction()` helper function
    - Cookie name: Uses `config('app.name')` for prefix

3. **Cache Configuration**:

    - Default: `redis`
    - Prefix: Uses `config('app.name')` for prefix

4. **Queue Configuration**:

    - Default: `redis`
    - Batching and failed jobs: Redis-based

5. **Telescope & Horizon**:

    - Secure default paths (not obvious)
    - Stable configurations with minimal env dependencies

6. **Logging Configuration**:
    - Daily log rotation enabled for all level-specific channels using Monolog's RotatingFileHandler
    - Logs are separated by level into individual folders: `storage/logs/{level}/laravel-{date}.log`
    - Each log level (emergency, alert, critical, error, warning, notice, info, debug) has its own channel
    - **Exact level filtering**: Each log file contains ONLY messages of its exact level (using Monolog's FilterHandler)
    - Deprecated logs are stored in `storage/logs/deprecations/laravel-{date}.log` with daily rotation
    - Default stack channel routes to all level-specific channels
    - Retention: Configurable via `LOG_DAILY_DAYS` environment variable (default: 14 days)
    - Custom factory: `App\Logging\LevelSpecificLogChannelFactory` handles exact level filtering with daily rotation

### Configuration Best Practices

-   Use `config('app.name')` instead of `env('APP_NAME')`
-   Use helper functions for environment checks: `isProduction()`, `isDevelopment()`, `isStaging()`, `isLocal()`, `isTesting()`
-   Use `appEnv()` to get the current environment (respects config caching)
-   Only use `env()` for credentials and connection details
-   Prefer stable defaults over environment variables for non-sensitive settings

### Helper Functions

The project includes helper functions organized by domain:

**Environment Helpers** (`app/helpers/app-helpers.php`):

-   `appEnv(): string` - Get current environment (uses `config('app.env')` to respect config caching)
-   `isProduction(): bool` - Check if running in production/prod
-   `isDevelopment(): bool` - Check if running in local/development/dev
-   `isStaging(): bool` - Check if running in staging/stage
-   `isLocal(): bool` - Check if running in local environment
-   `isTesting(): bool` - Check if running in testing environment
-   `inEnvironment(string ...$environments): bool` - Check if environment matches any of the given environments

**Authentication Helpers** (`app/helpers/auth-helpers.php`):

-   `getIdentifierFromRequest(Request $request): ?string` - Extract identifier (email or username) from request, supports both 'identifier' and 'email' fields for dual authentication
-   `setTeamSessionForUser(User $user): void` - Set team ID in session for TeamsPermission middleware after successful authentication

**Permission Helpers** (`app/helpers/permission-helpers.php`):

-   `clearPermissionCache(): void` - Clear Spatie Permission cache, used in seeders and after role/permission modifications to prevent stale data

These helpers are automatically loaded via Composer autoload and centralize common logic to avoid duplication.

