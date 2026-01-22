<?php

declare(strict_types=1);

namespace App\Services\Translation;

use Illuminate\Support\Facades\File;

class TranslationPruner
{
    protected array $supportedLocales;
    protected array $protectedFiles;
    protected array $namespaces;
    
    // Statistics
    protected int $keysPruned = 0;

    protected LocaleManager $localeManager;
    protected TranslationScanner $scanner;

    public function __construct(LocaleManager $localeManager, TranslationScanner $scanner)
    {
        $this->localeManager = $localeManager;
        $this->scanner = $scanner;
    }

    public function setConfiguration(array $supportedLocales, array $protectedFiles, array $namespaces): void
    {
        $this->supportedLocales = $supportedLocales;
        $this->protectedFiles = $protectedFiles;
        $this->namespaces = $namespaces;
    }

    public function getKeysPruned(): int
    {
        return $this->keysPruned;
    }
    
    public function resetStats(): void
    {
        $this->keysPruned = 0;
    }

    /**
     * Prune unused translation keys.
     */
    public function pruneUnusedKeys(bool $write, bool $pruneAll, $output = null): void
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
                // Note: namespaces check needs configured namespaces.
                if (! $pruneAll && in_array($filename, $this->namespaces, true)) {
                    continue;
                }

                $this->pruneFile($locale, $filename, $write, $output);
            }
        }
    }

    /**
     * Prune unused keys from a specific file.
     */
    public function pruneFile(string $locale, string $filename, bool $write, $output = null): void
    {
        $filePath = lang_path("{$locale}/{$filename}.php");
        if (! File::exists($filePath)) {
            return;
        }

        $translations = require $filePath;
        $pruned = $this->pruneTranslations($translations, $filename);

        if ($pruned !== $translations) {
            $prunedCount = $this->countPrunedKeys($translations, $pruned);
            $this->keysPruned += $prunedCount;

            if ($write) {
                $this->localeManager->writeTranslationFile($filePath, $pruned);
                if ($output) $output->info("Pruned {$prunedCount} keys from {$locale}/{$filename}.php");
            } else {
                if ($output) $output->line("Would prune {$prunedCount} keys from {$locale}/{$filename}.php");
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
        // Check if the full key is found in scanner results
        $foundKeys = $this->scanner->getFoundKeys();
        
        if (isset($foundKeys[$fullKey])) {
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
}
