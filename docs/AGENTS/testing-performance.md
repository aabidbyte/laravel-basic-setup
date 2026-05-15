# Testing Performance Rules

This project uses a three-lane Pest test strategy backed by one migrated MySQL schema per test process.

## Default Developer Suite

Run the fast suite with:

```bash
composer test
```

`composer test` runs the fast Unit lane. This is the default command for local feedback and should stay under 30 seconds on a warmed development machine.

Use this lane for tests that do not require Laravel HTTP bootstrapping, real tenant databases, or tenant migrations.

Run non-tenancy Feature tests with:

```bash
composer test:feature
```

## Tenancy Integration Suite

Run the real multi-database tenancy suite with:

```bash
composer test:integration
```

This suite includes tests that create tenants, initialize stancl tenancy, create tenant databases, migrate tenant databases, or assert database isolation across tenants. These tests are intentionally slower because they exercise the same MySQL multi-database behavior used by the application.

Run the full suite with:

```bash
composer test:all
```

`composer test:all` is the command that answers whether the whole test suite is green.

Tests that intentionally drop all testing tenant databases, such as the cleanup command tests, must run in the `serial-database-cleanup` group. Do not run them in the same parallel process pool as tests that use the reusable tenant database.

## Migration And Transaction Model

Feature tests must not use per-test `RefreshDatabase` or `DatabaseMigrations`. `Tests\TestCase` migrates the central MySQL database once per PHP process, prepares one reusable tenant database per process, then wraps central and reusable-tenant writes in transactions.

Parallel tests rely on Laravel's per-process test database suffix. Tenant test database names are derived from the active central database and include the configured testing prefix/suffix, so they stay separate from development and production databases.

Tests that call `asTenant()` without passing a tenant use the reusable tenant database and must rely on transactions for row cleanup. Tests that intentionally create real tenant databases are integration/provisioning tests and must keep their cleanup explicit.

Tenant provisioning can issue MySQL DDL, which may implicitly commit central database work. Do not assert global table counts in these tests. Assert rows scoped to the model, user, tenant, or record created by the test.

## Required Grouping

Any test that does one of the following must be grouped as `tenancy-provisioning` in `tests/Pest.php`:

- calls `asTenant()`
- calls `$this->setUpTenancy()`
- creates a tenant that triggers database provisioning
- calls `tenancy()->initialize()` and expects tenant database behavior
- runs or asserts tenant migrations
- creates, drops, or inspects tenant databases

Any test that drops all testing tenant databases must also be grouped as `serial-database-cleanup`.

Do not delete or weaken these tests to make the default suite faster. Move real multi-database coverage into the integration lane instead.

## Tenancy Testing Constraints

Do not use SQLite or `:memory:` for tenancy tests. The app uses MySQL multi-database tenancy, and tests must exercise the same connection type.

Do not use `RefreshDatabase` or `DatabaseMigrations` for routine Feature tests. They rebuild schemas per test and make the suite too slow. Use the existing one-time migration, transaction, and tenant cleanup flow.

Do not globally call `Event::fake()` before creating tenants. stancl tenancy relies on tenant lifecycle events to create and migrate tenant databases. If events must be faked, fake only the specific events under test after tenant setup.

## Cleanup

Testing tenant databases use the configured testing prefix and suffix so they are separated from development and production databases.

Clean all testing tenant databases manually with:

```bash
APP_ENV=testing php artisan tenants:clean-test-databases
```

Preview cleanup without dropping databases:

```bash
APP_ENV=testing php artisan tenants:clean-test-databases --dry-run
```
