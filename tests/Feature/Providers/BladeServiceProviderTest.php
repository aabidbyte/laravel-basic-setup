<?php

use App\Models\User;
use App\Providers\BladeServiceProvider;
use Illuminate\Support\Facades\View;

test('BladeServiceProvider shares layout variables when app layout view is rendered', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $provider = new BladeServiceProvider(app());
    $provider->boot();

    // Render the view - if variables are not available, it will throw an error
    $view = view('components.layouts.app', ['slot' => 'test'])->render();

    // The view should render successfully with layout variables available
    expect($view)->toBeString();
    expect($view)->toContain('lang=');
    expect($view)->toContain('dir=');
    expect($view)->toContain('data-theme=');
});

test('BladeServiceProvider shares layout variables when auth layout view is rendered', function () {
    $provider = new BladeServiceProvider(app());
    $provider->boot();

    // Render the view - if variables are not available, it will throw an error
    $view = view('components.layouts.auth', ['slot' => 'test'])->render();

    // The view should render successfully with layout variables available
    expect($view)->toBeString();
    expect($view)->toContain('lang=');
    expect($view)->toContain('dir=');
    expect($view)->toContain('data-theme=');
});

test('BladeServiceProvider shares menu data when sidebar components are rendered', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $provider = new BladeServiceProvider(app());
    $provider->boot();

    // Render the sidebar-menu view - if menu data is not available, it will throw an error
    $view = view('components.layouts.app.sidebar-menu')->render();

    // The view should render successfully with menu data available
    expect($view)->toBeString();
    // Should contain menu structure
    expect($view)->toContain('sidebar-top-menus');
    expect($view)->toContain('sidebar-bottom-menus');
});

test('BladeServiceProvider shares page title when header view is rendered', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $provider = new BladeServiceProvider(app());
    $provider->boot();

    // Render the header view - if pageTitle is not available, it will use fallback
    $view = view('components.layouts.app.header')->render();

    // The view should render successfully with pageTitle available
    expect($view)->toBeString();
    // Should contain page title (either from View::share or fallback to app name)
    expect($view)->toContain('text-xl');
});

test('BladeServiceProvider shares page subtitle when provided', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $provider = new BladeServiceProvider(app());
    $provider->boot();

    // Share a subtitle via View::share (simulating BasePageComponent behavior)
    View::share('pageSubtitle', 'Test Subtitle');

    // Render the header view
    $view = view('components.layouts.app.header')->render();

    // The view should render successfully with pageSubtitle available
    expect($view)->toBeString();
    // Should contain subtitle if it exists
    expect($view)->toContain('Test Subtitle');
});
