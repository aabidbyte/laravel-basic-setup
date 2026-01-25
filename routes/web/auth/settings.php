<?php

use App\Constants\Auth\Permissions;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Settings routes - redirect to new consolidated account page
Route::redirect('settings', 'settings/account')->name('settings.index');

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
    ->middleware('can:' . Permissions::CONFIGURE_MAIL_SETTINGS())
    ->name('settings.mail');
