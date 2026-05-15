# SKILL: stancl/tenancy — Laravel Multi-Tenancy Package (v3)

> Source: https://tenancyforlaravel.com/docs/v3/
> Package: `stancl/tenancy` | Requires Laravel 9.0+
> GitHub: https://github.com/stancl/tenancy

---

## OVERVIEW

`stancl/tenancy` is a flexible multi-tenancy package for Laravel. It bootstraps tenancy automatically in the background — switching database connections, caches, filesystems, queues, and Redis stores — so your existing application code needs zero or minimal changes.

Two main tenancy **types**:
- **Single-database tenancy** — all tenants share one DB; data scoped via `tenant_id` columns.
- **Multi-database tenancy** — each tenant gets their own database (primary focus of the package).

Two tenancy **modes**:
- **Automatic mode** (recommended) — bootstrappers run on identification middleware, scoping everything in the background.
- **Manual mode** — you manage connections yourself using model traits (`CentralConnection`, `TenantConnection`).

---

## INSTALLATION

```bash
composer require stancl/tenancy
php artisan tenancy:install
php artisan migrate
```

The `tenancy:install` command creates:
- Migrations (tenants table, domains table)
- `config/tenancy.php`
- `routes/tenant.php`
- `app/Providers/TenancyServiceProvider.php`

Register the service provider in `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\TenancyServiceProvider::class, // <-- add this
];
```

If using a different central DB than `DB_CONNECTION`, name it `central` in `config/database.php` and set `tenancy.central_connection` to match.

---

## TENANT MODEL

### Basic (no domain/DB)
```php
// Uses base Tenant model directly — UUID-based IDs, data JSON column, forced central connection
```

### With domains & databases (most common)
```php
namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
}
```

Update config:
```php
// config/tenancy.php
'tenant_model' => \App\Models\Tenant::class,
```

### Base model features (included by default)
- **Forced central connection** — can query Tenant models even inside the tenant context.
- **Data column trait** — attributes without dedicated columns are stored as JSON in the `data` column.
- **ID generation trait** — auto-generates UUID if no `id` supplied on creation.

### Custom columns

Override `getCustomColumns()` to declare columns that live outside the `data` JSON column:
```php
public static function getCustomColumns(): array
{
    return [
        'id',
        'plan', // always keep 'id' here
    ];
}
```

Rename the data column:
```php
public static function getDataColumn(): string
{
    return 'my-data-column';
}
```

### Incrementing IDs
To use auto-increment instead of UUIDs:
1. Set `tenancy.id_generator` to `null` in config.
2. Change `tenants` table migration to `bigIncrements()`.
3. Update `domains.tenant_id` column type to match.
4. Override on model:
```php
public function getIncrementing() { return true; }
```

### Creating tenants
```php
$tenant = Tenant::create(['plan' => 'free']);
// Event fires → CreateDatabase + MigrateDatabase jobs run automatically
```

### Running code in tenant context
```php
$tenant->run(function () {
    User::create([...]);
});

// Or for all tenants:
Tenant::all()->runForEach(function () {
    User::factory()->create();
});
```

### Accessing current tenant
```php
tenant()        // returns current Tenant model
tenant('id')    // returns attribute value
// Or typehint Stancl\Tenancy\Contracts\Tenant in constructor
```

### Internal key prefix
Keys prefixed with `tenancy_` (configurable via `internalPrefix()`) are reserved for internal use.

---

## DOMAINS

The `HasDomains` trait enables `Tenant hasMany Domain`.

```php
$tenant->domains()->create(['domain' => 'acme.com']);
// or for subdomain:
$tenant->domains()->create(['domain' => 'acme']); // stored without TLD
```

Domain model can be customized via `tenancy.domain_model` config.

---

## CONFIGURATION (`config/tenancy.php`)

