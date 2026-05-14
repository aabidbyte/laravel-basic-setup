<?php

namespace App\Logging;

use Monolog\Handler\RotatingFileHandler;
use Monolog\LogRecord;
use Monolog\Utils;

class TenantAwareRotatingFileHandler extends RotatingFileHandler
{
    private string $baseFilename;

    public function __construct(string $filename, mixed ...$arguments)
    {
        $this->baseFilename = $filename;

        parent::__construct(TenantLogPath::resolve($filename), ...$arguments);
    }

    protected function write(LogRecord $record): void
    {
        $this->useResolvedTenantPath();

        parent::write($record);
    }

    private function useResolvedTenantPath(): void
    {
        $filename = Utils::canonicalizePath(TenantLogPath::resolve($this->baseFilename));

        if ($filename === $this->filename) {
            return;
        }

        $this->close();

        $this->filename = $filename;
        $this->url = $this->getTimedFilename();
        $this->mustRotate = null;
    }
}
