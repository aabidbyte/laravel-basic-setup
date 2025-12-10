<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Check which frontend stack is installed
$isLivewire = class_exists(\Livewire\Volt\Volt::class);
$isInertia = class_exists(\Inertia\Inertia::class);

if ($isLivewire) {
    // Livewire/Volt routes
    Route::view('dashboard', 'dashboard')
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::middleware(['auth'])->group(function () {
        Route::redirect('settings', 'settings/profile');

        \Livewire\Volt\Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
        \Livewire\Volt\Volt::route('settings/password', 'settings.password')->name('user-password.edit');
        \Livewire\Volt\Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

        \Livewire\Volt\Volt::route('settings/two-factor', 'settings.two-factor')
            ->middleware(
                when(
                    Features::canManageTwoFactorAuthentication()
                        && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                    ['password.confirm'],
                    [],
                ),
            )
            ->name('two-factor.show');
    });
} elseif ($isInertia && class_exists(\Inertia\Inertia::class)) {
    // Inertia.js routes (React/Vue)
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard', function () {
            return \Inertia\Inertia::render('Dashboard');
        })->name('dashboard');
    });

    Route::middleware(['auth'])->group(function () {
        Route::redirect('settings', 'settings/profile');

        Route::get('settings/profile', function () {
            return \Inertia\Inertia::render('Settings/Profile');
        })->name('profile.edit');

        Route::get('settings/password', function () {
            return \Inertia\Inertia::render('Settings/Password');
        })->name('user-password.edit');

        Route::get('settings/appearance', function () {
            return \Inertia\Inertia::render('Settings/Appearance');
        })->name('appearance.edit');

        Route::get('settings/two-factor', function () {
            return \Inertia\Inertia::render('Settings/TwoFactor');
        })
            ->middleware(
                when(
                    Features::canManageTwoFactorAuthentication()
                        && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                    ['password.confirm'],
                    [],
                ),
            )
            ->name('two-factor.show');
    });
} else {
    // Fallback to basic Blade views
    Route::view('dashboard', 'dashboard')
        ->middleware(['auth', 'verified'])
        ->name('dashboard');
}