| Key | Description |
|-----|-------------|
| `tenant_model` | Your custom Tenant model class |
| `id_generator` | UUID generator class; set to `null` for autoincrement |
| `domain_model` | Custom Domain model |
| `central_domains` | Array of domains serving the central app (landing pages, admin) |
| `bootstrappers` | Tenancy bootstrapper classes to enable/disable |
| `database.*` | DB manager settings for multi-DB tenancy |
| `cache.*` | Cache separation settings (requires tagging-capable store, e.g. Redis) |
| `filesystem.*` | Storage separation settings |
| `redis.*` | Redis prefix separation settings (requires phpredis) |
| `features` | Optional feature classes |
| `migration_parameters` | Default params for `tenants:migrate` |
| `seeder_parameters` | Default params for `tenants:seed` |

### Static property configuration

Many classes expose `public static` properties for in-code configuration. Set them in `TenancyServiceProvider::boot()`:

```php
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

InitializeTenancyByDomain::$onFail = function () {
    return redirect('https://my-central-domain.com/');
};
```

---

## ROUTES

### Central routes (Laravel 11+)
```php
// routes/web.php
foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        // your central routes here
    });
}
```

### Tenant routes (`routes/tenant.php`)
```php
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', fn() => 'Tenant: ' . tenant('id'));
});
```

- `PreventAccessFromCentralDomains` — returns 404 when tenant routes are hit from central domains.
- Tenant routes take precedence over central routes (registered later).

### Universal routes
See `UniversalRoutes` optional feature — routes accessible in both central and tenant contexts.

---

## TENANT IDENTIFICATION

### Available middleware

| Middleware | Identifies by |
|-----------|---------------|
| `InitializeTenancyByDomain` | Full domain (`acme.com`) |
| `InitializeTenancyBySubdomain` | Subdomain prefix (`acme` stored, matched against central domains) |
| `InitializeTenancyByDomainOrSubdomain` | Both: records with dots = domain, without = subdomain |
| `InitializeTenancyByPath` | URL path segment (`/{tenant}/...`) |
| `InitializeTenancyByRequestData` | Header (`X-Tenant`) or query param (`?tenant=`) |

### Path identification
```php
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

Route::group([
    'prefix' => '/{tenant}',
    'middleware' => [InitializeTenancyByPath::class],
], function () {
    Route::get('/foo', 'FooController@index');
});
```

### Request data identification
```php
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

InitializeTenancyByRequestData::$header = 'X-Team';         // change header name
InitializeTenancyByRequestData::$header = null;              // disable header, use query only
InitializeTenancyByRequestData::$queryParameter = null;      // disable query, use header only
```

### onFail customization
```php
InitializeTenancyByDomain::$onFail = function ($exception, $request, $next) {
    return redirect('https://my-central-domain.com/');
};
```

### Manual identification
```php
tenancy()->initialize($tenant);
tenancy()->end();
```

---

## THE TWO APPLICATIONS

- **Central application** — served on `central_domains`. Handles landing pages, sign-ups, tenant management, billing.
- **Tenant application** — served on tenant domains. All tenancy bootstrappers are active here.

The central connection is always accessible via models using `CentralConnection` trait, regardless of current context.

When application code runs after tenancy is initialized, the default connection may point at the tenant database. Any model or query that must read central users, roles, teams, or pivots should use a central model/trait or an explicit central connection (`Model::on('central')`, `DB::connection('central')`). Do not assume the default `User` model remains central inside tenant context.

---

## TENANCY BOOTSTRAPPERS

Bootstrappers run automatically on `TenancyInitialized` event, reverting on `TenancyEnded`.

### Built-in bootstrappers

#### `DatabaseTenancyBootstrapper`
- Switches the **default** database connection to `tenant`.
- Only changes the default — explicit connections (`DB::connection('...')`, model `getConnectionName()`, `CentralConnection` trait) are always respected.

#### `CacheTenancyBootstrapper`
- Replaces `CacheManager` with a scoped version that tags every call with the tenant ID.
- Requires a cache store with tagging support (e.g. Redis).
- Clear a tenant's cache: `php artisan cache:clear --tag=tenant_<id>`

#### `FilesystemTenancyBootstrapper`
- Makes `Storage` facade, `storage_path()`, and `asset()` helpers tenant-aware.
- `storage_path()` is suffixed: e.g. `storage/tenant42/`.
- Disk roots in `tenancy.filesystem.disks` are suffixed. Override with `tenancy.filesystem.root_override`:
  ```php
  'root_override' => [
      'local'  => '%storage_path%/app/',
      'public' => '%storage_path%/app/public/',
  ],
  ```
