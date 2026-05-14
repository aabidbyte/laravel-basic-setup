<?php

use Illuminate\Support\Facades\File;

test('clears browser.log file', function () {
    $logsPath = storage_path('logs');
    $browserLogPath = "{$logsPath}/browser.log";

    // Create browser.log file
    File::ensureDirectoryExists($logsPath);
    File::put($browserLogPath, 'test browser log content');

    expect(File::exists($browserLogPath))->toBeTrue();

    $this->artisan('logs:clear')
        ->assertSuccessful();

    expect(File::exists($browserLogPath))->toBeFalse();
});

test('clears pail log files', function () {
    $pailPath = storage_path('pail');
    $pailFile = "{$pailPath}/test123.pail";

    // Create Pail log file
    File::ensureDirectoryExists($pailPath);
    File::put($pailFile, 'test pail log content');

    expect(File::exists($pailFile))->toBeTrue();

    $this->artisan('logs:clear')
        ->assertSuccessful();

    expect(File::exists($pailFile))->toBeFalse();
});

test('clears laravel.log file', function () {
    $logsPath = storage_path('logs');
    $laravelLogPath = "{$logsPath}/laravel.log";

    // Create laravel.log file
    File::ensureDirectoryExists($logsPath);
    File::put($laravelLogPath, 'test laravel log content');

    expect(File::exists($laravelLogPath))->toBeTrue();

    $this->artisan('logs:clear')
        ->assertSuccessful();

    expect(File::exists($laravelLogPath))->toBeFalse();
});

test('clears all main log files including browser.log and pail logs', function () {
    $logsPath = storage_path('logs');
    $pailPath = storage_path('pail');

    $laravelLogPath = "{$logsPath}/laravel.log";
    $browserLogPath = "{$logsPath}/browser.log";
    $pailFile1 = "{$pailPath}/test1.pail";
    $pailFile2 = "{$pailPath}/test2.pail";

    // Create all log files
    File::ensureDirectoryExists($logsPath);
    File::ensureDirectoryExists($pailPath);
    File::put($laravelLogPath, 'laravel log');
    File::put($browserLogPath, 'browser log');
    File::put($pailFile1, 'pail log 1');
    File::put($pailFile2, 'pail log 2');

    expect(File::exists($laravelLogPath))->toBeTrue();
    expect(File::exists($browserLogPath))->toBeTrue();
    expect(File::exists($pailFile1))->toBeTrue();
    expect(File::exists($pailFile2))->toBeTrue();

    $this->artisan('logs:clear')
        ->assertSuccessful();

    expect(File::exists($laravelLogPath))->toBeFalse();
    expect(File::exists($browserLogPath))->toBeFalse();
    expect(File::exists($pailFile1))->toBeFalse();
    expect(File::exists($pailFile2))->toBeFalse();
});

test('clears tenant nested log files', function () {
    $logsPath = storage_path('logs');
    $tenantLogPath = "{$logsPath}/tenant-alpha/info/laravel-2026-05-14.log";

    File::ensureDirectoryExists(\dirname($tenantLogPath));
    File::put($tenantLogPath, 'tenant info log');

    expect(File::exists($tenantLogPath))->toBeTrue();

    $this->artisan('logs:clear')
        ->assertSuccessful();

    expect(File::exists($tenantLogPath))->toBeFalse();
});

test('clears tenant nested log files for a specific level', function () {
    $logsPath = storage_path('logs');
    $infoLogPath = "{$logsPath}/tenant-alpha/info/laravel-2026-05-14.log";
    $errorLogPath = "{$logsPath}/tenant-alpha/error/laravel-2026-05-14.log";

    File::ensureDirectoryExists(\dirname($infoLogPath));
    File::ensureDirectoryExists(\dirname($errorLogPath));
    File::put($infoLogPath, 'tenant info log');
    File::put($errorLogPath, 'tenant error log');

    $this->artisan('logs:clear --level=info')
        ->assertSuccessful();

    expect(File::exists($infoLogPath))->toBeFalse()
        ->and(File::exists($errorLogPath))->toBeTrue();

    File::delete($errorLogPath);
});
