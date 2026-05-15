@props(['tenant'])

<div class="flex items-center gap-3">
    <x-ui.avatar initials="{{ strtoupper(substr($tenant->name ?? 'W', 0, 1)) }}"
                 size="sm"
                 shape="square"
                 :class="tenant()?->getTenantKey() === $tenant->tenant_id ? 'bg-primary' : 'bg-base-300'"></x-ui.avatar>
    <div class="flex flex-col items-start">
        <span @class([
            'text-sm font-medium',
            'text-primary' => tenant()?->getTenantKey() === $tenant->tenant_id,
            'text-base-content' => tenant()?->getTenantKey() !== $tenant->tenant_id,
        ])>
            {{ $tenant->name }}
        </span>
        @if (tenant()?->getTenantKey() === $tenant->tenant_id)
            <span class="text-primary/70 text-[10px] font-bold uppercase tracking-wider">
                {{ __('tenancy.active') }}
            </span>
        @endif
    </div>
</div>