- `asset()` routes through `TenantAssetsController` by default. Must configure:
  ```php
  // TenancyServiceProvider::boot()
  TenantAssetsController::$tenancyMiddleware = InitializeTenancyByDomainOrSubdomain::class;
  ```
- Global assets: use `global_asset()` or `mix()`.
- Disable tenant `asset()` via config (`tenancy.filesystem.asset_helper_tenancy = false`) and use `tenant_asset()` explicitly.

#### `QueueTenancyBootstrapper`
- Injects tenant ID into queued job payloads; re-initializes tenancy when the job processes.
- Force refresh on each job:
  ```php
  QueueTenancyBootstrapper::$forceRefresh = true;
  ```

#### `RedisTenancyBootstrapper`
- Changes the Redis prefix for each tenant (scopes direct Redis calls).
- Requires phpredis (not predis).

### Writing custom bootstrappers
```php
namespace App;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class MyBootstrapper implements TenancyBootstrapper
{
    public function bootstrap(Tenant $tenant) { /* switch something */ }
    public function revert() { /* revert it */ }
}
```

Register in config:
```php
'bootstrappers' => [
    // ...existing...
    App\MyBootstrapper::class,
],
```

---

## EVENT SYSTEM

Everything in the package is event-driven. All events are in `Stancl\Tenancy\Events` namespace.

### Tenancy lifecycle events
| Event | Fires when |
|-------|-----------|
| `InitializingTenancy` | Before initialization |
| `TenancyInitialized` | After tenant set; triggers `BootstrapTenancy` listener |
| `BootstrappingTenancy` | Before bootstrappers run |
| `TenancyBootstrapped` | After bootstrappers finish — app is now in tenant context |
| `EndingTenancy` | Before tenancy ends |
| `TenancyEnded` | After tenancy ends; triggers `RevertToCentralContext` |
| `RevertingToCentralContext` / `RevertedToCentralContext` | During/after revert |

> Use `TenancyBootstrapped` (not `TenancyInitialized`) when you need the app already in tenant context.

### Tenant Eloquent events
`CreatingTenant`, **`TenantCreated`**, `SavingTenant`, `TenantSaved`, `UpdatingTenant`, `TenantUpdated`, `DeletingTenant`, **`TenantDeleted`**

### Domain events
`CreatingDomain`, **`DomainCreated`**, `SavingDomain`, `DomainSaved`, `UpdatingDomain`, `DomainUpdated`, `DeletingDomain`, **`DomainDeleted`**

### Database events (multi-DB tenancy)
`CreatingDatabase`, **`DatabaseCreated`**, `MigratingDatabase`, `DatabaseMigrated`, `SeedingDatabase`, `DatabaseSeeded`, `RollingBackDatabase`, `DatabaseRolledBack`, `DeletingDatabase`, **`DatabaseDeleted`**

> Some database events fire **in the tenant context** — interact with the central DB explicitly if needed.

### Resource sync events
**`SyncedResourceSaved`**, `SyncedResourceChangedInForeignDatabase`

### TenancyServiceProvider
Generated service provider maps events → listeners. Default setup (on `TenantCreated`):
```php
Event::listen(TenantCreated::class, JobPipeline::make([
    CreateDatabase::class,
    MigrateDatabase::class,
    // SeedDatabase::class, // optional
])->send(fn (TenantCreated $e) => $e->tenant)->toListener());
```

### Job Pipelines
Convert any series of jobs into an event listener:
```php
use Stancl\JobPipeline\JobPipeline;

JobPipeline::make([
    CreateDatabase::class,
    MigrateDatabase::class,
    SeedDatabase::class,
])
->send(fn (TenantCreated $event) => $event->tenant)
->shouldBeQueued(true) // optional, default false
->toListener();

// Or set globally:
JobPipeline::$shouldBeQueuedByDefault = true;
```

> Be careful with `Event::fake()` in tests — it breaks tenancy initialization. Use selective faking: `Event::fake([MyEvent::class])`.

---

## MULTI-DATABASE TENANCY

Each tenant gets their own database, managed automatically.

