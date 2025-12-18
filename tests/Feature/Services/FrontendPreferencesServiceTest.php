<?php

declare(strict_types=1);

use App\Constants\FrontendPreferences;
use App\Models\User;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

beforeEach(function () {
    Session::start();
});

test('guest can get default preferences', function () {
    $service = app(FrontendPreferencesService::class);

    expect($service->getLocale())->toBe(FrontendPreferences::DEFAULT_LOCALE)
        ->and($service->getTheme())->toBe(FrontendPreferences::DEFAULT_THEME)
        ->and($service->getTimezone())->toBe(FrontendPreferences::DEFAULT_TIMEZONE);
});

test('guest can set and get preferences in session', function () {
    $service = app(FrontendPreferencesService::class);

    $service->setTheme('dark');
    $service->setLocale('fr_FR');
    $service->setTimezone('Europe/Paris');

    expect($service->getTheme())->toBe('dark')
        ->and($service->getLocale())->toBe('fr_FR')
        ->and($service->getTimezone())->toBe('Europe/Paris');

    // Verify stored in session
    $sessionPrefs = Session::get(FrontendPreferences::SESSION_KEY);
    expect($sessionPrefs['theme'])->toBe('dark')
        ->and($sessionPrefs['locale'])->toBe('fr_FR')
        ->and($sessionPrefs['timezone'])->toBe('Europe/Paris');
});

test('authenticated user loads preferences from database', function () {
    $user = User::factory()->create([
        'frontend_preferences' => [
            'theme' => 'dark',
            'locale' => 'fr_FR',
            'timezone' => 'Europe/Paris',
        ],
    ]);

    $this->actingAs($user);

    $service = app(FrontendPreferencesService::class);

    expect($service->getTheme())->toBe('dark')
        ->and($service->getLocale())->toBe('fr_FR')
        ->and($service->getTimezone())->toBe('Europe/Paris');
});

test('authenticated user persists preferences to database', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $service = app(FrontendPreferencesService::class);

    $service->setTheme('dark');
    $service->setLocale('fr_FR');
    $service->setTimezone('Europe/Paris');

    $user->refresh();

    expect($user->frontend_preferences['theme'])->toBe('dark')
        ->and($user->frontend_preferences['locale'])->toBe('fr_FR')
        ->and($user->frontend_preferences['timezone'])->toBe('Europe/Paris');
});

test('authenticated user preferences are cached in session', function () {
    $user = User::factory()->create([
        'frontend_preferences' => [
            'theme' => 'dark',
        ],
    ]);

    $this->actingAs($user);

    $service = app(FrontendPreferencesService::class);

    // First call loads from DB and caches
    $service->getTheme();

    // Clear session to simulate new request
    Session::forget(FrontendPreferences::SESSION_KEY);

    // Should still work because it reloads from DB
    expect($service->getTheme())->toBe('dark');
});

test('refresh reloads preferences from persistent store', function () {
    $user = User::factory()->create([
        'frontend_preferences' => [
            'theme' => 'light',
        ],
    ]);

    $this->actingAs($user);

    $service = app(FrontendPreferencesService::class);

    // Load initial preferences
    expect($service->getTheme())->toBe('light');

    // Update in database directly using fresh query
    User::where('id', $user->id)->update([
        'frontend_preferences' => json_encode([
            'theme' => 'dark',
        ]),
    ]);

    // Should still return cached value
    expect($service->getTheme())->toBe('light');

    // Refresh should reload from DB
    $service->refresh();

    expect($service->getTheme())->toBe('dark');
});

test('setTheme validates theme value', function () {
    $service = app(FrontendPreferencesService::class);

    $service->setTheme('invalid');
    expect($service->getTheme())->toBe(FrontendPreferences::DEFAULT_THEME);

    $service->setTheme('dark');
    expect($service->getTheme())->toBe('dark');
});

test('setLocale validates locale using I18nService', function () {
    $service = app(FrontendPreferencesService::class);

    $service->setLocale('invalid_locale');
    expect($service->getLocale())->toBe(FrontendPreferences::DEFAULT_LOCALE);

    $service->setLocale('fr_FR');
    expect($service->getLocale())->toBe('fr_FR');
});

