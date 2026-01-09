<?php

declare(strict_types=1);

use App\Http\Middleware\Trash\EnableTrashedContext;
use App\Services\Trash\TrashRegistry;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Trash Routes
|--------------------------------------------------------------------------
|
| Routes for the unified trash/restore system. Index and show pages use
| Livewire SFC components. Restore and force-delete actions are handled
| directly in the Livewire components.
|
*/

// Get entity type pattern dynamically from registry
$entityPattern = app(TrashRegistry::class)->getRoutePattern();

Route::prefix('trash')
    ->name('trash.')
    ->middleware([EnableTrashedContext::class])
    ->group(function () use ($entityPattern) {
        // Trash index - uses plain Blade with Livewire DataTable
        Route::get('/{entityType}', fn (string $entityType) => view('pages.trash.index', [
            'entityType' => $entityType,
        ]))
            ->name('index')
            ->where('entityType', $entityPattern);

        // Trash show - uses Livewire SFC
        Route::livewire('/{entityType}/{uuid}', 'pages::trash.show')
            ->where('entityType', $entityPattern)
            ->name('show');
    });