### TenantDatabaseManagers
Supported drivers: MySQL, PostgreSQL, SQLite. Also supports PostgreSQL schemas (one schema per tenant, shared DB).
Configure in `tenancy.database` config section.

### Lifecycle
On `TenantCreated` → `CreateDatabase` → `MigrateDatabase` → (optionally) `SeedDatabase`
On `TenantDeleted` → `DeleteDatabase`

---

## MIGRATIONS

Tenant migrations go in `database/migrations/tenant/`. They run via:
```bash
php artisan tenants:migrate
```

All migrations share the same PHP namespace — use different class names even if table names match between central and tenant.

For custom paths, set `--paths` in `tenancy.migration_parameters` config:
```php
'migration_parameters' => [
    '--path' => [database_path('migrations/tenant'), database_path('migrations/other')],
    '--realpath' => true,
],
```

---

## CONSOLE COMMANDS

All tenant-aware commands accept `--tenants=<id>` (repeatable). Without it, they run for **all tenants**.

| Command | Description |
|---------|-------------|
| `php artisan tenants:migrate [--tenants=<id>]` | Run tenant migrations |
| `php artisan tenants:rollback [--tenants=<id>]` | Rollback tenant migrations |
| `php artisan tenants:seed [--tenants=<id>]` | Seed tenant databases |
| `php artisan tenants:migrate-fresh [--tenants=<id>]` | Wipe + re-migrate tenant DB |
| `php artisan tenants:run <command> [--tenants=<id>] [--option=k=v] [--argument=k=v]` | Run custom artisan command in tenant context |
| `php artisan tenants:list` | List all tenants |
| `php artisan cache:clear --tags=tenant_<id>` | Clear a specific tenant's cache |

### Using `tenants:run` via Artisan facade
```php
Artisan::call('tenants:run', [
    'commandname' => 'email:send',
    '--tenants' => ['8075a580-...'],
    '--option'  => ['queue=1', 'subject=Hello'],
    '--argument'=> ['body=Hello world'],
]);
```

---

## TENANT-AWARE COMMANDS (custom)

Use the `TenantAwareCommand` trait to build commands that run in tenant context:

```php
use Stancl\Tenancy\Commands\TenantAwareCommand;
use Stancl\Tenancy\Concerns\HasATenantsOption; // optional --tenants flag (all tenants by default)
use Stancl\Tenancy\Concerns\HasATenantArgument; // required tenant ID argument

class FooCommand extends Command
{
    use TenantAwareCommand, HasATenantsOption;

    public function handle()
    {
        // runs inside tenant context automatically
    }
}
```

Manual approach:
```php
tenancy()->find($this->argument('tenant_id'))->run(function () {
    // tenant context code
});
```

---

## SINGLE-DATABASE TENANCY

All tenants share one database; data scoped via `tenant_id` WHERE clauses automatically.

**Trade-offs vs multi-DB:**
- ✅ Lower devops complexity, no per-tenant DB management
- ❌ More code complexity, harder to integrate some 3rd-party packages
- Best when tenants share many resources and cross-DB queries would be expensive

