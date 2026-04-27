<?php

namespace Tests\Traits;

trait UsesTenantDb
{
    protected function setUpUsesTenantDb(): void
    {
        // Connection is handled by TestCase::applyConfiguredDatabaseConnection
    }
}
