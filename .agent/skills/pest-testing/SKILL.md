---
name: pest-testing
description: "Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: test()/it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code."
license: MIT
metadata:
  author: laravel
---

# Pest Testing 4

## Documentation

Use `search-docs` for detailed Pest 4 patterns and documentation.

## Basic Usage

### Creating Tests

All tests must be written using Pest. Use `php artisan make:test --pest {name}`.

The `{name}` argument should include only the path and test name, but should not include the test suite.
- Incorrect: `php artisan make:test --pest Feature/SomeFeatureTest` will generate `tests/Feature/Feature/SomeFeatureTest.php`
- Correct: `php artisan make:test --pest SomeControllerTest` will generate `tests/Feature/SomeControllerTest.php`
- Incorrect: `php artisan make:test --pest --unit Unit/SomeServiceTest` will generate `tests/Unit/Unit/SomeServiceTest.php`
- Correct: `php artisan make:test --pest --unit SomeServiceTest` will generate `tests/Unit/SomeServiceTest.php`

### Test Organization

- Unit/Feature tests: `tests/Feature` and `tests/Unit` directories.
- Browser tests: `tests/Browser/` directory.
- Do NOT remove tests without approval - these are core application code.
- In multi-database tenancy projects, follow the project's transaction strategy. Do not add `RefreshDatabase`, `DatabaseMigrations`, SQLite, or `:memory:` when the project uses reusable MySQL schemas and transactions.

### Basic Test Structure

Pest supports both `test()` and `it()` functions. Before writing new tests, check existing test files in the same directory to match the project's convention. Use `test()` if existing tests use `test()`, or `it()` if they use `it()`.

<!-- Basic Pest Test Example -->
```php
it('is true', function () {
    expect(true)->toBeTrue();
});
```

### Running Tests

- Run minimal tests with filter before finalizing: `php artisan test --compact --filter=testName`.
- Run all tests: `php artisan test --compact`.
- Run file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- For projects with Composer test lanes, use the documented lane commands (`composer test`, `composer test:feature`, `composer test:integration`, `composer test:all`) instead of inventing new scripts.

### Laravel Multi-Tenancy Performance Rules

For Laravel projects using stancl/tenancy with multi-database MySQL tenancy:

- Keep the default developer suite fast. Real tenant database provisioning tests should live in an explicit Pest group such as `tenancy-provisioning` or `integration`, and the default local command should exclude that group. If Feature tests all use per-test migrations, the default command may need to target `tests/Unit` only and expose Feature tests through a separate command.
- Keep the full tenancy integration suite available under a separate command. Do not delete real tenant provisioning tests just to improve local feedback time.
- Do not switch tenancy tests to SQLite or `:memory:` when the application uses MySQL multi-database tenancy. The test connection type must match the runtime behavior being validated.
- Do not use `RefreshDatabase` or `DatabaseMigrations` as the reset mechanism for routine Feature tests in large multi-database tenancy suites. Prefer one migrated MySQL schema per test process, transaction rollback for row isolation, and explicit tenant database cleanup for databases created by the tenancy package.
- For parallel tests, let Laravel suffix the central test database per process, then derive tenant test database names from the active central database. Shared/reusable tenant databases must also be per-process and use a testing-only prefix/suffix.
- If helper-based tenant tests do not need to verify real tenant provisioning, use a reusable tenant database migrated once per process and wrap tenant writes in transactions.
- Tests that intentionally create tenant databases, run tenant migrations, or verify stancl lifecycle events belong in the explicit integration/provisioning group.
- Tests that intentionally drop all testing tenant databases must run in a serial group outside the parallel pool, because they can delete reusable tenant databases that other workers are using.
- Prefer explicit cleanup for test tenant databases. Use a test-only prefix/suffix, then clean matching databases with the project's cleanup command or teardown helper.
- Avoid global `Event::fake()` before creating tenants. stancl/tenancy relies on lifecycle events to create and migrate tenant databases. If events must be faked, fake specific events after tenant setup.
- In parallel MySQL tenancy tests, do not assume seeded IDs exist in every process database. Create the actor/record needed by the test.
- Avoid mixing writes on one connection with component queries/assertions on another connection inside the same transaction; align the setup connection with the model/component under test.
- MySQL DDL can implicitly commit open transactions. In tenant provisioning tests, avoid global table-count assertions; assert records scoped to the user, tenant, model, or entity created by the test.

## Assertions

Use specific assertions (`assertSuccessful()`, `assertNotFound()`) instead of `assertStatus()`:

<!-- Pest Response Assertion -->
```php
it('returns all', function () {
    $this->postJson('/api/docs', [])->assertSuccessful();
});
```

| Use | Instead of |
|-----|------------|
| `assertSuccessful()` | `assertStatus(200)` |
| `assertNotFound()` | `assertStatus(404)` |
| `assertForbidden()` | `assertStatus(403)` |

## Mocking

Import mock function before use: `use function Pest\Laravel\mock;`

## Datasets

Use datasets for repetitive tests (validation rules, etc.):

<!-- Pest Dataset Example -->
```php
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
```

## Pest 4 Features

| Feature | Purpose |
|---------|---------|
| Browser Testing | Full integration tests in real browsers |
| Smoke Testing | Validate multiple pages quickly |
| Visual Regression | Compare screenshots for visual changes |
| Test Sharding | Parallel CI runs |
| Architecture Testing | Enforce code conventions |

### Browser Test Example

Browser tests run in real browsers for full integration testing:

- Browser tests live in `tests/Browser/`.
- Use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories.
- In standard non-tenancy Laravel apps, `RefreshDatabase` can provide clean state. In MySQL multi-database tenancy projects, follow the host project's test database strategy instead; do not add `RefreshDatabase` or `DatabaseMigrations` when the project forbids per-test migrations.
- Interact with page: click, type, scroll, select, submit, drag-and-drop, touch gestures.
- Test on multiple browsers (Chrome, Firefox, Safari) if requested.
- Test on different devices/viewports (iPhone 14 Pro, tablets) if requested.
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging.

<!-- Pest Browser Test Example -->
```php
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in');

    $page->assertSee('Sign In')
        ->assertNoJavaScriptErrors()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!');

    Notification::assertSent(ResetPassword::class);
});
```

### Smoke Testing

Quickly validate multiple pages have no JavaScript errors:

<!-- Pest Smoke Testing Example -->
```php
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavaScriptErrors()->assertNoConsoleLogs();
```

### Visual Regression Testing

Capture and compare screenshots to detect visual changes.

### Test Sharding

Split tests across parallel processes for faster CI runs.

### Architecture Testing

Pest 4 includes architecture testing (from Pest 3):

<!-- Architecture Test Example -->
```php
arch('controllers')
    ->expect('App\Http\Controllers')
    ->toExtendNothing()
    ->toHaveSuffix('Controller');
```

## Common Pitfalls

- Not importing `use function Pest\Laravel\mock;` before using mock
- Using `assertStatus(200)` instead of `assertSuccessful()`
- Forgetting datasets for repetitive validation tests
- Deleting tests without approval
- Forgetting `assertNoJavaScriptErrors()` in browser tests
- Prefixing `Feature/` or `Unit/` in `{name}` when using `make:test`
- In parallel MySQL tenancy tests, do not assume seeded IDs exist in every process database. Create the actor/record needed by the test.
- Avoid mixing writes on one connection with component queries/assertions on another connection inside the same transaction; align the setup connection with the model/component under test.