### Setup
1. Disable `DatabaseTenancyBootstrapper` in config (don't create per-tenant DBs).
2. Remove `CreateDatabase`, `MigrateDatabase`, `SeedDatabase` from `TenantCreated` event listener.
3. You can still use other bootstrappers (cache, filesystem, Redis separation).

### Model types
- **Tenant model** — your `Tenant` model
- **Primary models** — directly `belongTo` Tenant → apply `BelongsToTenant` trait
- **Secondary models** — indirectly belong to tenant through a primary model
- **Global models** — not scoped to any tenant

### BelongsToTenant trait (primary models)
```php
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Post extends Model
{
    use BelongsToTenant;
}
// All Post queries are now automatically scoped to tenant('id')
// Child relations (e.g. $post->comments) are scoped through the parent relationship
```

Disable scope for specific queries:
```php
Post::withoutTenancy()->get();
```

Customize column name (global, affects all models):
```php
BelongsToTenant::$tenantIdColumn = 'team_id';
```

### BelongsToPrimaryModel trait (secondary models)
```php
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

class Comment extends Model
{
    use BelongsToPrimaryModel;

    public function getRelationshipToPrimaryModel(): string
    {
        return 'post'; // relationship method name
    }
}
// Comment::all() now scoped to current tenant via post relationship
```

### Unique indexes — tenant scoping
```php
// Primary models:
$table->unique(['tenant_id', 'slug']);

// Secondary models:
$table->unique(['post_id', 'user_id']);
```

### Scoped validation rules
```php
Rule::unique('posts', 'slug')->where('tenant_id', tenant('id'));

// Or use HasScopedValidationRules trait on Tenant model:
$rules = [
    'slug' => tenant()->unique('posts'),
    'id'   => tenant()->exists('posts'),
];
```

> ⚠️ `DB` facade queries are never automatically scoped — handle manually.

---

## QUEUES

### Tenant queued jobs
With `QueueTenancyBootstrapper` active, jobs dispatched from tenant context auto-initialize tenancy before processing.

Jobs are stored in the **central** database (not per-tenant).

### Database queue — force central connection
```php
// config/queue.php
'connections' => [
    'database' => [
        'connection' => 'central', // <-- add this
        // ...
    ],
]
```

### Redis queue — avoid prefixed connections
Ensure the queue connection is not listed in `tenancy.redis.prefixed_connections`.

### Explicitly central jobs
```php
// config/queue.php
'central' => [
    'driver'   => 'database',
    'table'    => 'jobs',
    'queue'    => 'default',
    'retry_after' => 90,
    'central'  => true, // marks as always-central
],

// Usage:
dispatch(new SomeJob(...))->onConnection('central');
```

---

## SYNCED RESOURCES (cross-tenant/central)

Sync specific model attributes between tenant databases and the central database (e.g. shared users).

### Requirements
- Central model implements `SyncMaster` + uses `ResourceSyncing` trait + `CentralConnection` trait
- Tenant model implements `Syncable` + uses `ResourceSyncing` trait
- Both must have `getGlobalIdentifierKeyName()`, `getCentralModelName()`, `getSyncedAttributeNames()`
- `SyncMaster` additionally needs `getTenantModelName()` and `tenants()` relationship
- A pivot table in the central DB links users ↔ tenants
- Use `TenantPivot` for the `belongsToMany` pivot model

### Central tenant membership queries

For central database models that relate to tenants through a `tenants()` relationship or a direct `tenant_id` column, use the shared query workflow instead of duplicating access rules:

- `App\Support\Tenancy\TenantAudience` describes the requested slice: all tenant members, a specific tenant, central-only records, or all records.
- `App\Services\Tenancy\TenantMembershipQuery::apply()` applies that slice to a `tenants()` relationship.
- `App\Services\Tenancy\TenantMembershipQuery::applyToTenantKey()` applies that slice to a direct `tenant_id` column, which is used by Error Logs and tenant-aware Trash tables.
- "All Tenants" means records attached to at least one tenant, excluding protected central accounts. Central-only records are intentionally separate and should be requested with `TenantAudience::centralOnly()` or the `TenantMembershipQuery::CENTRAL_RECORDS_FILTER` datatable option.
- For users, the protected platform account is user ID `1`, guarded by the MySQL session trigger workflow. It is central even if local/development seeders attach it to a tenant.
- Non-super-admin actors only see records attached to tenants they belong to. They do not see central-only records through this workflow.
- Use this same workflow for future charts and analytics so dashboard totals, chart datasets, datatables, exports, and reports all use identical tenant visibility rules.

Example:

```php
$query = User::query()->with('tenants')->select('users.*');

app(TenantMembershipQuery::class)->apply(
    $query,
    TenantAudience::visibleTo($actor),
);
```

### Key concepts
- `getSyncedAttributeNames()` — only these attributes sync between DBs (others are DB-local)
- Saving a synced model fires `SyncedResourceSaved` → `UpdateSyncedResource` listener syncs all DBs
- Creates/updates run `withoutEvents()` — Eloquent events won't fire during sync propagation
- The global ID must be the same across all DBs

### Attaching a resource to a tenant
```php
$user = CentralUser::create([...]);
$user->tenants()->attach($tenant); // copies all columns to tenant DB (incl. unsynced as defaults)
```

### Queue syncing (recommended for production)
```php
\Stancl\Tenancy\Listeners\UpdateSyncedResource::$shouldQueue = true;
```

---

## OPTIONAL FEATURES

Enable in `tenancy.features` config. All in `Stancl\Tenancy\Features` namespace.

| Feature class | Purpose |
|---------------|---------|
| `UserImpersonation` | Generate impersonation tokens for tenant users from other contexts |
| `TelescopeTags` | Tag Telescope entries with current tenant ID |
| `TenantConfig` | Map tenant model attributes into `config()` values |
| `CrossDomainRedirect` | Adds `domain()` macro to `RedirectResponse` for cross-domain redirects |
| `UniversalRoutes` | Route actions that work in both central and tenant context |
| `ViteBundler` | Makes Vite generate correct asset paths per-tenant |

---

## TENANT ATTRIBUTE ENCRYPTION

Encrypt sensitive tenant attributes (e.g. DB credentials) using Laravel's `encrypted` cast.

1. Add dedicated columns to `tenants` table (min 512 chars, nullable):
```php
$table->string('tenancy_db_username', 512)->nullable();
$table->string('tenancy_db_password', 512)->nullable();
```

2. Declare custom columns on Tenant model:
```php
public static function getCustomColumns(): array
{
    return ['id', 'tenancy_db_username', 'tenancy_db_password'];
}
```

3. Add casts:
```php
protected $casts = [
    'tenancy_db_username' => 'encrypted',
    'tenancy_db_password' => 'encrypted',
];
```

---

## MANUAL INITIALIZATION

```php
tenancy()->initialize($tenant);   // initialize for a tenant
tenancy()->end();                  // revert to central context
tenancy()->find($id);             // find tenant by ID

// Run something in a tenant's context, then return:
$tenant->run(function () {
    // tenant context code
});
```

---

## EARLY IDENTIFICATION

For cases where tenancy must be initialized before the standard middleware runs (e.g. broadcasting, Websockets). See `docs/v3/early-identification` — use the `InitializeTenancyByDomain` (or other) middleware earlier in the pipeline, or call `tenancy()->initialize($tenant)` directly.

---

## SESSION SCOPING

When using multi-database tenancy with domain identification, sessions are automatically scoped to the domain. If you use subdomain identification and the session cookie is shared across subdomains, you may need to set the session domain config to `.yourdomain.com` (with leading dot) so it works across all subdomains.

---

## TESTING

### Central app tests
Write normal Pest tests and run them through the project Composer lanes. Do not add per-test database migration traits.

### Tenant app tests

Use the project Pest helper:

- `asTenant()` creates a reusable testing tenant backed by the per-process shared tenant database and wraps tenant writes in a transaction.
- `asTenant($tenant)` initializes a specific tenant when the test is intentionally validating real tenant provisioning behavior.
- Tests that create, migrate, inspect, or initialize real tenant databases must be grouped as `tenancy-provisioning` in `tests/Pest.php`.
- Tests that drop all testing databases must also be grouped as `serial-database-cleanup` and run outside the parallel pool.

### Important caveats
- ❌ Cannot use `:memory:` SQLite, SQLite, `RefreshDatabase`, or `DatabaseMigrations` as the routine reset mechanism with this MySQL multi-DB automatic tenancy setup.
- ❌ `Event::fake()` (without args) breaks tenancy initialization — use selective faking:
  ```php
  Event::fake([MyEvent::class]); // ✅
  Event::fake();                 // ❌ breaks tenancy
  ```
- ❌ Do not assert global table counts in provisioning tests. MySQL DDL can implicitly commit transactions; assert records scoped to the user, tenant, entity, or model created by the test.
- ✅ Use `composer test`, `composer test:feature`, `composer test:integration`, and `composer test:all` for suite verification.

---

## INTEGRATIONS

The package integrates with many popular packages. All integration guides at `docs/v3/integrations/<name>`.

| Package | Notes |
|---------|-------|
| **Laravel Nova** | Can manage tenants centrally and resources inside tenant DBs |
| **Laravel Horizon** | Works; see integration guide for queue configuration |
| **Laravel Passport** | Works; see guide for personal access token syncing |
| **Laravel Telescope** | Use `TelescopeTags` feature for tenant-aware tagging |
| **Laravel Sanctum** | Works; see guide |
| **Laravel Sail** | Use `127.0.0.1`/`localhost` as central domains; see guide |
| **Livewire** | Works; see guide for component mounting context |
| **Spatie packages** | Most work; see guide for permission syncing patterns |
| **Orchid** | See guide |
| **Vite** | Use `ViteBundler` feature |
| **Laravel Vapor** | Works — many users reported no issues |

---

## CACHED LOOKUP

To avoid repeated DB queries for tenant lookups, enable the `CachedTenantResolver` feature. This caches the domain → tenant mapping in your configured cache store.

Configure via `tenancy.cache` config. Requires a tagging-capable cache driver (Redis recommended).

---

## REAL-TIME FACADES

The package ships with real-time facade support. Use `Facades\Tenancy` for accessing the `Tenancy` class via facade if needed.

---

## TENANT MAINTENANCE MODE

Put individual tenants into maintenance mode without affecting others:

```php
$tenant->putIntoDatabaseMaintenanceMode();
$tenant->returnFromDatabaseMaintenanceMode();
```

This uses per-tenant flags rather than the global `php artisan down` approach.

---

## KEY CLASSES & NAMESPACES REFERENCE

| Class | Namespace |
|-------|-----------|
| `Tenant` (base) | `Stancl\Tenancy\Database\Models\Tenant` |
| `Domain` (base) | `Stancl\Tenancy\Database\Models\Domain` |
| `TenantPivot` | `Stancl\Tenancy\Database\Models\TenantPivot` |
| `Tenancy` (service) | `Stancl\Tenancy\Tenancy` |
| `TenancyServiceProvider` | `App\Providers\TenancyServiceProvider` (generated) |
| `JobPipeline` | `Stancl\JobPipeline\JobPipeline` |
| `BelongsToTenant` | `Stancl\Tenancy\Database\Concerns\BelongsToTenant` |
| `BelongsToPrimaryModel` | `Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel` |
| `CentralConnection` | `Stancl\Tenancy\Database\Concerns\CentralConnection` |
| `TenantConnection` | `Stancl\Tenancy\Database\Concerns\TenantConnection` |
| `HasDatabase` | `Stancl\Tenancy\Database\Concerns\HasDatabase` |
| `HasDomains` | `Stancl\Tenancy\Database\Concerns\HasDomains` |
| `ResourceSyncing` | `Stancl\Tenancy\Database\Concerns\ResourceSyncing` |
| `TenantAwareCommand` | `Stancl\Tenancy\Commands\TenantAwareCommand` |
| `TenancyBootstrapper` (interface) | `Stancl\Tenancy\Contracts\TenancyBootstrapper` |
| `TenantWithDatabase` (interface) | `Stancl\Tenancy\Contracts\TenantWithDatabase` |
| All Events | `Stancl\Tenancy\Events\*` |
| All Features | `Stancl\Tenancy\Features\*` |
| All Bootstrappers | `Stancl\Tenancy\Bootstrappers\*` |
| All Middleware | `Stancl\Tenancy\Middleware\*` |

---

## COMMON PATTERNS & GOTCHAS

1. **Always keep `id` in `getCustomColumns()`** — forgetting it breaks UUID storage.
2. **Don't use `Event::fake()` without args** in tenant tests — it silently breaks tenancy.
3. **DB facade calls are never tenant-scoped** in single-DB tenancy — scope them manually.
4. **Queue jobs stored centrally** even when dispatched from tenant context (use `'connection' => 'central'` for DB queue driver).
5. **The default DB connection switches** in automatic mode — don't rely on connection names inside tenant code.
6. **Central routes must be registered under central domain** in Laravel 11+ using `Route::domain(...)->group(...)`.
7. **Tenant migrations need unique class names** even if they share table names with central migrations.
8. **Only the default connection is switched** by the DB bootstrapper — explicit connections are always respected.
9. **Cache scoping requires a tagging store** (e.g. Redis) — won't work with file/database cache drivers.
10. **phpredis (not predis) is required** for the Redis tenancy bootstrapper.
