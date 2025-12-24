<?php

declare(strict_types=1);

use App\Constants\Preferences\FrontendPreferences;
use App\Models\User;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can update theme preference', function () {
    $response = $this->post(route('preferences.theme'), [
        'theme' => 'dark',
    ]);

    $response->assertRedirect();
    $preferences = app(FrontendPreferencesService::class);
    expect($preferences->getTheme())->toBe('dark');
});

test('authenticated user can update theme preference', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('preferences.theme'), [
        'theme' => 'dark',
    ]);

    $response->assertRedirect();
    $user->refresh();
    expect($user->frontend_preferences['theme'])->toBe('dark');
});

test('theme update validates theme value', function () {
    $response = $this->post(route('preferences.theme'), [
        'theme' => 'invalid_theme',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('theme');
    $preferences = app(FrontendPreferencesService::class);
    expect($preferences->getTheme())->toBe(FrontendPreferences::DEFAULT_THEME);
});

test('guest can update locale preference', function () {
    $response = $this->post(route('preferences.locale'), [
        'locale' => 'fr_FR',
    ]);

    $response->assertRedirect();
    $preferences = app(FrontendPreferencesService::class);
    expect($preferences->getLocale())->toBe('fr_FR');
});

test('authenticated user can update locale preference', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('preferences.locale'), [
        'locale' => 'fr_FR',
    ]);

    $response->assertRedirect();
    $user->refresh();
    expect($user->frontend_preferences['locale'])->toBe('fr_FR');
});

test('locale update validates locale value', function () {
    $response = $this->post(route('preferences.locale'), [
        'locale' => 'invalid_locale',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('locale');
    $preferences = app(FrontendPreferencesService::class);
    expect($preferences->getLocale())->toBe(FrontendPreferences::DEFAULT_LOCALE);
});

test('theme update persists for authenticated user', function () {
    $user = User::factory()->create([
        'frontend_preferences' => ['theme' => 'light'],
    ]);

    $this->actingAs($user);

    $response = $this->post(route('preferences.theme'), [
        'theme' => 'dark',
    ]);

    $response->assertRedirect();
    $user->refresh();
    expect($user->frontend_preferences['theme'])->toBe('dark');
});

test('locale update persists for authenticated user', function () {
    $user = User::factory()->create([
        'frontend_preferences' => ['locale' => 'en_US'],
    ]);

    $this->actingAs($user);

    $response = $this->post(route('preferences.locale'), [
        'locale' => 'fr_FR',
    ]);

    $response->assertRedirect();
    $user->refresh();
    expect($user->frontend_preferences['locale'])->toBe('fr_FR');
});
