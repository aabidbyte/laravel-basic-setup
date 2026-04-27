<?php

namespace App\Console\Commands\Database;

use App\Enums\Database\ConnectionType;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

abstract class BaseDatabaseCommand extends Command
{
    protected ConnectionType $connectionType;

    protected function unifyName(string $name): string
    {
        return ucfirst(Str::camel($name));
    }

    /**
     * Get filtered options for sub-commands.
     */
    protected function getFilteredOptions(): array
    {
        $options = $this->options();
        $filtered = [];

        if ($this->hasOption('fresh') && $options['fresh']) {
            $filtered['--fresh'] = true;
        }

        if ($this->hasOption('seed') && $options['seed']) {
            $filtered['--seed'] = true;
        }

        if ($this->hasOption('force') && $options['force']) {
            $filtered['--force'] = true;
        }

        return $filtered;
    }
}
