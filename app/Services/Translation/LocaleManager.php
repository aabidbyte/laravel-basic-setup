<?php

declare(strict_types=1);

namespace App\Services\Translation;

use App\Support\Translation\MergeOptions;
use App\Support\Translation\SyncOptions;
use App\Support\Translation\TranslationConfig;
use Illuminate\Support\Facades\File;

class LocaleManager
{
    protected string $sourceLocale;

    protected array $supportedLocales;

    protected array $namespaces;

    protected string $extractedFile;

    // Statistics for key additions
    protected int $keysAdded = 0;

    protected int $filesUpdated = 0;

    protected DynamicKeyResolver $dynamicKeyResolver;

    public function __construct(DynamicKeyResolver $dynamicKeyResolver)
    {
        $this->dynamicKeyResolver = $dynamicKeyResolver;
    }

    public function setConfiguration(TranslationConfig $config): void
    {
        $this->sourceLocale = $config->sourceLocale;
        $this->supportedLocales = $config->supportedLocales;
        $this->namespaces = $config->namespaces;
        $this->extractedFile = $config->extractedFile;
    }

    public function getSourceLocale(): string
    {
        return $this->sourceLocale;
    }

    public function getKeysAdded(): int
    {
        return $this->keysAdded;
    }

    public function getFilesUpdated(): int
    {
        return $this->filesUpdated;
    }

    public function resetStats(): void
    {
        $this->keysAdded = 0;
        $this->filesUpdated = 0;
    }

