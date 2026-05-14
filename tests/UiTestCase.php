<?php

declare(strict_types=1);

namespace Tests;

/**
 * Blade/UI feature tests that need the app container and migrations but must not run
 * {@see DatabaseSeeder} (avoids tenant + ext-redis provisioning in environments without Redis).
 */
abstract class UiTestCase extends TestCase
{
    protected $seed = false;
}
