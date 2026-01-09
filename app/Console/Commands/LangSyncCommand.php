<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LangSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:sync
                            {--write : Actually write changes to files}
                            {--prune : Remove unused keys from non-protected files}
                            {--prune-all : Remove unused keys from all files (including ui.php and messages.php)}
                            {--allow-json : Allow JSON string keys for literal strings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync translation files across all locales, add missing keys, and optionally prune unused keys';

    /**
     * Default locale (source of truth).
     */
    protected string $defaultLocale;

    /**
     * Supported locales.
     *
     * @var array<string>
     */
    protected array $supportedLocales;

    /**
     * Protected translation files that should never be pruned.
     *
     * @var array<string>
     */
    protected array $protectedFiles;

    /**
     * Translation namespaces.
     *
     * @var array<string>
     */
    protected array $namespaces;

    /**
     * Extracted file name.
     */
    protected string $extractedFile;

    /**
     * Found translation keys from codebase scan with their usage locations.
     * Format: ['key' => ['file.php' => [10, 25], 'other.php' => [5]]]
     *
     * @var array<string, array<string, array<int>>>
     */
    protected array $foundKeys = [];

    /**
     * Statistics for the sync operation.
     *
     * @var array<string, int>
     */
    protected array $stats = [
        'keys_found' => 0,
        'keys_added' => 0,
        'keys_pruned' => 0,
        'files_updated' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->loadConfiguration();

        $this->info('Scanning codebase for translation usage...');
        $this->scanCodebase();

        $this->info("Found {$this->stats['keys_found']} translation keys in codebase.");

        if (! $this->option('write')) {
            $this->warn('Running in dry-run mode. Use --write to apply changes.');
        }

        // First, sync default locale with missing keys from codebase
        $this->info('Syncing default locale with missing keys...');
        $this->syncDefaultLocale();

        $this->info('Syncing locales...');
        $this->syncLocales();

        if ($this->option('prune') || $this->option('prune-all')) {
            $this->info('Pruning unused keys...');
            $this->pruneUnusedKeys();
        }

        $this->displaySummary();

        return Command::SUCCESS;
    }

    /**
     * Load configuration from i18n config.
     */
    protected function loadConfiguration(): void
    {
        $this->defaultLocale = config('i18n.default_locale', 'en_US');
        $this->supportedLocales = array_keys(config('i18n.supported_locales', []));
        $this->protectedFiles = config('i18n.protected_translation_files', []);
        $this->namespaces = config('i18n.namespaces', ['ui', 'messages']);
        $this->extractedFile = config('i18n.extracted_file', 'extracted');

        if (empty($this->supportedLocales)) {
            $this->error('No supported locales found in config/i18n.php');
            exit(1);
        }
    }

    /**
     * Scan codebase for translation usage.
     */
    protected function scanCodebase(): void
    {
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

        // Update stats
        $this->stats['keys_found'] = count($this->foundKeys);
    }

    /**
     * Scan a single file for translation keys and track their locations.
     */
    protected function scanFile(string $filePath): void
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
     * Sync all locales with the default locale as source of truth.
     */
    protected function syncLocales(): void
    {
        $defaultLangPath = lang_path($this->defaultLocale);

        if (! File::exists($defaultLangPath)) {
            $this->error("Default locale directory not found: {$defaultLangPath}");

            return;
        }

        // Get all translation files from default locale
        $defaultFiles = File::files($defaultLangPath);
        $translationFiles = [];

        foreach ($defaultFiles as $file) {
            $filename = $file->getFilenameWithoutExtension();
            if ($filename === 'extracted' && ! $this->option('allow-json')) {
                continue;
            }
            $translationFiles[] = $filename;
        }

        // Sync each locale
        foreach ($this->supportedLocales as $locale) {
            if ($locale === $this->defaultLocale) {
                continue; // Skip default locale
            }

            $this->syncLocale($locale, $translationFiles);
        }
    }

    /**
     * Sync a specific locale with the default locale.
     */
    protected function syncLocale(string $locale, array $translationFiles): void
    {
        $localePath = lang_path($locale);
        $defaultPath = lang_path($this->defaultLocale);

        if (! File::exists($localePath)) {
            File::makeDirectory($localePath, 0755, true);
            $this->info("Created locale directory: {$locale}");
        }

        foreach ($translationFiles as $file) {
            $defaultFile = "{$defaultPath}/{$file}.php";
            $localeFile = "{$localePath}/{$file}.php";

            if (! File::exists($defaultFile)) {
                continue;
            }

            $defaultTranslations = require $defaultFile;
            $localeTranslations = File::exists($localeFile) ? require $localeFile : [];

            // Merge: add missing keys from default, keep existing locale values
            $merged = $this->mergeTranslations($defaultTranslations, $localeTranslations, $file);

            // Check if there are changes
            if ($merged !== $localeTranslations) {
                if ($this->option('write')) {
                    $this->writeTranslationFile($localeFile, $merged);
                    $this->stats['files_updated']++;
                    $this->info("Updated: {$locale}/{$file}.php");
                } else {
                    $this->line("Would update: {$locale}/{$file}.php");
                }

                $added = $this->countAddedKeys($localeTranslations, $merged);
                $this->stats['keys_added'] += $added;
            }
        }
    }

    /**
     * Sync default locale with missing keys from codebase scan.
     */
    protected function syncDefaultLocale(): void
    {
        $defaultLangPath = lang_path($this->defaultLocale);

        if (! File::exists($defaultLangPath)) {
            File::makeDirectory($defaultLangPath, 0755, true);
        }

        // Group keys by namespace (file)
        $keysByFile = [];

        foreach ($this->foundKeys as $key => $locations) {
            // Determine which file this key belongs to based on namespace
            $namespace = $this->getNamespaceFromKey($key);
            if (! isset($keysByFile[$namespace])) {
                $keysByFile[$namespace] = [];
            }
            $keysByFile[$namespace][$key] = $locations;
        }

        // Sync each translation file
        foreach ($keysByFile as $filename => $keys) {
            $filePath = "{$defaultLangPath}/{$filename}.php";
            $existingTranslations = File::exists($filePath) ? require $filePath : [];

            // Add missing keys with their usage locations
            $updated = $this->addMissingKeysToDefault($existingTranslations, $keys, $filename);

            if ($updated !== $existingTranslations) {
                if ($this->option('write')) {
                    $this->writeTranslationFile($filePath, $updated);
                    $this->stats['files_updated']++;
                    $this->info("Updated default locale: {$this->defaultLocale}/{$filename}.php");
                } else {
                    $this->line("Would update default locale: {$this->defaultLocale}/{$filename}.php");
                }
            }
        }
    }

    /**
     * Get namespace (filename) from translation key.
     */
    protected function getNamespaceFromKey(string $key): string
    {
        // Check if key starts with a known namespace
        foreach ($this->namespaces as $namespace) {
            if (str_starts_with($key, "{$namespace}.")) {
                return $namespace;
            }
        }

        // Default to extracted file for unknown keys
        return $this->extractedFile;
    }

    /**
     * Add missing keys to default locale translations with usage locations.
     *
     * @param  array<string, mixed>  $translations
     * @param  array<string, array<string, array<int>>>  $keys
     * @return array<string, mixed>
     */
    protected function addMissingKeysToDefault(array $translations, array $keys, string $filename): array
    {
        $updated = $translations;

        foreach ($keys as $fullKey => $locations) {
            // Remove namespace prefix if present
            $keyWithoutNamespace = str_starts_with($fullKey, "{$filename}.")
                ? substr($fullKey, strlen($filename) + 1)
                : $fullKey;

            // Build location string (can be multiple)
            $locationStrings = [];
            foreach ($locations as $file => $lines) {
                foreach ($lines as $line) {
                    $locationStrings[] = "{$file}:{$line}";
                }
            }
            $locationValue = implode(', ', $locationStrings);

            // For extracted file, always treat keys as simple (even if they contain dots)
            // For other files, check if this is a namespaced key (has dots in the middle, not just at the end)
            $isSimpleKey = $filename === $this->extractedFile
                || ! str_contains($keyWithoutNamespace, '.')
                || (str_ends_with($keyWithoutNamespace, '.') && substr_count($keyWithoutNamespace, '.') === 1);

            // Determine the appropriate value based on whether the key is dynamic
            $valueToSet = $this->isDynamicKey($fullKey)
                ? $this->getDynamicKeyValue($fullKey, $locationValue)
                : "TRANSLATION_NEEDED: Please see context at {$locationValue}";

            if ($isSimpleKey) {
                // Simple key - set directly
                if (! isset($updated[$keyWithoutNamespace]) || $this->isRawLocationValue($updated[$keyWithoutNamespace])) {
                    $updated[$keyWithoutNamespace] = $valueToSet;
                    $this->stats['keys_added']++;
                }
            } else {
                // Navigate/create nested structure for namespaced keys
                $keyParts = explode('.', $keyWithoutNamespace);
                $current = &$updated;

                foreach ($keyParts as $i => $keyPart) {
                    if (empty($keyPart) && $i < count($keyParts) - 1) {
                        // Skip empty intermediate keys
                        continue;
                    }

                    if ($i === count($keyParts) - 1) {
                        // Last key - set the value if missing or if it's a raw location placeholder
                        if (! isset($current[$keyPart]) || $this->isRawLocationValue($current[$keyPart])) {
                            $current[$keyPart] = $valueToSet;
                            $this->stats['keys_added']++;
                        }
                    } else {
                        // Intermediate key - ensure array exists
                        if (! isset($current[$keyPart]) || ! is_array($current[$keyPart])) {
                            $current[$keyPart] = [];
                        }
                        $current = &$current[$keyPart];
                    }
                }
            }
        }

        return $updated;
    }

    /**
     * Check if a value appears to be a raw location reference.
     */
    protected function isRawLocationValue(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        // Check for common file extensions and line number pattern: .php:123 or .blade.php:123
        return preg_match('/\.php:\d+/', $value) === 1 || preg_match('/\.blade\.php:\d+/', $value) === 1;
    }

    /**
     * Check if a translation key contains dynamic PHP variable interpolation.
     *
     * Detects patterns like:
     * - "permissions.actions.{$action}" (curly brace interpolation)
     * - "permissions.actions.$action" (direct variable)
     */
    protected function isDynamicKey(string $key): bool
    {
        // Match {$var}, {$var->prop}, {$var['key']}, or direct $var patterns
        return preg_match('/\{\$[a-zA-Z_]|\$[a-zA-Z_]/', $key) === 1;
    }

    /**
     * Get the value for a dynamic translation key with AI-agent instructions.
     */
    protected function getDynamicKeyValue(string $key, string $locationValue): string
    {
        return "DYNAMIC_KEY: This key is dynamically constructed using PHP variables. "
            . "The variable portion should be resolved to all possible values from the source class/constants. "
            . "See source at {$locationValue} to find the values (e.g., from a constants class). "
            . "Create individual translation entries for each resolved key instead of this pattern.";
    }

    /**
     * Merge translations, adding missing keys from default.
     *
     * @param  array<string, mixed>  $default
     * @param  array<string, mixed>  $locale
     * @return array<string, mixed>
     */
    protected function mergeTranslations(array $default, array $locale, string $filename = '', string $prefix = ''): array
    {
        $merged = $locale;

        foreach ($default as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (! isset($merged[$key])) {
                // Key missing in locale - set empty value
                if (is_array($value)) {
                    // If default value is an array, recursively merge with empty array
                    $merged[$key] = $this->mergeTranslations($value, [], $filename, $fullKey);
                } else {
                    // For scalar values, set empty string
                    $merged[$key] = '';
                }
            } elseif (is_array($value) && is_array($merged[$key])) {
                // Both are arrays, recursively merge
                $merged[$key] = $this->mergeTranslations($value, $merged[$key], $filename, $fullKey);
            } elseif (is_array($value) && ! is_array($merged[$key])) {
                // Default is array but locale has scalar - replace with merged array
                $merged[$key] = $this->mergeTranslations($value, [], $filename, $fullKey);
            }
        }

        return $merged;
    }

    /**
     * Count how many keys were added.
     */
    protected function countAddedKeys(array $old, array $new): int
    {
        $count = 0;
        $this->countAddedKeysRecursive($old, $new, $count);

        return $count;
    }

    /**
     * Recursively count added keys.
     */
    protected function countAddedKeysRecursive(array $old, array $new, int &$count): void
    {
        foreach ($new as $key => $value) {
            if (! isset($old[$key])) {
                $count++;
            } elseif (is_array($value) && is_array($old[$key])) {
                $this->countAddedKeysRecursive($old[$key], $value, $count);
            }
        }
    }

    /**
     * Prune unused translation keys.
     */
    protected function pruneUnusedKeys(): void
    {
        foreach ($this->supportedLocales as $locale) {
            $localePath = lang_path($locale);
            if (! File::exists($localePath)) {
                continue;
            }

            $files = File::files($localePath);
            foreach ($files as $file) {
                $filename = $file->getFilenameWithoutExtension();

                // Skip protected files
                if (in_array($filename, $this->protectedFiles, true)) {
                    continue;
                }

                // Skip ui.php and messages.php unless --prune-all is set
                if (! $this->option('prune-all') && in_array($filename, $this->namespaces, true)) {
                    continue;
                }

                $this->pruneFile($locale, $filename);
            }
        }
    }

    /**
     * Prune unused keys from a specific file.
     */
    protected function pruneFile(string $locale, string $filename): void
    {
        $filePath = lang_path("{$locale}/{$filename}.php");
        if (! File::exists($filePath)) {
            return;
        }

        $translations = require $filePath;
        $pruned = $this->pruneTranslations($translations, $filename);

        if ($pruned !== $translations) {
            $prunedCount = $this->countPrunedKeys($translations, $pruned);
            $this->stats['keys_pruned'] += $prunedCount;

            if ($this->option('write')) {
                $this->writeTranslationFile($filePath, $pruned);
                $this->info("Pruned {$prunedCount} keys from {$locale}/{$filename}.php");
            } else {
                $this->line("Would prune {$prunedCount} keys from {$locale}/{$filename}.php");
            }
        }
    }

    /**
     * Prune unused keys from translations array.
     */
    protected function pruneTranslations(array $translations, string $namespace): array
    {
        $pruned = [];

        foreach ($translations as $key => $value) {
            $fullKey = $namespace === 'extracted' ? $key : "{$namespace}.{$key}";

            // Check if key is used in codebase
            $isUsed = $this->isKeyUsed($fullKey, $translations, $key, $value);

            if ($isUsed) {
                if (is_array($value)) {
                    $nestedPruned = $this->pruneNestedTranslations($value, $fullKey);
                    if (! empty($nestedPruned)) {
                        $pruned[$key] = $nestedPruned;
                    }
                } else {
                    $pruned[$key] = $value;
                }
            }
        }

        return $pruned;
    }

    /**
     * Prune nested translation arrays.
     */
    protected function pruneNestedTranslations(array $translations, string $parentKey): array
    {
        $pruned = [];

        foreach ($translations as $key => $value) {
            $fullKey = "{$parentKey}.{$key}";
            $isUsed = $this->isKeyUsed($fullKey, $translations, $key, $value);

            if ($isUsed) {
                if (is_array($value)) {
                    $nestedPruned = $this->pruneNestedTranslations($value, $fullKey);
                    if (! empty($nestedPruned)) {
                        $pruned[$key] = $nestedPruned;
                    }
                } else {
                    $pruned[$key] = $value;
                }
            }
        }

        return $pruned;
    }

    /**
     * Check if a translation key is used in the codebase.
     */
    protected function isKeyUsed(string $fullKey, array $context, string $shortKey, mixed $value): bool
    {
        // Check if the full key is found
        if (isset($this->foundKeys[$fullKey])) {
            return true;
        }

        // For nested arrays, check if any child keys are used
        if (is_array($value)) {
            foreach ($value as $nestedKey => $nestedValue) {
                $nestedFullKey = "{$fullKey}.{$nestedKey}";
                if ($this->isKeyUsed($nestedFullKey, $context, $nestedKey, $nestedValue)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Count how many keys were pruned.
     */
    protected function countPrunedKeys(array $old, array $new): int
    {
        $count = 0;
        $this->countPrunedKeysRecursive($old, $new, $count);

        return $count;
    }

    /**
     * Recursively count pruned keys.
     */
    protected function countPrunedKeysRecursive(array $old, array $new, int &$count): void
    {
        foreach ($old as $key => $value) {
            if (! isset($new[$key])) {
                $count++;
            } elseif (is_array($value) && is_array($new[$key])) {
                $this->countPrunedKeysRecursive($value, $new[$key], $count);
            }
        }
    }

    /**
     * Write translation file with proper formatting.
     */
    protected function writeTranslationFile(string $filePath, array $translations): void
    {
        $content = "<?php\n\n";

        // Add header for extracted file
        if (str_contains($filePath, "/{$this->extractedFile}.php")) {
            $content .= "/*\n";
            $content .= "|--------------------------------------------------------------------------\n";
            $content .= "| Extracted Translations\n";
            $content .= "|--------------------------------------------------------------------------\n";
            $content .= "|\n";
            $content .= "| Keys found here should be moved to appropriate files with semantic keys.\n";
            $content .= "|\n";
            $content .= "*/\n\n";
        }

        $content .= "return [\n";
        $content .= $this->arrayToPhpString($translations, 1);
        $content .= "];\n";

        File::put($filePath, $content);
    }

    /**
     * Convert array to PHP string representation.
     */
    protected function arrayToPhpString(array $array, int $indent = 0): string
    {
        $spaces = str_repeat('    ', $indent);
        $result = '';

        foreach ($array as $key => $value) {
            // Format key: use simple string if valid identifier, otherwise use var_export
            $keyStr = is_string($key) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)
                ? "'{$key}'"
                : var_export($key, true);

            if (is_array($value)) {
                $result .= "{$spaces}{$keyStr} => [\n";
                $result .= $this->arrayToPhpString($value, $indent + 1);
                $result .= "{$spaces}],\n";
            } else {
                // Properly escape string values using var_export
                // var_export handles all escaping including quotes, newlines, etc.
                $valueStr = var_export($value, true);
                $result .= "{$spaces}{$keyStr} => {$valueStr},\n";
            }
        }

        return $result;
    }

    /**
     * Display summary of sync operation.
     */
    protected function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== Sync Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Keys found in codebase', $this->stats['keys_found']],
                ['Keys added to locales', $this->stats['keys_added']],
                ['Keys pruned', $this->stats['keys_pruned']],
                ['Files updated', $this->stats['files_updated']],
            ],
        );

        if (! $this->option('write')) {
            $this->newLine();
            $this->warn('This was a dry run. Use --write to apply changes.');
        }
    }
}
