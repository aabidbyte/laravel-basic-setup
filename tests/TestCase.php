<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\InteractsWithTenancy;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    /**
     * The connections that should be transacted.
     *
     * @var array<int, string>
     */
    protected array $connectionsToTransact = ['mysql', 'central'];

    protected $seed = true;

    public function createApplication()
    {
        $app = require Application::inferBasePath() . '/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
