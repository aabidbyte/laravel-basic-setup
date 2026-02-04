<?php

declare(strict_types=1);

namespace App\Support\Translation;

/**
 * Data Object for Syncing a specific locale.
 */
readonly class SyncOptions
{
    /**
     * Create a new SyncOptions instance.
     *
     * @param  string  $locale  The target locale
     * @param  array<string>  $translationFiles  List of files to sync
     * @param  bool  $write  Whether to write changes to disk
     * @param  mixed  $output  Output interface for feedback
     */
    public function __construct(
        public string $locale,
        public array $translationFiles,
        public bool $write,
        public mixed $output = null,
    ) {}
}
