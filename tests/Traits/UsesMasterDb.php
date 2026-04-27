<?php

namespace Tests\Traits;

trait UsesMasterDb
{
    protected function setUpUsesMasterDb(): void
    {
        // Connection is handled by TestCase::applyConfiguredDatabaseConnection
    }
}
