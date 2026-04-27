<?php

declare(strict_types=1);

namespace App\Enums\Database;

/**
 * Types of seeders in the multi-tenancy architecture
 */
enum SeederType: string
{
    case COMMON = 'common';
    case TARGET = 'target';

    public function folderName(): string
    {
        return match ($this) {
            self::COMMON => 'CommonSeeders',
            self::TARGET => 'TargetSeeders',
        };
    }
}
