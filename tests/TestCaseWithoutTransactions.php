<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * A TestCase variant that does NOT apply DatabaseTransactions.
 *
 * Use this for tests that rely on DB::afterCommit callbacks — those
 * callbacks only fire on a real commit, which DatabaseTransactions
 * prevents by wrapping everything in a rolled-back transaction.
 *
 * Each test file using this base must perform its own cleanup in afterEach.
 */
abstract class TestCaseWithoutTransactions extends BaseTestCase
{
    use Support\MultiTenancyTestCase;

    public function createApplication(): Application
    {
        $app = require Application::inferBasePath() . '/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();
        $this->app = $app;

        if ($app->environment('testing')) {
            $this->setupMultiTenancyTests();
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
