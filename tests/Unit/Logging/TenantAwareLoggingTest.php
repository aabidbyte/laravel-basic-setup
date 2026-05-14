<?php

use App\Constants\Logging\LogChannels;
use App\Models\Tenant;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

afterEach(function (): void {
    if (\function_exists('tenancy')) {
        tenancy()->end();
    }
});

test('level specific logs use central path outside tenant context', function (): void {
    $path = storage_path('logs/info/laravel-' . now()->format('Y-m-d') . '.log');

    File::delete($path);

    Log::channel(LogChannels::INFO)->info('central info log');

    expect(File::exists($path))->toBeTrue()
        ->and(File::get($path))->toContain('central info log');
});

test('level specific logs use tenant path inside tenant context', function (): void {
    config(['tenancy.bootstrappers' => []]);

    $tenant = new Tenant(['id' => 'tenant-alpha']);
    $tenantPath = storage_path('logs/tenant-alpha/info/laravel-' . now()->format('Y-m-d') . '.log');
    $centralPath = storage_path('logs/info/laravel-' . now()->format('Y-m-d') . '.log');

    File::delete($tenantPath);
    File::delete($centralPath);

    tenancy()->initialize($tenant);

    Log::channel(LogChannels::INFO)->info('tenant info log');

    tenancy()->end();

    expect(File::exists($tenantPath))->toBeTrue()
        ->and(File::get($tenantPath))->toContain('tenant info log')
        ->and(File::exists($centralPath))->toBeFalse();
});

test('level specific logs return to central path after tenancy ends', function (): void {
    config(['tenancy.bootstrappers' => []]);

    $tenant = new Tenant(['id' => 'tenant-beta']);
    $tenantPath = storage_path('logs/tenant-beta/info/laravel-' . now()->format('Y-m-d') . '.log');
    $centralPath = storage_path('logs/info/laravel-' . now()->format('Y-m-d') . '.log');

    File::delete($tenantPath);
    File::delete($centralPath);

    tenancy()->initialize($tenant);
    Log::channel(LogChannels::INFO)->info('tenant beta info log');
    tenancy()->end();

    Log::channel(LogChannels::INFO)->info('central info log after tenancy');

    expect(File::exists($tenantPath))->toBeTrue()
        ->and(File::get($tenantPath))->toContain('tenant beta info log')
        ->and(File::exists($centralPath))->toBeTrue()
        ->and(File::get($centralPath))->toContain('central info log after tenancy');
});
