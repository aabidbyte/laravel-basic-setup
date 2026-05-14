<?php

use App\Models\Tenant;
use App\Support\Csp\MyCspPreset;
use Spatie\Csp\Policy;

it('adds only current tenant domains to passive and connect csp sources', function (): void {
    $tenant = Tenant::factory()->create();
    $tenant->domains()->create(['domain' => 'current-tenant.example.test']);

    $otherTenant = Tenant::factory()->create();
    $otherTenant->domains()->create(['domain' => 'other-tenant.example.test']);

    tenancy()->initialize($tenant);

    $policy = new Policy();
    app(MyCspPreset::class)->configure($policy);

    $contents = $policy->getContents();

    expect($contents)
        ->toContain('connect-src')
        ->toContain('http://current-tenant.example.test')
        ->toContain('ws://current-tenant.example.test')
        ->toContain('frame-src')
        ->toContain('img-src')
        ->not->toContain('other-tenant.example.test');

    $scriptDirective = collect(explode(';', $contents))
        ->first(fn (string $directive) => str_starts_with($directive, 'script-src'));

    expect($scriptDirective)->not->toContain('current-tenant.example.test');
});

it('does not add tenant csp sources outside a tenant context', function (): void {
    $tenant = Tenant::factory()->create();
    $tenant->domains()->create(['domain' => 'inactive-tenant.example.test']);

    $policy = new Policy();
    app(MyCspPreset::class)->configure($policy);

    expect($policy->getContents())->not->toContain('inactive-tenant.example.test');
});

it('allows runtime style elements for the email template builder', function (): void {
    $policy = new Policy();
    app(MyCspPreset::class)->configure($policy);

    $styleElementDirective = collect(explode(';', $policy->getContents()))
        ->first(fn (string $directive): bool => str_starts_with($directive, 'style-src-elem'));

    expect($styleElementDirective)
        ->toContain("'unsafe-inline'")
        ->toContain('https://unpkg.com');
});
