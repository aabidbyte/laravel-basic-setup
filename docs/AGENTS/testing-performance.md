# Testing Performance Rules

This project uses a two-lane Pest test strategy.

## Default Developer Suite

Run the fast suite with:

```bash
composer test
```

`composer test` runs in parallel and excludes the `tenancy-provisioning` group. This is the default command for local feedback and should stay under 30 seconds on a warmed development machine.

Use this lane for unit tests and service tests that do not create real tenant databases or run tenant migrations. Feature tests in this project use `DatabaseMigrations` through `Tests\TestCase`, so they are intentionally kept out of the default lane unless they are promoted to a genuinely fast test case.

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

## Required Grouping

Any test that does one of the following must be grouped as `tenancy-provisioning` in `tests/Pest.php`:

- calls `asTenant()`
- calls `$this->setUpTenancy()`
- creates a tenant that triggers database provisioning
- calls `tenancy()->initialize()` and expects tenant database behavior
- runs or asserts tenant migrations
- creates, drops, or inspects tenant databases

Do not delete or weaken these tests to make the default suite faster. Move real multi-database coverage into the integration lane instead.

## Tenancy Testing Constraints

Do not use SQLite or `:memory:` for tenancy tests. The app uses MySQL multi-database tenancy, and tests must exercise the same connection type.

Do not use `RefreshDatabase` for real multi-database tenancy tests. It does not reset every tenant database created by stancl tenancy. Use the existing project cleanup flow and the `tenants:clean-test-databases` command.

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
