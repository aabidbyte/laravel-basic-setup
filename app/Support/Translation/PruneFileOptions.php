<?php

declare(strict_types=1);

namespace App\Support\Translation;

/**
 * Data Object for pruning a specific file.
 */
readonly class PruneFileOptions
{
    /**
     * Create a new PruneFileOptions instance.
     *
     * @param  string  $locale  The locale
     * @param  string  $filename  The filename
     * @param  bool  $write  Whether to write changes
     * @param  mixed  $output  Output interface
     */
    public function __construct(
        public string $locale,
        public string $filename,
        public bool $write,
        public mixed $output = null,
    ) {}
}
