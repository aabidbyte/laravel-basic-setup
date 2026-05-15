<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Models\Tenant;
use Tests\Concerns\InteractsWithTenancy;
use Tests\MiddlewareTestCase;
use Tests\TestCase;
use Tests\UiTestCase;
use Tests\UnitTestCase;

pest()->extend(UnitTestCase::class)
    ->in('Unit');

pest()->extend(MiddlewareTestCase::class)
    ->use(InteractsWithTenancy::class)
    ->afterEach(function () {
        if (\function_exists('tenancy')) {
            tenancy()->end();
        }

        $this->deleteTestingTenants();

        unset($this->tenant);

        $this->dropTestingTenantDatabases();
    })
    ->in('Middleware');

pest()->extend(UiTestCase::class)
    ->use(InteractsWithTenancy::class)
    ->afterEach(function () {
        if (\function_exists('tenancy')) {
            tenancy()->end();
        }

        $this->deleteTestingTenants();

        unset($this->tenant);

        $this->dropTestingTenantDatabases();
    })
    ->in('UI');

pest()->extend(TestCase::class)
    ->use(InteractsWithTenancy::class)
    ->afterEach(function () {
        if (\function_exists('tenancy')) {
            tenancy()->end();
        }

        $this->deleteTestingTenants();

        unset($this->tenant);

        $this->dropTestingTenantDatabases();
    })
    ->in('Feature');

pest()->group('tenancy-provisioning')->in(
    'Feature/Admin/EmailContentSystemTest.php',
    'Feature/Auth',
    'Feature/Consistency/ModelServiceConsistencyTest.php',
    'Feature/DebugTenantTest.php',
    'Feature/EmailTemplate/EmailTemplateSeederTest.php',
    'Feature/Livewire/DataTable/DirectExecutionSecurityTest.php',
    'Feature/Livewire/SubscriptionManagementTest.php',
    'Feature/Livewire/TenantManagementTest.php',
    'Feature/Livewire/TenantSwitcherTest.php',
    'Feature/MailCredentialResolverTest.php',
    'Feature/Operational/OperationalConfigurationTest.php',
    'Feature/ReproductionTest.php',
    'Feature/Security/CsrfSecurityTest.php',
    'Feature/Security/ProtectedRoutesTest.php',
    'Feature/Security/TenantCspPresetTest.php',
    'Feature/Tenancy',
    'Feature/TenantPolicyTest.php',
    'Feature/TenantSwitcherAuthorizationTest.php',
    'Feature/TenantUserTableAuthorizationTest.php',
    'Feature/Users/ActivationServiceTest.php',
    'Feature/Users/UserCrudMailpitTest.php',
    'Feature/Users/UsersListAuthorizationTest.php',
    'Unit/Logging/TenantAwareLoggingTest.php',
);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Run the test as a specific tenant or create a new one.
 */
function asTenant(?Tenant $tenant = null)
{
    test()->setUpTenancy($tenant);

    return test();
}
