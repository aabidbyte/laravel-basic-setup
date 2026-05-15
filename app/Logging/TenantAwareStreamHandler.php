<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\LogRecord;
use Monolog\Utils;

class TenantAwareStreamHandler extends StreamHandler
{
    private string $baseStream;

    public function __construct(string $stream, mixed ...$arguments)
    {
        $this->baseStream = $stream;

        parent::__construct(TenantLogPath::resolve($stream), ...$arguments);
    }

    protected function write(LogRecord $record): void
    {
        $this->useResolvedTenantPath();

        parent::write($record);
    }

    private function useResolvedTenantPath(): void
    {
        $stream = Utils::canonicalizePath(TenantLogPath::resolve($this->baseStream));

        if ($stream === $this->url) {
            return;
        }

        $this->close();

        $this->url = $stream;
    }
}
