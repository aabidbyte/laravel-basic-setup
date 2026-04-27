<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Concerns;

use Illuminate\Process\Pool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;

trait CanRunInParallel
{
    /**
     * Run a command in parallel for a query or collection.
     */
    protected function runInParallel(
        $query,
        string $commandName,
        callable $argumentsCallback,
        int $chunkSize = 5,
    ): bool {
        $totalItems = $query->count();
        $totalChunks = (int) \ceil($totalItems / $chunkSize);

        $this->info("Found {$totalItems} items. Running '{$commandName}' in parallel (chunk size: {$chunkSize})...");

        $anyFailed = false;
        $chunkIndex = 0;

        $processChunk = function ($chunk) use (&$anyFailed, &$chunkIndex, $commandName, $argumentsCallback, $totalChunks) {
            $chunkIndex++;
            $this->info("\n>>> Processing chunk {$chunkIndex} / {$totalChunks}");

            $invokedPool = Process::pool(function (Pool $pool) use ($chunk, $commandName, $argumentsCallback) {
                foreach ($chunk as $item) {
                    $args = (array) $argumentsCallback($item);
                    $flattenedArgs = [];

                    foreach ($args as $key => $value) {
                        if (is_int($key)) {
                            $flattenedArgs[] = $value;
                        } elseif ($value === true) {
                            $flattenedArgs[] = $key;
                        } elseif ($value !== false && $value !== null) {
                            $flattenedArgs[] = $key;
                            $flattenedArgs[] = (string) $value;
                        }
                    }

                    $label = $flattenedArgs[0] ?? 'process';

                    $pool->as($label)->command([
                        PHP_BINARY,
                        base_path('artisan'),
                        $commandName,
                        ...$flattenedArgs,
                        '--ansi',
                        '--no-interaction',
                    ]);
                }
            })->start(function (string $type, string $output, string $key) {
                if ($output !== '') {
                    $lines = \explode(PHP_EOL, \trim($output));
                    foreach ($lines as $line) {
                        if ($line !== '') {
                            $this->output->writeln("<fg=gray>[{$key}]</> {$line}");
                        }
                    }
                }
            });

            // Reactive Wait Loop: Keep checking all processes to flush their output buffers in real-time.
            // This prevents the "serial appearance" of logs.
            while ($invokedPool->running()->isNotEmpty()) {
                \usleep(10000); // 10ms
            }

            $results = $invokedPool->wait();

            if ($results->failed()) {
                $anyFailed = true;
            }
        };

        if ($query instanceof Collection) {
            $query->chunk($chunkSize)->each($processChunk);
        } else {
            $query->chunk($chunkSize, $processChunk);
        }

        return ! $anyFailed;
    }
}
