<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::livewire('/features', 'pages::features.⚡index')
        ->name('features.index');

    Route::livewire('/features/create', 'pages::features.⚡edit')
        ->name('features.create');

    Route::livewire('/features/{feature}/edit', 'pages::features.⚡edit')
        ->name('features.edit');
});
