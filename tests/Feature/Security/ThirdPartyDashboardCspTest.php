<?php

use App\Support\Csp\MyCspPreset;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Facades\LogViewer;
use Spatie\Csp\Policy;

it('keeps the default application csp strict for inline scripts and unsafe eval', function (): void {
    setRequestPath(route('dashboard', absolute: false));

    expect(scriptDirective())
        ->toContain('script-src')
        ->not->toContain("'unsafe-inline'")
        ->not->toContain("'unsafe-eval'");
});

it('allows inline dashboard scripts and unsafe eval only on horizon and telescope dashboards', function (string $configKey): void {
    setRequestPathFromConfig($configKey);

    $scriptDirective = scriptDirective();

    expect($scriptDirective)
        ->toContain("'unsafe-eval'")
        ->toContain("'unsafe-inline'");

    expect(cspDirective('script-src-elem'))
        ->toContain("'self'")
        ->toContain("'unsafe-inline'");
})->with([
    'horizon' => ['horizon.path'],
    'telescope' => ['telescope.path'],
]);

it('allows log viewer inline bootstrap script only on log viewer routes', function (): void {
    setRequestPathFromConfig('log-viewer.route_path');

    expect(scriptDirective())
        ->toContain("'unsafe-inline'")
        ->toContain("'unsafe-eval'");

    expect(cspDirective('script-src-elem'))
        ->toContain("'self'")
        ->toContain("'unsafe-inline'");
});

it('adds a nonce to the log viewer bootstrap script', function (): void {
    LogViewer::auth(fn (): bool => true);

    $response = $this->get('/' . config('log-viewer.route_path'));

    $response->assertOk();

    expect($response->getContent())
        ->toMatch('/<script\\s+nonce="[^"]+">\\s*window\\.LogViewer/s');
});

it('supports adding future vendor dashboards through csp config', function (): void {
    $futureDashboardPath = 'vendor/future-package';

    config()->set('csp.third_party_dashboards', [
        [
            'name' => 'future-package',
            'path' => $futureDashboardPath,
            'allow_inline_scripts' => true,
            'allow_unsafe_eval' => false,
        ],
    ]);

    setRequestPath("{$futureDashboardPath}/reports");

    expect(scriptDirective())
        ->toContain("'unsafe-inline'")
        ->not->toContain("'unsafe-eval'");
});

function policyContents(): string
{
    $policy = new Policy();

    app(MyCspPreset::class)->configure($policy);

    return $policy->getContents();
}

function scriptDirective(): string
{
    return cspDirective('script-src');
}

function cspDirective(string $name): string
{
    return collect(\explode(';', policyContents()))
        ->first(fn (string $directive): bool => $directive === $name || str_starts_with($directive, "{$name} "))
        ?? '';
}

function setRequestPath(string $path): void
{
    app()->instance('request', Request::create($path));
}

function setRequestPathFromConfig(string $key): void
{
    setRequestPath((string) config($key));
}
