<?php

namespace Tests\Feature\Console;

use App\Services\Translation\TranslationScanner;
use Illuminate\Support\Facades\File;

test('scanner ignores empty translation keys', function () {
    // Setup
    $scanner = new TranslationScanner;
    $tempFile = base_path('tests/temp_empty_keys.blade.php');
    File::put($tempFile, '<div>{{ __("") }} {{ __(\'\') }} {{ __("valid.key") }}</div>');

    try {
        // Act
        $scanner->scanFile($tempFile);
        $keys = $scanner->getFoundKeys();

        // Assert
        expect($keys)->not->toHaveKey('');
        expect($keys)->toHaveKey('valid.key');
    } finally {
        // Cleanup
        if (File::exists($tempFile)) {
            File::delete($tempFile);
        }
    }
});
