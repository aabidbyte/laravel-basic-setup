<?php

namespace App\Logging;

use App\Models\Tenant;

class TenantLogPath
{
    public static function resolve(string $path): string
    {
        $tenantDirectory = self::tenantDirectory();

        if ($tenantDirectory === null) {
            return $path;
        }

        $logsPath = base_path('storage/logs');
        $normalizedLogsPath = \rtrim($logsPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $normalizedPath = \str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if (! \str_starts_with($normalizedPath, $normalizedLogsPath)) {
            return $path;
        }

        $relativePath = \substr($normalizedPath, \strlen($normalizedLogsPath));

        return $normalizedLogsPath . $tenantDirectory . DIRECTORY_SEPARATOR . $relativePath;
    }

    private static function tenantDirectory(): ?string
    {
        if (! \function_exists('tenant')) {
            return null;
        }

        $tenant = \tenant();

        if ($tenant instanceof Tenant) {
            return $tenant->logDirectoryName();
        }

        $tenantId = \tenant()?->getTenantKey();

        if (! \is_string($tenantId) || $tenantId === '') {
            return null;
        }

        return self::safeTenantDirectory($tenantId);
    }

    private static function safeTenantDirectory(string $tenantId): string
    {
        return \preg_replace('/[^A-Za-z0-9._-]/', '_', $tenantId) ?: 'tenant';
    }
}