test('setTimezone validates timezone', function () {
    $service = app(FrontendPreferencesService::class);

    $service->setTimezone('Invalid/Timezone');
    expect($service->getTimezone())->toBe(FrontendPreferences::DEFAULT_TIMEZONE);

    $service->setTimezone('America/New_York');
    expect($service->getTimezone())->toBe('America/New_York');
});

test('get method returns default if key does not exist', function () {
    $service = app(FrontendPreferencesService::class);

    expect($service->get('non_existent_key', 'default_value'))->toBe('default_value');
});

test('setMany sets multiple preferences at once', function () {
    $service = app(FrontendPreferencesService::class);

    $service->setMany([
        'theme' => 'dark',
        'locale' => 'fr_FR',
        'timezone' => 'Europe/Paris',
    ]);

    expect($service->getTheme())->toBe('dark')
        ->and($service->getLocale())->toBe('fr_FR')
        ->and($service->getTimezone())->toBe('Europe/Paris');
});

test('all method returns all preferences', function () {
    $service = app(FrontendPreferencesService::class);

    $service->setTheme('dark');
    $service->setLocale('fr_FR');

    $all = $service->all();

    expect($all)->toHaveKeys(['theme', 'locale', 'timezone'])
        ->and($all['theme'])->toBe('dark')
        ->and($all['locale'])->toBe('fr_FR');
});

test('detects browser language from Accept-Language header on first visit', function () {
    $request = request();
    $request->headers->set('Accept-Language', 'fr-FR,fr;q=0.9,en;q=0.8');

    $service = app(FrontendPreferencesService::class);

    // On first visit, should detect French
    $locale = $service->getLocale($request);
    expect($locale)->toBe('fr_FR');

    // Verify it's saved
    $sessionPrefs = Session::get(FrontendPreferences::SESSION_KEY);
    expect($sessionPrefs['locale'])->toBe('fr_FR');
});

test('detects browser language with language code only', function () {
    $request = request();
    $request->headers->set('Accept-Language', 'fr,en;q=0.9');

    $service = app(FrontendPreferencesService::class);

    // Should match fr_FR from supported locales
    $locale = $service->getLocale($request);
    expect($locale)->toBe('fr_FR');
});

test('falls back to default locale if browser language not supported', function () {
    $request = request();
    $request->headers->set('Accept-Language', 'de-DE,de;q=0.9');

    $service = app(FrontendPreferencesService::class);

    // German not supported, should use default
    $locale = $service->getLocale($request);
    expect($locale)->toBe(FrontendPreferences::DEFAULT_LOCALE);
});

test('does not detect browser language if preference already set', function () {
    $service = app(FrontendPreferencesService::class);

    // Set a preference first
    $service->setLocale('fr_FR');

    // Try to detect with English browser
    $request = request();
    $request->headers->set('Accept-Language', 'en-US,en;q=0.9');

    // Should keep existing preference
    $locale = $service->getLocale($request);
    expect($locale)->toBe('fr_FR');
});

test('detects theme from cookie on first visit', function () {
    $request = request();
    $request->cookies->set('_preferred_theme', 'dark');

    $service = app(FrontendPreferencesService::class);

    // On first visit, should detect dark theme from cookie
    $theme = $service->getTheme($request);
    expect($theme)->toBe('dark');

    // Verify it's saved
    $sessionPrefs = Session::get(FrontendPreferences::SESSION_KEY);
    expect($sessionPrefs['theme'])->toBe('dark');
});

test('ignores invalid theme from cookie', function () {
    $request = request();
    $request->cookies->set('_preferred_theme', 'invalid_theme');

    $service = app(FrontendPreferencesService::class);

    // Should use default theme if cookie value is invalid
    $theme = $service->getTheme($request);
    expect($theme)->toBe(FrontendPreferences::DEFAULT_THEME);
});

test('does not detect theme from cookie if preference already set', function () {
    $service = app(FrontendPreferencesService::class);

    // Set a preference first
    $service->setTheme('light');

    // Try to detect with dark theme cookie
    $request = request();
    $request->cookies->set('_preferred_theme', 'dark');

    // Should keep existing preference
    $theme = $service->getTheme($request);
    expect($theme)->toBe('light');
});
