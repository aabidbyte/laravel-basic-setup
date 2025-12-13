<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

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
