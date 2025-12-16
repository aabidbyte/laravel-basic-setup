<?php

declare(strict_types=1);

namespace App\Services;

class I18nService
{
    /**
     * Get the current locale.
     */
    public function getLocale(): string
    {
        return app()->getLocale();
    }

    /**
     * Get the HTML lang attribute value.
     * Converts locale format (e.g., 'en_US') to HTML lang format (e.g., 'en-US').
     */
    public function getHtmlLangAttribute(): string
    {
        return str_replace('_', '-', $this->getLocale());
    }

    /**
     * Get the HTML dir attribute value (ltr or rtl).
     */
    public function getHtmlDirAttribute(): string
    {
        $locale = $this->getLocale();
        $localeMetadata = $this->getLocaleMetadata($locale);

        return $localeMetadata['direction'] ?? 'ltr';
    }

    /**
     * Get locale metadata for the current locale.
     *
     * @return array<string, mixed>|null
     */
    public function getCurrentLocaleMetadata(): ?array
    {
        return $this->getLocaleMetadata($this->getLocale());
    }

    /**
     * Get locale metadata for a specific locale.
     *
     * @return array<string, mixed>|null
     */
    public function getLocaleMetadata(?string $locale = null): ?array
    {
        $locale = $locale ?? $this->getLocale();
        $supportedLocales = $this->getSupportedLocales();

        return $supportedLocales[$locale] ?? null;
    }

    /**
     * Get all supported locales.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getSupportedLocales(): array
    {
        return config('i18n.supported_locales', []);
    }

    /**
     * Check if a locale is RTL.
     */
    public function isRtl(?string $locale = null): bool
    {
        $locale = $locale ?? $this->getLocale();
        $localeMetadata = $this->getLocaleMetadata($locale);

        return ($localeMetadata['direction'] ?? 'ltr') === 'rtl';
    }

    /**
     * Check if a locale is supported.
     */
    public function isLocaleSupported(string $locale): bool
    {
        $supportedLocales = $this->getSupportedLocales();

        return isset($supportedLocales[$locale]);
    }

    /**
     * Get a valid locale (current, fallback to default if not supported).
     */
    public function getValidLocale(?string $locale = null): string
    {
        $locale = $locale ?? $this->getLocale();

        if ($this->isLocaleSupported($locale)) {
            return $locale;
        }

        return $this->getDefaultLocale();
    }

    /**
     * Get the default locale.
     */
    public function getDefaultLocale(): string
    {
        return config('i18n.default_locale', 'en_US');
    }

    /**
     * Get the fallback locale.
     */
    public function getFallbackLocale(): string
    {
        return config('i18n.fallback_locale', 'en_US');
    }
}
