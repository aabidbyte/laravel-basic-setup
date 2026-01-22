<?php

declare(strict_types=1);

namespace App\Services\Translation;

use Illuminate\Support\Facades\File;

class TranslationScanner
{
    /**
     * Found translation keys from codebase scan with their usage locations.
     * Format: ['key' => ['file.php' => [10, 25], 'other.php' => [5]]]
     *
     * @var array<string, array<string, array<int>>>
     */
    protected array $foundKeys = [];

    /**
     * Get found keys.
     *
     * @return array<string, array<string, array<int>>>
     */
    public function getFoundKeys(): array
    {
        return $this->foundKeys;
    }

    /**
     * Scan codebase for translation usage.
     */
    public function scanCodebase(): void
    {
        $this->foundKeys = [];
        $paths = [
            app_path(),
            resource_path('views'),
        ];

        foreach ($paths as $path) {
            if (! File::exists($path)) {
                continue;
            }

            $files = File::allFiles($path);
            foreach ($files as $file) {
                $extension = $file->getExtension();
                if (! in_array($extension, ['php', 'blade.php'], true)) {
                    continue;
                }

                $this->scanFile($file->getPathname());
            }
        }
    }

    /**
     * Scan a single file for translation keys and track their locations.
     */
    public function scanFile(string $filePath): void
    {
        $content = File::get($filePath);
        $lines = explode("\n", $content);
        $relativePath = str_replace(base_path() . '/', '', $filePath);

        // Get comment ranges to exclude matches within comments
        $commentRanges = $this->getCommentRanges($content);

        // Pattern 1: __('key') or __('namespace.key') - allow parameters
        $this->scanPattern($content, $lines, "/__\s*\(\s*['\"]([^'\"]+)['\"]/", $relativePath, $commentRanges);

        // Pattern 2: @lang('key') - allow parameters
        $this->scanPattern($content, $lines, "/@lang\s*\(\s*['\"]([^'\"]+)['\"]/", $relativePath, $commentRanges);

        // Pattern 3: trans('key') - allow parameters
        $this->scanPattern($content, $lines, "/trans\s*\(\s*['\"]([^'\"]+)['\"]/", $relativePath, $commentRanges);

        // Pattern 4: :label="__('key')" (Blade attributes) - allow parameters
        $this->scanPattern($content, $lines, "/:\w+\s*=\s*__\s*\(\s*['\"]([^'\"]+)['\"]/", $relativePath, $commentRanges);

        // Pattern 5: ->title('key') (NotificationBuilder)
        $this->scanPattern($content, $lines, "/->title\s*\(\s*['\"]([^'\"]+)['\"]/", $relativePath, $commentRanges);

        // Pattern 6: ->subtitle('key') (NotificationBuilder)
        $this->scanPattern($content, $lines, "/->subtitle\s*\(\s*['\"]([^'\"]+)['\"]/", $relativePath, $commentRanges);

        // Pattern 7: DISABLED - was creating empty string keys from concatenation
        // __('string' . $variable) patterns should use existing keys
    }

