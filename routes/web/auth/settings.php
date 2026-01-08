<?php

use App\Constants\Auth\Permissions;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Settings routes - redirect to new consolidated account page
Route::redirect('settings', 'settings/account');

// New consolidated settings pages
Route::livewire('settings/account', 'pages::settings.account')
    ->name('settings.account');

Route::livewire('settings/security', 'pages::settings.security')
    ->middleware(
        when(
            Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
            ['password.confirm'],
            [],
        ),
    )
    ->name('settings.security');

Route::livewire('settings/preferences', 'pages::settings.preferences')
    ->name('settings.preferences');

Route::livewire('settings/notifications', 'pages::settings.notifications')
    ->name('settings.notifications');

Route::livewire('settings/mail', 'pages::settings.mail')
    ->middleware('can:' . Permissions::CONFIGURE_MAIL_SETTINGS)
    ->name('settings.mail');

// Legacy routes with redirects for backward compatibility
Route::redirect('settings/profile', 'settings/account');
Route::redirect('settings/password', 'settings/account');
Route::redirect('settings/two-factor', 'settings/security');

// Keep old route names as aliases for backward compatibility
Route::livewire('settings/profile-legacy', 'pages::settings.profile')
    ->name('profile.edit');

Route::livewire('settings/password-legacy', 'pages::settings.password')
    ->name('user-password.edit');

Route::livewire('settings/two-factor-legacy', 'pages::settings.two-factor')
    ->middleware(
        when(
            Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
            ['password.confirm'],
            [],
        ),
    )
    ->name('two-factor.show');

// Error Logs management routes
require __DIR__ . '/error-logs.php';
