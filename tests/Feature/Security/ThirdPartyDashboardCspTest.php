<?php

use App\Support\Csp\MyCspPreset;
use Illuminate\Http\Request;
use Spatie\Csp\Policy;

it('keeps the default application csp strict for inline scripts and unsafe eval', function (): void {
    setRequestPath('/dashboard');

    expect(scriptDirective())
        ->toContain('script-src')
        ->not->toContain("'unsafe-inline'")
        ->not->toContain("'unsafe-eval'");
});

it('allows inline dashboard scripts and unsafe eval only on horizon and telescope dashboards', function (string $path): void {
    setRequestPath($path);

    $scriptDirective = scriptDirective();

    expect($scriptDirective)
        ->toContain("'unsafe-eval'")
        ->toContain("'unsafe-inline'");

    expect(cspDirective('script-src-elem'))
        ->toContain("'self'")
        ->toContain("'unsafe-inline'");
})->with([
    'horizon' => ['/admin/system/queue-monitor'],
    'telescope' => ['/admin/system/debug/monitoring'],
]);

it('allows log viewer inline bootstrap script only on log viewer routes', function (): void {
    setRequestPath('/admin/system/log-viewer');

    expect(scriptDirective())
        ->toContain("'unsafe-inline'")
        ->toContain("'unsafe-eval'");

    expect(cspDirective('script-src-elem'))
        ->toContain("'self'")
        ->toContain("'unsafe-inline'");
});

it('supports adding future vendor dashboards through csp config', function (): void {
    config()->set('csp.third_party_dashboards', [
        [
            'name' => 'future-package',
            'path' => 'admin/system/future-package',
            'allow_inline_scripts' => true,
            'allow_unsafe_eval' => false,
        ],
    ]);

    setRequestPath('/admin/system/future-package/reports');

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
