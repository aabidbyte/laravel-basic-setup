<?php

declare(strict_types=1);

namespace App\Support\Translation;

/**
 * Data Object for scanning a pattern in content.
 */
readonly class ScanOptions
{
    /**
     * Create a new ScanOptions instance.
     *
     * @param  string  $content  The file content
     * @param  array<int, string>  $lines  Content split into lines
     * @param  string  $pattern  The regex pattern to scan
     * @param  string  $filePath  The path of the file being scanned
     * @param  array<array<int>>  $commentRanges  Byte ranges of comments to exclude
     */
    public function __construct(
        public string $content,
        public array $lines,
        public string $pattern,
        public string $filePath,
        public array $commentRanges = [],
    ) {}
}
