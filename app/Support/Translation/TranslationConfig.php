<?php

declare(strict_types=1);

namespace App\Support\Translation;

/**
 * Data Object for Translation configuration.
 */
readonly class TranslationConfig
{
    /**
     * Create a new TranslationConfig instance.
     *
     * @param  string  $sourceLocale  The source locale (e.g., 'en_US')
     * @param  array<string>  $supportedLocales  List of supported locales
     * @param  array<string>  $namespaces  List of namespaces (files)
     * @param  string  $extractedFile  Filename for extracted keys
     */
    public function __construct(
        public string $sourceLocale,
        public array $supportedLocales,
        public array $namespaces,
        public string $extractedFile,
    ) {}
}
