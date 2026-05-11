<?php

use App\Models\User;
use App\Services\FrontendPreferences\FrontendPreferencesService;
use Livewire\Livewire;
use Tests\Fixtures\DataTables\TestDatatableForPreferences;

it('saves and loads per page preference', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // 1. Initial State: Default perPage should be 12 (as per recent change)
    Livewire::test(TestDatatableForPreferences::class)
        ->assertSet('perPage', 12);

    // 2. Change perPage to 200
    Livewire::test(TestDatatableForPreferences::class)
        ->set('perPage', 200)
        ->assertSet('perPage', 200);

    // Verify it was saved to the service/session
    $prefsService = app(FrontendPreferencesService::class);
    $identifier = TestDatatableForPreferences::class;
    $savedPrefs = $prefsService->getDatatablePreferences($identifier);

    expect($savedPrefs['perPage'] ?? null)->toBe(200);

    // 3. Simulate a fresh page load (new component instance)
    // The mount method should load the preference from the service
    Livewire::test(TestDatatableForPreferences::class)
        ->assertSet('perPage', 200);

    // 4. Simulate a page load with a DIFFERENT query param (e.g., search)
    // This makes loadQueryStringParameters find 'search', but perPage should still load from prefs (200), not default (12)
    Livewire::withQueryParams(['search' => 'foo'])
        ->test(TestDatatableForPreferences::class)
        ->assertSet('search', 'foo')
        ->assertSet('perPage', 200);
});
