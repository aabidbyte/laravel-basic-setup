<?php

declare(strict_types=1);

namespace App\Enums\Database;

/**
 * Seeder execution environments
 */
enum SeederEnvironment: string
{
    case PRODUCTION = 'production';
    case DEVELOPMENT = 'development';

    public function folderName(): string
    {
        return match ($this) {
            self::PRODUCTION => 'Production',
            self::DEVELOPMENT => 'Development',
        };
    }

    public function shouldRun(): bool
    {
        if ($this === self::PRODUCTION) {
            return true;
        }

        return ! isProduction();
    }
}
