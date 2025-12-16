<?php

use App\Services\I18nService;
use Illuminate\Support\Facades\App;

test('getLocale returns current locale', function () {
    App::setLocale('fr_FR');
    $service = app(I18nService::class);

    expect($service->getLocale())->toBe('fr_FR');
});

test('getHtmlLangAttribute converts locale format correctly', function () {
    App::setLocale('en_US');
    $service = app(I18nService::class);

    expect($service->getHtmlLangAttribute())->toBe('en-US');
});

test('getHtmlDirAttribute returns ltr for en_US', function () {
    App::setLocale('en_US');
    $service = app(I18nService::class);

    expect($service->getHtmlDirAttribute())->toBe('ltr');
});

test('getHtmlDirAttribute returns ltr for fr_FR', function () {
    App::setLocale('fr_FR');
    $service = app(I18nService::class);

    expect($service->getHtmlDirAttribute())->toBe('ltr');
});

test('getSupportedLocales returns all supported locales', function () {
    $service = app(I18nService::class);
    $locales = $service->getSupportedLocales();

    expect($locales)->toHaveKeys(['en_US', 'fr_FR']);
});

test('getDefaultLocale returns default locale', function () {
    $service = app(I18nService::class);

    expect($service->getDefaultLocale())->toBe('en_US');
});

test('getFallbackLocale returns fallback locale', function () {
    $service = app(I18nService::class);

    expect($service->getFallbackLocale())->toBe('en_US');
});

test('getLocaleMetadata returns metadata for supported locale', function () {
    $service = app(I18nService::class);
    $metadata = $service->getLocaleMetadata('en_US');

    expect($metadata)->toBeArray();
    expect($metadata)->toHaveKey('native_name');
    expect($metadata)->toHaveKey('date_format');
    expect($metadata)->toHaveKey('currency');
});

test('getLocaleMetadata returns null for unsupported locale', function () {
    $service = app(I18nService::class);
    $metadata = $service->getLocaleMetadata('unsupported_locale');

    expect($metadata)->toBeNull();
});

test('getLocaleMetadata uses current locale when null is passed', function () {
    App::setLocale('en_US');
    $service = app(I18nService::class);
    $metadata = $service->getLocaleMetadata(null);

    expect($metadata)->toBeArray();
    expect($metadata)->toHaveKey('native_name');
});

test('getCurrentLocaleMetadata returns metadata for current locale', function () {
    App::setLocale('en_US');
    $service = app(I18nService::class);
    $metadata = $service->getCurrentLocaleMetadata();

    expect($metadata)->toBeArray();
    expect($metadata)->toHaveKey('native_name');
});

test('isLocaleSupported returns true for supported locale', function () {
    $service = app(I18nService::class);

    expect($service->isLocaleSupported('en_US'))->toBeTrue();
    expect($service->isLocaleSupported('fr_FR'))->toBeTrue();
});

test('isLocaleSupported returns false for unsupported locale', function () {
    $service = app(I18nService::class);

    expect($service->isLocaleSupported('unsupported_locale'))->toBeFalse();
});

test('getValidLocale returns locale if supported', function () {
    $service = app(I18nService::class);

    expect($service->getValidLocale('en_US'))->toBe('en_US');
});

test('getValidLocale returns default locale if not supported', function () {
    $service = app(I18nService::class);

    expect($service->getValidLocale('unsupported_locale'))->toBe('en_US');
});

test('getValidLocale uses current locale when null is passed', function () {
    App::setLocale('fr_FR');
    $service = app(I18nService::class);

    expect($service->getValidLocale(null))->toBe('fr_FR');
});

test('isRtl returns false for ltr locales', function () {
    $service = app(I18nService::class);

    expect($service->isRtl('en_US'))->toBeFalse();
    expect($service->isRtl('fr_FR'))->toBeFalse();
});

test('isRtl uses current locale when null is passed', function () {
    App::setLocale('en_US');
    $service = app(I18nService::class);

    expect($service->isRtl(null))->toBeFalse();
});
