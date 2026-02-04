<?php

declare(strict_types=1);

namespace App\Support\Translation;

/**
 * Data Object for merging translations.
 */
readonly class MergeOptions
{
    /**
     * Create a new MergeOptions instance.
     *
     * @param  array<string, mixed>  $source  Source translations
     * @param  array<string, mixed>  $locale  Target translations
     * @param  string  $filename  The file name being merged
     * @param  string  $prefix  The key prefix for nested merging
     */
    public function __construct(
        public array $source,
        public array $locale,
        public string $filename = '',
        public string $prefix = '',
    ) {}
}
