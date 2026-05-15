<?php

use App\Livewire\Tables\TenantTable;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Stancl\Tenancy\Database\Models\Domain;

test('tenant table renders first domain with remaining count in tenant color badge', function (): void {
    $tenant = new Tenant([
        'id' => 'org',
        'color' => 'accent',
    ]);
    $tenant->setRelation('domains', new Collection([
        new Domain(['domain' => 'org.example.test']),
        new Domain(['domain' => 'org-alt.example.test']),
        new Domain(['domain' => 'org-extra.example.test']),
    ]));

    $html = renderTenantTableDomainsBadge($tenant);

    expect($html)
        ->toContain('badge-accent')
        ->toContain('org.example.test +2')
        ->not->toContain('org-alt.example.test');
});

test('tenant table renders a muted placeholder when no domain exists', function (): void {
    $tenant = new Tenant([
        'id' => 'org',
        'color' => 'primary',
    ]);
    $tenant->setRelation('domains', new Collection());

    expect(renderTenantTableDomainsBadge($tenant))
        ->toContain('text-base-content/40')
        ->toContain('>-<');
});

function renderTenantTableDomainsBadge(Tenant $tenant): string
{
    $method = new ReflectionMethod(TenantTable::class, 'renderDomainsBadge');
    $method->setAccessible(true);

    return (string) $method->invoke(new TenantTable(), $tenant);
}
