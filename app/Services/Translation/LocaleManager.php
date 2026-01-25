<?php

declare(strict_types=1);

namespace App\Services\Translation;

use Illuminate\Support\Facades\File;

class LocaleManager
{
    protected string $defaultLocale;

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

    public function setConfiguration(string $defaultLocale, array $supportedLocales, array $namespaces, string $extractedFile): void
    {
        $this->defaultLocale = $defaultLocale;
        $this->supportedLocales = $supportedLocales;
        $this->namespaces = $namespaces;
        $this->extractedFile = $extractedFile;
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
     * Auto-discover namespaces from default locale files.
     *
     * @return array<string>
     */
    public function discoverNamespaces(): array
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
     * Sync all locales with the default locale as source of truth.
     */
    public function syncLocales(bool $write = false, bool $allowJson = false, $output = null): void
    {
        $defaultLangPath = lang_path($this->defaultLocale);

        if (! File::exists($defaultLangPath)) {
            if ($output) {
                $output->error("Default locale directory not found: {$defaultLangPath}");
            }

            return;
        }

        // Get all translation files from default locale
        $defaultFiles = File::files($defaultLangPath);
        $translationFiles = [];

        foreach ($defaultFiles as $file) {
            $filename = $file->getFilenameWithoutExtension();
            if ($filename === 'extracted' && ! $allowJson) {
                continue;
            }
            $translationFiles[] = $filename;
        }

        // Sync each locale
        foreach ($this->supportedLocales as $locale) {
            if ($locale === $this->defaultLocale) {
                continue; // Skip default locale
            }

            $this->syncLocale($locale, $translationFiles, $write, $output);
        }
    }

    /**
     * Sync a specific locale with the default locale.
     */
    public function syncLocale(string $locale, array $translationFiles, bool $write, $output = null): void
    {
        $localePath = lang_path($locale);
        $defaultPath = lang_path($this->defaultLocale);

        if (! File::exists($localePath)) {
            File::makeDirectory($localePath, 0755, true);
            if ($output) {
                $output->info("Created locale directory: {$locale}");
            }
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
                if ($write) {
                    $this->writeTranslationFile($localeFile, $merged);
                    $this->filesUpdated++;
                    if ($output) {
                        $output->info("Updated: {$locale}/{$file}.php");
                    }
                } else {
                    if ($output) {
                        $output->line("Would update: {$locale}/{$file}.php");
                    }
                }

                $added = $this->countAddedKeys($localeTranslations, $merged);
                $this->keysAdded += $added;
            }
        }
    }

    /**
     * Sync default locale with missing keys from codebase scan.
     */
    public function syncDefaultLocale(array $foundKeys, bool $write, $output = null): void
    {
        $defaultLangPath = lang_path($this->defaultLocale);

        if (! File::exists($defaultLangPath)) {
            File::makeDirectory($defaultLangPath, 0755, true);
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
            $filePath = "{$defaultLangPath}/{$filename}.php";
            $existingTranslations = File::exists($filePath) ? require $filePath : [];

            // Add missing keys with their usage locations
            $updated = $this->addMissingKeysToDefault($existingTranslations, $keys, $filename);

            if ($updated !== $existingTranslations) {
                if ($write) {
                    $this->writeTranslationFile($filePath, $updated);
                    $this->filesUpdated++;
                    if ($output) {
                        $output->info("Updated default locale: {$this->defaultLocale}/{$filename}.php");
                    }
                } else {
                    if ($output) {
                        $output->line("Would update default locale: {$this->defaultLocale}/{$filename}.php");
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
    public function addMissingKeysToDefault(array $translations, array $keys, string $filename): array
    {
        $updated = $translations;

        foreach ($keys as $fullKey => $locations) {
            // Remove namespace prefix if present
            $keyWithoutNamespace = str_starts_with($fullKey, "{$filename}.")
                ? substr($fullKey, strlen($filename) + 1)
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
            $locationValue = implode(', ', $locationStrings);

            // For extracted file, always treat keys as simple (even if they contain dots)
            // For other files, check if this is a namespaced key (has dots in the middle, not just at the end)
            $isSimpleKey = $filename === $this->extractedFile
                || ! str_contains($keyWithoutNamespace, '.')
                || (str_ends_with($keyWithoutNamespace, '.') && substr_count($keyWithoutNamespace, '.') === 1);

            // Try automatic resolution for dynamic keys
            if ($this->dynamicKeyResolver->isDynamicKey($fullKey)) {
                $resolved = $this->dynamicKeyResolver->tryAutoResolve($fullKey, $locations);

                if ($resolved !== null && ! empty($resolved)) {
                    // Successfully resolved - expand the key
                    foreach ($resolved as $value) {
                        $expandedKey = $this->dynamicKeyResolver->expandKey($keyWithoutNamespace, $value);
                        $translationValue = $this->dynamicKeyResolver->generateTranslationValue($expandedKey);

                        // Add expanded key to translations
                        if (str_contains($expandedKey, '.')) {
                            // Nested key
                            $parts = explode('.', $expandedKey);
                            $current = &$updated;

                            foreach ($parts as $idx => $part) {
                                if ($idx === count($parts) - 1) {
                                    if (! isset($current[$part])) {
                                        $current[$part] = $translationValue;
                                        $this->keysAdded++;
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
                $keyParts = explode('.', $keyWithoutNamespace);
                $current = &$updated;

                foreach ($keyParts as $i => $keyPart) {
                    if (empty($keyPart) && $i < count($keyParts) - 1) {
                        // Skip empty intermediate keys
                        continue;
                    }

                    if ($i === count($keyParts) - 1) {
                        // Last key - set the value if missing or if it's a raw location placeholder
                        if (! isset($current[$keyPart]) || $this->dynamicKeyResolver->isRawLocationValue($current[$keyPart])) {
                            $current[$keyPart] = $valueToSet;
                            $this->keysAdded++;
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
     * Merge translations, adding missing keys from default.
     *
     * @param  array<string, mixed>  $default
     * @param  array<string, mixed>  $locale
     * @return array<string, mixed>
     */
    public function mergeTranslations(array $default, array $locale, string $filename = '', string $prefix = ''): array
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
            } elseif (is_array($value) && is_array($old[$key])) {
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
}
