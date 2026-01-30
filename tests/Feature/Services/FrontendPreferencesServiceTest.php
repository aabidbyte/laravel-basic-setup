<?php

declare(strict_types=1);

use App\Constants\Preferences\FrontendPreferences;
use App\Models\User;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
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

test('authenticated user loads preferences from database and syncs to session', function () {
    $user = User::factory()->create([
        'frontend_preferences' => [
            'theme' => 'dark',
            'locale' => 'fr_FR',
            'timezone' => 'Europe/Paris',
        ],
    ]);

    $this->actingAs($user);

    $service = app(FrontendPreferencesService::class);

    // First call should sync from DB to session
    expect($service->getTheme())->toBe('dark')
        ->and($service->getLocale())->toBe('fr_FR')
        ->and($service->getTimezone())->toBe('Europe/Paris');

    // Verify preferences are synced to session
    $sessionPrefs = Session::get(FrontendPreferences::SESSION_KEY);
    expect($sessionPrefs['theme'])->toBe('dark')
        ->and($sessionPrefs['locale'])->toBe('fr_FR')
        ->and($sessionPrefs['timezone'])->toBe('Europe/Paris');
});

test('authenticated user persists preferences to database first, then session', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $service = app(FrontendPreferencesService::class);

    $service->setTheme('dark');
    $service->setLocale('fr_FR');
    $service->setTimezone('Europe/Paris');

    // Verify stored in database
    $user->refresh();
    expect($user->frontend_preferences['theme'])->toBe('dark')
        ->and($user->frontend_preferences['locale'])->toBe('fr_FR')
        ->and($user->frontend_preferences['timezone'])->toBe('Europe/Paris');

    // Verify also stored in session (single source of truth)
    $sessionPrefs = Session::get(FrontendPreferences::SESSION_KEY);
    expect($sessionPrefs['theme'])->toBe('dark')
        ->and($sessionPrefs['locale'])->toBe('fr_FR')
        ->and($sessionPrefs['timezone'])->toBe('Europe/Paris');
});

test('authenticated user preferences sync from database to session on first read', function () {
    $user = User::factory()->create([
        'frontend_preferences' => [
            'theme' => 'dark',
        ],
    ]);

    $this->actingAs($user);

    $service = app(FrontendPreferencesService::class);

    // First call should sync from DB to session
    expect($service->getTheme())->toBe('dark');

    // Verify synced to session
    $sessionPrefs = Session::get(FrontendPreferences::SESSION_KEY);
    expect($sessionPrefs['theme'])->toBe('dark');

    // Clear session to simulate new request
    Session::forget(FrontendPreferences::SESSION_KEY);

    // Should reload from DB and sync to session again
    expect($service->getTheme())->toBe('dark');

    // Verify synced to session again
    $sessionPrefs = Session::get(FrontendPreferences::SESSION_KEY);
    expect($sessionPrefs['theme'])->toBe('dark');
});

test('refresh reloads preferences from database and syncs to session', function () {
    $user = User::factory()->create([
        'frontend_preferences' => [
            'theme' => 'light',
        ],
    ]);

    $this->actingAs($user);

    $service = app(FrontendPreferencesService::class);

    // Load initial preferences (syncs from DB to session)
    expect($service->getTheme())->toBe('light');

    // Update in database directly using fresh query
    User::where('id', $user->id)->update([
        'frontend_preferences' => \json_encode([
            'theme' => 'dark',
        ]),
    ]);

    // Should still return session value (single source of truth)
    expect($service->getTheme())->toBe('light');

    // Refresh should reload from DB and sync to session
    $service->refresh();

    expect($service->getTheme())->toBe('dark');

    // Verify synced to session
    $sessionPrefs = Session::get(FrontendPreferences::SESSION_KEY);
    expect($sessionPrefs['theme'])->toBe('dark');
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

test('default theme is light', function () {
    $service = app(FrontendPreferencesService::class);

    // Default theme should be "light"
    $theme = $service->getTheme();
    expect($theme)->toBe('light');
});

test('can set theme to light or dark', function () {
    $service = app(FrontendPreferencesService::class);

    $service->setTheme('light');
    expect($service->getTheme())->toBe('light');

    $service->setTheme('dark');
    expect($service->getTheme())->toBe('dark');
});

test('session is single source of truth for reads', function () {
    $user = User::factory()->create([
        'frontend_preferences' => [
            'theme' => 'light',
        ],
    ]);

    $this->actingAs($user);

    $service = app(FrontendPreferencesService::class);

    // First read syncs from DB to session
    expect($service->getTheme())->toBe('light');

    // Manually update session (simulating session-only change)
    Session::put(FrontendPreferences::SESSION_KEY, [
        'theme' => 'dark',
    ]);

    // Should read from session (single source of truth)
    expect($service->getTheme())->toBe('dark');

    // Even though DB still has 'light', session value is used
    $user->refresh();
    expect($user->frontend_preferences['theme'])->toBe('light');
});

test('authenticated user updates go to database first, then session', function () {
    $user = User::factory()->create([
        'frontend_preferences' => [
            'theme' => 'light',
        ],
    ]);

    $this->actingAs($user);

    $service = app(FrontendPreferencesService::class);

    // Update preference
    $service->setTheme('dark');

    // Verify DB updated first
    $user->refresh();
    expect($user->frontend_preferences['theme'])->toBe('dark');

    // Verify session also updated (single source of truth)
    $sessionPrefs = Session::get(FrontendPreferences::SESSION_KEY);
    expect($sessionPrefs['theme'])->toBe('dark');
});

test('preferences are synced from database to session on login', function () {
    $user = User::factory()->create([
        'frontend_preferences' => [
            'theme' => 'dark',
            'locale' => 'fr_FR',
            'timezone' => 'Europe/Paris',
        ],
    ]);

    // Clear session to simulate fresh login
    Session::forget(FrontendPreferences::SESSION_KEY);

    // Simulate login by firing the Login event
    Event::dispatch(new Login('web', $user, false));

    // Verify preferences are synced to session
    $sessionPrefs = Session::get(FrontendPreferences::SESSION_KEY);
    expect($sessionPrefs['theme'])->toBe('dark')
        ->and($sessionPrefs['locale'])->toBe('fr_FR')
        ->and($sessionPrefs['timezone'])->toBe('Europe/Paris');

    // Verify service reads from session (single source of truth)
    $service = app(FrontendPreferencesService::class);
    expect($service->getTheme())->toBe('dark')
        ->and($service->getLocale())->toBe('fr_FR')
        ->and($service->getTimezone())->toBe('Europe/Paris');
});
