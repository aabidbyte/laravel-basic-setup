<?php

namespace App\Logging;

class TenantLogPath
{
    public static function resolve(string $path): string
    {
        $tenantId = self::tenantId();

        if ($tenantId === null) {
            return $path;
        }

        $logsPath = base_path('storage/logs');
        $normalizedLogsPath = \rtrim($logsPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $normalizedPath = \str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if (! \str_starts_with($normalizedPath, $normalizedLogsPath)) {
            return $path;
        }

        $relativePath = \substr($normalizedPath, \strlen($normalizedLogsPath));

        return $normalizedLogsPath . self::safeTenantDirectory($tenantId) . DIRECTORY_SEPARATOR . $relativePath;
    }

    private static function tenantId(): ?string
    {
        if (! \function_exists('tenant')) {
            return null;
        }

        $tenantId = \tenant('id');

        if (! \is_string($tenantId) || $tenantId === '') {
            return null;
        }

        return $tenantId;
    }

    private static function safeTenantDirectory(string $tenantId): string
    {
        return \preg_replace('/[^A-Za-z0-9._-]/', '_', $tenantId) ?: 'tenant';
    }
}
