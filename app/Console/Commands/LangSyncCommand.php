<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

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

        // Load configured namespaces and merge with auto-discovered ones
        $configuredNamespaces = config('i18n.namespaces', ['ui', 'messages']);
        $discoveredNamespaces = $this->discoverNamespaces();
        $this->namespaces = array_unique(array_merge($configuredNamespaces, $discoveredNamespaces));

        $this->extractedFile = config('i18n.extracted_file', 'extracted');

        if (empty($this->supportedLocales)) {
            $this->error('No supported locales found in config/i18n.php');
            exit(1);
        }
    }

    /**
     * Auto-discover namespaces from default locale files.
     *
     * @return array<string>
     */
    protected function discoverNamespaces(): array
    {
        $defaultPath = lang_path($this->defaultLocale);

        if (! File::exists($defaultPath)) {
            return [];
        }

        $files = File::files($defaultPath);
        $namespaces = [];
        $extractedFile = config('i18n.extracted_file', 'extracted');

        foreach ($files as $file) {
            $filename = $file->getFilenameWithoutExtension();

            // Skip extracted file
            if ($filename !== $extractedFile) {
                $namespaces[] = $filename;
            }
        }

        return $namespaces;
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

            // Try automatic resolution for dynamic keys
            if ($this->isDynamicKey($fullKey)) {
                $resolved = $this->tryAutoResolve($fullKey, $locations);

                if ($resolved !== null && ! empty($resolved)) {
                    // Successfully resolved - expand the key
                    foreach ($resolved as $value) {
                        $expandedKey = $this->expandKey($keyWithoutNamespace, $value);
                        $translationValue = $this->generateTranslationValue($expandedKey);

                        // Add expanded key to translations
                        if (str_contains($expandedKey, '.')) {
                            // Nested key
                            $parts = explode('.', $expandedKey);
                            $current = &$updated;

                            foreach ($parts as $idx => $part) {
                                if ($idx === count($parts) - 1) {
                                    if (! isset($current[$part])) {
                                        $current[$part] = $translationValue;
                                        $this->stats['keys_added']++;
                                    }
                                } else {
                                    if (! isset($current[$part]) || ! is_array($current[$part])) {
                                        $current[$part] = [];
                                    }
                                    $current = &$current[$part];
                                }
                            }
                        } else {
                            // Simple key
                            if (! isset($updated[$expandedKey])) {
                                $updated[$expandedKey] = $translationValue;
                                $this->stats['keys_added']++;
                            }
                        }
                    }

                    // Skip adding the dynamic pattern key
                    continue;
                }

                // Auto-resolution failed - use DYNAMIC_KEY marker
                $valueToSet = $this->getDynamicKeyValue($fullKey, $locationValue);
            } else {
                $valueToSet = "TRANSLATION_NEEDED: Please see context at {$locationValue}";
            }

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
     * Get the value for a dynamic translation key with configuration instructions.
     */
    protected function getDynamicKeyValue(string $key, string $locationValue): string
    {
        return 'DYNAMIC_KEY: This key was not found in config/translation-resolvers.php. '
            . "To auto-resolve this dynamic pattern:\n\n"
            . "1. Open config/translation-resolvers.php\n"
            . "2. Add this pattern with a resolver:\n"
            . "   '{$key}' => fn() => YourClass::getValues(),\n"
            . "3. Run: php artisan lang:sync --write\n\n"
            . "Example resolvers:\n"
            . "- Static method: fn() => PermissionAction::all()\n"
            . "- Service: fn() => array_keys(app(I18nService::class)->getSupportedLocales())\n"
            . "- Database: fn() => DB::table('x')->pluck('column')->toArray()\n"
            . "- Enum: fn() => array_map(fn(\$c) => \$c->value, Status::cases())\n\n"
            . "Source: {$locationValue}";
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

            // Skip DYNAMIC_KEY and TRANSLATION_NEEDED entries - they should only be in default locale
            if (is_string($value) && (str_starts_with($value, 'DYNAMIC_KEY:') || str_starts_with($value, 'TRANSLATION_NEEDED:'))) {
                continue;
            }

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

    /**
     * Extract code context around a translation key usage.
     */
    protected function extractContext(string $content, int $lineNum): array
    {
        $lines = explode("\n", $content);
        $startLine = max(0, $lineNum - 10);
        $endLine = min(count($lines), $lineNum + 10);

        $contextLines = array_slice($lines, $startLine, $endLine - $startLine);

        return [
            'code_block' => implode("\n", $contextLines),
            'line_number' => $lineNum,
        ];
    }

    /**
     * Try to automatically resolve a dynamic translation key using config.
     */
    protected function tryAutoResolve(string $key, array $locations): ?array
    {
        $resolvers = config('translation-resolvers.resolvers', []);

        // Check if we have a resolver for this pattern
        if (! isset($resolvers[$key])) {
            return null;
        }

        try {
            $resolver = $resolvers[$key];

            if (! is_callable($resolver)) {
                return null;
            }

            // Execute the resolver
            $values = $resolver();

            if (! is_array($values) || empty($values)) {
                return null;
            }

            return $values;
        } catch (Exception $e) {
            // Silent fail - will use DYNAMIC_KEY marker
            return null;
        }
    }

    /**
     * Analyze variable source from code context.
     */
    protected function analyzeVariableSource(string $key, array $context, string $fullContent): ?array
    {
        $codeBlock = $context['code_block'];

        // Pattern 1: Foreach loops with iterable source
        if (preg_match('/@foreach\s*\(\s*([^)]+?)\s+as\s+(?:\$\w+\s*=>\s*)?\$(\w+)\s*\)/', $codeBlock, $match)) {
            $source = trim($match[1]);

            return $this->resolveIterableSource($source, $fullContent);
        }

        // Pattern 2: Model property access (e.g., $template->type)
        if (preg_match('/\$(\w+)->(\w+)/', $key, $match)) {
            $varName = $match[1];
            $propertyName = $match[2];

            // Find the variable's class/type from context
            $modelClass = $this->findVariableType($varName, $codeBlock, $fullContent);

            if ($modelClass) {
                return [
                    'type' => 'model_property',
                    'model' => $modelClass,
                    'property' => $propertyName,
                ];
            }
        }

        return null;
    }

    /**
     * Resolve an iterable source (service call, static method, etc).
     */
    protected function resolveIterableSource(string $source, string $fullContent): ?array
    {
        // Pattern: app(ServiceClass::class)->method()
        if (preg_match('/app\s*\(\s*([^)]+)\s*\)\s*->\s*(\w+)\s*\(\s*\)/', $source, $match)) {
            $service = trim($match[1], '\'"');
            $method = $match[2];

            return [
                'type' => 'service_method',
                'service' => $service,
                'method' => $method,
            ];
        }

        // Pattern: ClassName::method()
        if (preg_match('/([A-Z]\w+)::(\w+)\s*\(\s*\)/', $source, $match)) {
            $className = $match[1];
            $method = $match[2];

            $fullClassName = $this->resolveClassName($className, $fullContent);

            return [
                'type' => 'static_method',
                'class' => $fullClassName,
                'method' => $method,
            ];
        }

        // Pattern: $variable->method() - need to find the variable's type
        if (preg_match('/\$(\w+)->(\w+)\s*\(\s*\)/', $source, $match)) {
            $varName = $match[1];
            $method = $match[2];

            $className = $this->findVariableType($varName, $fullContent, $fullContent);

            if ($className) {
                return [
                    'type' => 'instance_method',
                    'class' => $className,
                    'method' => $method,
                ];
            }
        }

        return null;
    }

    /**
     * Resolve class name to fully qualified name.
     */
    protected function resolveClassName(string $className, string $fileContent): string
    {
        // Check for use statements
        $pattern = '/use\s+([^;]+\\\\' . preg_quote($className) . ')\s*;/';
        if (preg_match($pattern, $fileContent, $match)) {
            return $match[1];
        }

        // Common Laravel namespaces
        $commonNamespaces = [
            'App\\Constants\\Auth\\',
            'App\\Enums\\',
            'App\\Services\\',
            'App\\Models\\',
        ];

        foreach ($commonNamespaces as $namespace) {
            $fqn = $namespace . $className;
            if (class_exists($fqn)) {
                return $fqn;
            }
        }

        return $className;
    }

    /**
     * Find variable type from context.
     */
    protected function findVariableType(string $varName, string $context, string $fullContent): ?string
    {
        // Pattern: Type $varName (method parameter)
        if (preg_match('/([A-Z]\w+)\s+\$' . preg_quote($varName) . '\b/', $context, $match)) {
            return $this->resolveClassName($match[1], $fullContent);
        }

        // Pattern: $varName = new ClassName()
        if (preg_match('/\$' . preg_quote($varName) . '\s*=\s*new\s+([A-Z]\w+)/', $context, $match)) {
            return $this->resolveClassName($match[1], $fullContent);
        }

        return null;
    }

    /**
     * Execute resolver to get actual values.
     */
    protected function executeResolver(array $resolver): ?array
    {
        try {
            $command = null;

            if ($resolver['type'] === 'service_method') {
                $command = sprintf(
                    'echo json_encode(array_keys(app(%s)->%s()));',
                    $resolver['service'],
                    $resolver['method'],
                );
            } elseif ($resolver['type'] === 'static_method' || $resolver['type'] === 'instance_method') {
                $command = sprintf(
                    'echo json_encode(%s::%s());',
                    $resolver['class'],
                    $resolver['method'],
                );
            } elseif ($resolver['type'] === 'model_property') {
                // Check if property is an enum cast
                $command = $this->buildEnumCommand($resolver);
            }

            if ($command === null) {
                return null;
            }

            $result = Process::timeout(10)->run(
                sprintf('php artisan tinker --execute="%s"', addslashes($command)),
            );

            if ($result->successful()) {
                $output = trim($result->output());
                $decoded = json_decode($output, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (Exception $e) {
            // Silent fail - will fall back to DYNAMIC_KEY
        }

        return null;
    }

    /**
     * Build command to get enum values from model cast.
     */
    protected function buildEnumCommand(array $resolver): ?string
    {
        return sprintf(
            '\$reflection = new \\ReflectionClass(%s); ' .
            '\$casts = \$reflection->hasMethod(\'casts\') ? (new %s)->casts() : []; ' .
            'if (isset(\$casts[\'%s\']) && enum_exists(\$casts[\'%s\'])) { ' .
            '  echo json_encode(array_map(fn(\$case) => \$case->value, \$casts[\'%s\']::cases())); ' .
            '}',
            $resolver['model'],
            $resolver['model'],
            $resolver['property'],
            $resolver['property'],
            $resolver['property'],
        );
    }

    /**
     * Expand a dynamic key pattern with a concrete value.
     * For 'locales.{$translation->locale}' with value 'en_US', returns 'locales.en_US'
     */
    protected function expandKey(string $pattern, string $value): string
    {
        // Replace {$var->property} patterns (e.g., {$translation->locale})
        $expanded = preg_replace('/\{\$[\w>-]+\}/', $value, $pattern);

        // Replace {$var} patterns
        $expanded = preg_replace('/\{\$\w+\}/', $value, $expanded);

        // Replace $var patterns (e.g., $type, $code)
        $expanded = preg_replace('/\$\w+/', $value, $expanded);

        return $expanded;
    }

    /**
     * Generate a human-readable translation value from a key.
     */
    protected function generateTranslationValue(string $key): string
    {
        // Get the last part of the key
        $parts = explode('.', $key);
        $value = end($parts);

        // Convert snake_case or kebab-case to Title Case
        $value = str_replace(['_', '-'], ' ', $value);
        $value = ucwords($value);

        return $value;
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