    /**
     * Get comment ranges in the content.
     * Returns array of [start, end] offset pairs for all comments.
     *
     * @return array<int, array{0: int, 1: int}>
     */
    protected function getCommentRanges(string $content): array
    {
        $ranges = [];
        $length = strlen($content);
        $i = 0;

        while ($i < $length) {
            // Single-line comment: //
            if ($i + 1 < $length && $content[$i] === '/' && $content[$i + 1] === '/') {
                $start = $i;
                // Find end of line
                while ($i < $length && $content[$i] !== "\n") {
                    $i++;
                }
                $ranges[] = [$start, $i];

                continue;
            }

            // Multi-line comment: /* ... */
            if ($i + 1 < $length && $content[$i] === '/' && $content[$i + 1] === '*') {
                $start = $i;
                $i += 2;
                // Find closing */
                while ($i + 1 < $length) {
                    if ($content[$i] === '*' && $content[$i + 1] === '/') {
                        $i += 2;
                        $ranges[] = [$start, $i];
                        break;
                    }
                    $i++;
                }

                continue;
            }

            // Hash comment: # (less common in PHP, but handle it)
            if ($content[$i] === '#') {
                $start = $i;
                // Find end of line
                while ($i < $length && $content[$i] !== "\n") {
                    $i++;
                }
                $ranges[] = [$start, $i];

                continue;
            }

            // Skip string literals to avoid false positives
            if ($content[$i] === '"' || $content[$i] === "'") {
                $quote = $content[$i];
                $i++;
                // Skip escaped quotes and find closing quote
                while ($i < $length) {
                    if ($content[$i] === '\\') {
                        $i += 2; // Skip escaped character

                        continue;
                    }
                    if ($content[$i] === $quote) {
                        $i++;
                        break;
                    }
                    $i++;
                }

                continue;
            }

            $i++;
        }

        return $ranges;
    }

    /**
     * Check if an offset is within any comment range.
     *
     * @param  array<int, array{0: int, 1: int}>  $commentRanges
     */
    protected function isInComment(int $offset, array $commentRanges): bool
    {
        foreach ($commentRanges as [$start, $end]) {
            if ($offset >= $start && $offset < $end) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scan content for a pattern and track line numbers.
     */
    protected function scanPattern(string $content, array $lines, string $pattern, string $filePath, array $commentRanges = []): void
    {
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[1])) {
            return;
        }

        // $matches[0] contains full matches, $matches[1] contains captured groups
        foreach ($matches[0] as $index => $fullMatch) {
            if (! is_array($fullMatch) || ! isset($fullMatch[0], $fullMatch[1])) {
                continue;
            }

            // Get the full match offset (start of __('key') or similar)
            $fullMatchOffset = (int) $fullMatch[1];

            // Skip if the full match is within a comment
            if (! empty($commentRanges) && $this->isInComment($fullMatchOffset, $commentRanges)) {
                continue;
            }

            // Skip if inside an enum label() method (e.g., __("key.{$this->value}"))
            if ($this->isInEnumLabelMethod($content, $fullMatchOffset, $filePath)) {
                continue;
            }

            // Get the captured key from $matches[1]
            if (! isset($matches[1][$index]) || ! is_array($matches[1][$index])) {
                continue;
            }

            $keyMatch = $matches[1][$index];
            if (! isset($keyMatch[0], $keyMatch[1])) {
                continue;
            }

            $key = (string) $keyMatch[0];
            $keyOffset = (int) $keyMatch[1];

            // Skip empty keys
            if (trim($key) === '') {
                continue;
            }

            // Calculate line number from key offset
            $lineNum = substr_count(substr($content, 0, $keyOffset), "\n") + 1;

            // Initialize key if not exists
            if (! isset($this->foundKeys[$key])) {
                $this->foundKeys[$key] = [];
            }

            // Initialize file if not exists
            if (! isset($this->foundKeys[$key][$filePath])) {
                $this->foundKeys[$key][$filePath] = [];
            }

            // Add line number if not already present
            if (! in_array($lineNum, $this->foundKeys[$key][$filePath], true)) {
                $this->foundKeys[$key][$filePath][] = $lineNum;
            }
        }
    }

    /**
     * Check if an offset is within an enum label() method.
     *
     * Detects patterns like:
     * public function label(): string {
     *     return __("key.{$this->value}");
     * }
     */
    protected function isInEnumLabelMethod(string $content, int $offset, string $filePath = ''): bool
    {
        // Check if file is in app/Enums/ directory
        if (! str_contains($filePath, 'app/Enums/')) {
            return false;
        }

        // Check if the surrounding content contains {$this->value}
        $before = substr($content, max(0, $offset - 100), min(100, $offset));
        $after = substr($content, $offset, 100);
        $context = $before . $after;

        // If we find {$this->value} in the pattern, it's an enum label method
        return str_contains($context, '{$this->value}');
    }
}
