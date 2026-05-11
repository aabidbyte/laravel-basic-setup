@props(['tenant'])

<div class="flex items-center gap-3">
    <x-ui.avatar initials="{{ strtoupper(substr($tenant->name ?? 'W', 0, 1)) }}"
                 size="sm"
                 shape="square"
                 :class="tenant('id') === $tenant->id ? 'bg-primary' : 'bg-base-300'"></x-ui.avatar>
    <div class="flex flex-col items-start">
        <span @class([
            'text-sm font-medium',
            'text-primary' => tenant('id') === $tenant->id,
            'text-base-content' => tenant('id') !== $tenant->id,
        ])>
            {{ $tenant->name }}
        </span>
        @if (tenant('id') === $tenant->id)
            <span class="text-primary/70 text-[10px] font-bold uppercase tracking-wider">
                {{ __('tenancy.active') }}
            </span>
        @endif
    </div>
</div>
