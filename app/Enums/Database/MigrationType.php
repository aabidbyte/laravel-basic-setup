<?php

declare(strict_types=1);

namespace App\Enums\Database;

/**
 * Types of migrations in the multi-tenancy architecture
 */
enum MigrationType: string
{
    case COMMON = 'common';
    case TARGET = 'target';

    public function folderName(): string
    {
        return match ($this) {
            self::COMMON => 'CommonMigrations',
            self::TARGET => 'TargetMigrations',
        };
    }
}
