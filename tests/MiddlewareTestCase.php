<?php

declare(strict_types=1);

namespace Tests;

/**
 * Feature tests that only need migrated schema + factories, not {@see DatabaseSeeder}
 * (avoids tenant provisioning paths that require ext-redis in some environments).
 */
abstract class MiddlewareTestCase extends TestCase
{
    protected $seed = false;
}
