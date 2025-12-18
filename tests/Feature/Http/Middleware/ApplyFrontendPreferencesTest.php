<?php

declare(strict_types=1);

use App\Constants\FrontendPreferences;
use App\Models\User;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('middleware applies locale preference', function () {
    $user = User::factory()->create([
        'frontend_preferences' => [
            'locale' => 'fr_FR',
        ],
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', absolute: false));

    $response->assertSuccessful();
    expect(App::getLocale())->toBe('fr_FR');
});

test('middleware does not change system timezone', function () {
    $user = User::factory()->create([
        'frontend_preferences' => [
            'timezone' => 'America/New_York',
        ],
    ]);

    $this->actingAs($user);

    $originalTimezone = date_default_timezone_get();

    $response = $this->get(route('dashboard', absolute: false));

    $response->assertSuccessful();
    // Timezone preference is for display only, not applied globally
    // System timezone remains as configured in config/app.php
    expect(date_default_timezone_get())->toBe($originalTimezone);
});

test('middleware uses default locale when no preference set', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('dashboard', absolute: false));

    $response->assertSuccessful();
    expect(App::getLocale())->toBe(FrontendPreferences::DEFAULT_LOCALE);
});

test('middleware preserves system timezone', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $originalTimezone = date_default_timezone_get();

    $response = $this->get(route('dashboard', absolute: false));

    $response->assertSuccessful();
    // System timezone remains unchanged (from config/app.php)
    expect(date_default_timezone_get())->toBe($originalTimezone);
});

test('middleware works for guest users', function () {
    $preferences = app(FrontendPreferencesService::class);
    $preferences->setLocale('fr_FR');

    // Create a test route in web middleware group to verify middleware applies preferences
    Route::middleware('web')->get('/test-guest-preferences', function () {
        return response()->json([
            'locale' => app()->getLocale(),
        ]);
    });

    $response = $this->get('/test-guest-preferences');

    $response->assertSuccessful();
    expect($response->json('locale'))->toBe('fr_FR');
});
