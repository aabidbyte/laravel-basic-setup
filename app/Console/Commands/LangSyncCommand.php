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
     * Found translation keys from codebase scan.
     *
     * @var array<string>
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
                if (! in_array($extension, ['php', 'blade.php'])) {
                    continue;
                }

                $this->scanFile($file->getPathname());
            }
        }

        // Remove duplicates and update stats
        $this->foundKeys = array_unique($this->foundKeys);
        $this->stats['keys_found'] = count($this->foundKeys);
    }

    /**
     * Scan a single file for translation keys.
     */
    protected function scanFile(string $filePath): void
    {
        $content = File::get($filePath);

        // Pattern 1: __('key') or __('namespace.key')
        preg_match_all("/__\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $matches1);
        if (! empty($matches1[1])) {
            foreach ($matches1[1] as $key) {
                $this->foundKeys[] = $key;
            }
        }

        // Pattern 2: @lang('key')
        preg_match_all("/@lang\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $matches2);
        if (! empty($matches2[1])) {
            foreach ($matches2[1] as $key) {
                $this->foundKeys[] = $key;
            }
        }

        // Pattern 3: trans('key')
        preg_match_all("/trans\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $matches3);
        if (! empty($matches3[1])) {
            foreach ($matches3[1] as $key) {
                $this->foundKeys[] = $key;
            }
        }

        // Pattern 4: :label="__('key')" (Blade attributes)
        preg_match_all("/:\w+\s*=\s*__\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $matches4);
        if (! empty($matches4[1])) {
            foreach ($matches4[1] as $key) {
                $this->foundKeys[] = $key;
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
            $merged = $this->mergeTranslations($defaultTranslations, $localeTranslations);

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
     * Merge translations, adding missing keys from default.
     */
    protected function mergeTranslations(array $default, array $locale): array
    {
        $merged = $locale;

        foreach ($default as $key => $value) {
            if (! isset($merged[$key])) {
                // Key missing in locale, add from default
                $merged[$key] = $value;
            } elseif (is_array($value) && is_array($merged[$key])) {
                // Both are arrays, recursively merge
                $merged[$key] = $this->mergeTranslations($value, $merged[$key]);
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
                if (in_array($filename, $this->protectedFiles)) {
                    continue;
                }

                // Skip ui.php and messages.php unless --prune-all is set
                if (! $this->option('prune-all') && in_array($filename, $this->namespaces)) {
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
        if (in_array($fullKey, $this->foundKeys)) {
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
        $content = "<?php\n\nreturn [\n";
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
            $keyStr = is_string($key) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key) ? $key : var_export($key, true);

            if (is_array($value)) {
                $result .= "{$spaces}{$keyStr} => [\n";
                $result .= $this->arrayToPhpString($value, $indent + 1);
                $result .= "{$spaces}],\n";
            } else {
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
            ]
        );

        if (! $this->option('write')) {
            $this->newLine();
            $this->warn('This was a dry run. Use --write to apply changes.');
        }
    }
}
