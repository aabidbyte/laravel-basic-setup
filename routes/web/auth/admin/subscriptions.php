<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::livewire('/subscriptions', 'pages::subscriptions.⚡index')
        ->name('subscriptions.index');

    Route::livewire('/subscriptions/create', 'pages::subscriptions.⚡edit')
        ->name('subscriptions.create');

    Route::livewire('/subscriptions/{subscription}/edit', 'pages::subscriptions.⚡edit')
        ->name('subscriptions.edit');
});