    /**
     * Auto-discover namespaces from source locale files.
     *
     * @return array<string>
     */
    public function discoverNamespaces(): array
    {
        $sourcePath = lang_path($this->sourceLocale);

        if (! File::exists($sourcePath)) {
            return [];
        }

        $files = File::files($sourcePath);
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
     * Sync all locales with the source locale as source of truth.
     */
    public function syncLocales(bool $write = false, bool $allowJson = false, $output = null): void
    {
        $sourceLangPath = lang_path($this->sourceLocale);

        if (! File::exists($sourceLangPath)) {
            if ($output) {
                $output->error("Source locale directory not found: {$sourceLangPath}");
            }

            return;
        }

        // Get all translation files from source locale
        $sourceFiles = File::files($sourceLangPath);
        $translationFiles = [];

        foreach ($sourceFiles as $file) {
            $filename = $file->getFilenameWithoutExtension();
            if ($filename === 'extracted' && ! $allowJson) {
                continue;
            }
            $translationFiles[] = $filename;
        }

        // Sync each locale
        foreach ($this->supportedLocales as $locale) {
            if ($locale === $this->sourceLocale) {
                continue; // Skip source locale
            }

            $this->syncLocale(new SyncOptions(
                locale: $locale,
                translationFiles: $translationFiles,
                write: $write,
                output: $output,
            ));
        }
    }

    /**
     * Sync a specific locale with the source locale.
     */
    public function syncLocale(SyncOptions $options): void
    {
        $localePath = lang_path($options->locale);
        $sourcePath = lang_path($this->sourceLocale);

        if (! File::exists($localePath)) {
            File::makeDirectory($localePath, 0755, true);
            if ($options->output) {
                $options->output->info("Created locale directory: {$options->locale}");
            }
        }

        foreach ($options->translationFiles as $file) {
            $sourceFile = "{$sourcePath}/{$file}.php";
            $localeFile = "{$localePath}/{$file}.php";

            if (! File::exists($sourceFile)) {
                continue;
            }

            $sourceTranslations = require $sourceFile;
            $localeTranslations = File::exists($localeFile) ? require $localeFile : [];

            // Merge: add missing keys from source, keep existing locale values
            $merged = $this->mergeTranslations(new MergeOptions(
                source: $sourceTranslations,
                locale: $localeTranslations,
                filename: $file,
            ));

            // Check if there are changes
            if ($merged !== $localeTranslations) {
                if ($options->write) {
                    $this->writeTranslationFile($localeFile, $merged);
                    $this->filesUpdated++;
                    if ($options->output) {
                        $options->output->info("Updated: {$options->locale}/{$file}.php");
                    }
                } else {
                    if ($options->output) {
                        $options->output->line("Would update: {$options->locale}/{$file}.php");
                    }
                }

                $added = $this->countAddedKeys($localeTranslations, $merged);
                $this->keysAdded += $added;
            }
        }
    }

    /**
     * Sync source locale (fallback locale) with missing keys from codebase scan.
     */
    public function syncSourceLocale(array $foundKeys, bool $write, $output = null): void
    {
        $sourceLangPath = lang_path($this->sourceLocale);

        if (! File::exists($sourceLangPath)) {
            File::makeDirectory($sourceLangPath, 0755, true);
        }

        // Group keys by namespace (file)
        $keysByFile = [];

        foreach ($foundKeys as $key => $locations) {
            // Determine which file this key belongs to based on namespace
            $namespace = $this->getNamespaceFromKey($key);
            if (! isset($keysByFile[$namespace])) {
                $keysByFile[$namespace] = [];
            }
            $keysByFile[$namespace][$key] = $locations;
        }

        // Sync each translation file
        foreach ($keysByFile as $filename => $keys) {
            $filePath = "{$sourceLangPath}/{$filename}.php";
            $existingTranslations = File::exists($filePath) ? require $filePath : [];

            // Add missing keys with their usage locations
            $updated = $this->addMissingKeysToSource($existingTranslations, $keys, $filename);

            if ($updated !== $existingTranslations) {
                if ($write) {
                    $this->writeTranslationFile($filePath, $updated);
                    $this->filesUpdated++;
                    if ($output) {
                        $output->info("Updated source locale: {$this->sourceLocale}/{$filename}.php");
                    }
                } else {
                    if ($output) {
                        $output->line("Would update source locale: {$this->sourceLocale}/{$filename}.php");
                    }
                }
            }
        }
    }

    /**
     * Get namespace (filename) from translation key.
     */
    public function getNamespaceFromKey(string $key): string
    {
        // Check if key starts with a known namespace
        foreach ($this->namespaces as $namespace) {
            if (\str_starts_with($key, "{$namespace}.")) {
                return $namespace;
            }
        }

        // Default to extracted file for unknown keys
        return $this->extractedFile;
    }

    /**
     * Add missing keys to source locale translations with usage locations.
     *
     * @param  array<string, mixed>  $translations
     * @param  array<string, array<string, array<int>>>  $keys
     * @return array<string, mixed>
     */
    public function addMissingKeysToSource(array $translations, array $keys, string $filename): array
    {
        $updated = $translations;

        foreach ($keys as $fullKey => $locations) {
            // Remove namespace prefix if present
            $keyWithoutNamespace = \str_starts_with($fullKey, "{$filename}.")
                ? \substr($fullKey, \strlen($filename) + 1)
                : $fullKey;

            if ($keyWithoutNamespace === '' || $keyWithoutNamespace === null) {
                continue;
            }

            // Build location string (can be multiple)
            $locationStrings = [];
            foreach ($locations as $file => $lines) {
                foreach ($lines as $line) {
                    $locationStrings[] = "{$file}:{$line}";
                }
            }
            $locationValue = \implode(', ', $locationStrings);

            // For extracted file, always treat keys as simple (even if they contain dots)
            // For other files, check if this is a namespaced key (has dots in the middle, not just at the end)
            $isSimpleKey = $filename === $this->extractedFile
                || ! \str_contains($keyWithoutNamespace, '.')
                || (\str_ends_with($keyWithoutNamespace, '.') && substr_count($keyWithoutNamespace, '.') === 1);

            // Try automatic resolution for dynamic keys
            if ($this->dynamicKeyResolver->isDynamicKey($fullKey)) {
                $resolved = $this->dynamicKeyResolver->tryAutoResolve($fullKey, $locations);

                if ($resolved !== null && ! empty($resolved)) {
                    // Successfully resolved - expand the key
                    foreach ($resolved as $value) {
                        $expandedKey = $this->dynamicKeyResolver->expandKey($keyWithoutNamespace, $value);
                        $translationValue = $this->dynamicKeyResolver->generateTranslationValue($expandedKey);

                        // Add expanded key to translations
                        if (\str_contains($expandedKey, '.')) {
                            // Nested key
                            $parts = \explode('.', $expandedKey);
                            $current = &$updated;

                            foreach ($parts as $idx => $part) {
                                if ($idx === \count($parts) - 1) {
                                    if (! isset($current[$part])) {
                                        $current[$part] = $translationValue;
                                        $this->keysAdded++;
                                    }
                                } else {
                                    if (! isset($current[$part]) || ! \is_array($current[$part])) {
                                        $current[$part] = [];
                                    }
                                    $current = &$current[$part];
                                }
                            }
                        } else {
                            // Simple key
                            if (! isset($updated[$expandedKey])) {
                                $updated[$expandedKey] = $translationValue;
                                $this->keysAdded++;
                            }
                        }
                    }

                    // Skip adding the dynamic pattern key
                    continue;
                }

                // Auto-resolution failed - use DYNAMIC_KEY marker
                $valueToSet = $this->dynamicKeyResolver->getDynamicKeyValue($fullKey, $locationValue);
            } else {
                $valueToSet = "TRANSLATION_NEEDED: Please see context at {$locationValue}";
            }

            if ($isSimpleKey) {
                // Simple key - set directly
                if (! isset($updated[$keyWithoutNamespace]) || $this->dynamicKeyResolver->isRawLocationValue($updated[$keyWithoutNamespace])) {
                    $updated[$keyWithoutNamespace] = $valueToSet;
                    $this->keysAdded++;
                }
            } else {
                // Navigate/create nested structure for namespaced keys
                $keyParts = \explode('.', $keyWithoutNamespace);
                $current = &$updated;

                foreach ($keyParts as $i => $keyPart) {
                    if (empty($keyPart) && $i < \count($keyParts) - 1) {
                        // Skip empty intermediate keys
                        continue;
                    }

                    if ($i === \count($keyParts) - 1) {
                        // Last key - set the value if missing or if it's a raw location placeholder
                        if (! isset($current[$keyPart]) || $this->dynamicKeyResolver->isRawLocationValue($current[$keyPart])) {
                            $current[$keyPart] = $valueToSet;
                            $this->keysAdded++;
                        }
                    } else {
                        // Intermediate key - ensure array exists
                        if (! isset($current[$keyPart]) || ! \is_array($current[$keyPart])) {
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
     * Merge translations, adding missing keys from source.
     *
     * @return array<string, mixed>
     */
    public function mergeTranslations(MergeOptions $options): array
    {
        $merged = $options->locale;

        foreach ($options->source as $key => $value) {
            $fullKey = $options->prefix ? "{$options->prefix}.{$key}" : $key;

            if (! isset($merged[$key])) {
                // Key missing in locale - set placeholder value
                if (\is_array($value)) {
                    // If source value is an array, recursively merge with empty array
                    $merged[$key] = $this->mergeTranslations(new MergeOptions(
                        source: $value,
                        locale: [],
                        filename: $options->filename,
                        prefix: $fullKey,
                    ));
                } else {
                    // For scalar values, set empty string to allow fallback or manual translation
                    $merged[$key] = '';
                }
            } elseif (\is_array($value) && \is_array($merged[$key])) {
                // Both are arrays, recursively merge
                $merged[$key] = $this->mergeTranslations(new MergeOptions(
                    source: $value,
                    locale: $merged[$key],
                    filename: $options->filename,
                    prefix: $fullKey,
                ));
            } elseif (\is_array($value) && ! \is_array($merged[$key])) {
                // Source is array but locale has scalar - replace with merged array
                $merged[$key] = $this->mergeTranslations(new MergeOptions(
                    source: $value,
                    locale: [],
                    filename: $options->filename,
                    prefix: $fullKey,
                ));
            }
        }

        return $merged;
    }

    /**
     * Count how many keys were added.
     */
    public function countAddedKeys(array $old, array $new): int
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
            } elseif (\is_array($value) && \is_array($old[$key])) {
                $this->countAddedKeysRecursive($old[$key], $value, $count);
            }
        }
    }

    /**
     * Write translation file with proper formatting.
     */
    public function writeTranslationFile(string $filePath, array $translations): void
    {
        $content = "<?php\n\n";

        // Add header for extracted file
        if (\str_contains($filePath, "/{$this->extractedFile}.php")) {
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
            $keyStr = \is_string($key) && \preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)
                ? "'{$key}'"
                : var_export($key, true);

            if (\is_array($value)) {
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
}
