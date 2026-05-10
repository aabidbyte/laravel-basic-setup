{{--
    Context Indicator Component:
    Displays the current context (Central Platform or Tenant Site) with a premium design.
--}}
<div class="flex items-center gap-3">
    @php
        $isTenant = tenancy()->initialized();
        $tenant = $isTenant ? tenant() : null;
        
        $tier = $isTenant ? 'tenant' : 'central';
        
        $tierLabel = match($tier) {
            'central' => __('navigation.platform'),
            'tenant' => __('navigation.site'),
        };
        
        $tierColor = match($tier) {
            'central' => 'primary',
            'tenant' => 'accent',
        };
        
        $tierIcon = match($tier) {
            'central' => 'globe-alt',
            'tenant' => 'building-office',
        };
    @endphp

    <div class="flex items-center gap-2 px-3 py-1.5 bg-base-200/50 rounded-lg border border-base-content/5 shadow-sm">
        <div @class([
            'flex items-center justify-center w-8 h-8 rounded-md shadow-inner',
            'bg-primary/10 text-primary' => $tier === 'central',
            'bg-accent/10 text-accent' => $tier === 'tenant',
        ])>
            <x-ui.icon :name="$tierIcon" size="sm" />
        </div>
        
        <div class="flex flex-col leading-none">
            <span class="text-[10px] uppercase tracking-wider font-bold opacity-50">{{ $tierLabel }}</span>
            <span class="text-sm font-semibold truncate max-w-[150px]">
                @if ($tier === 'central')
                    {{ config('app.name') }}
                @else
                    {{ $tenant->name ?? $tenant->id }}
                @endif
            </span>
        </div>

        @if ($tier === 'tenant')
            <div class="divider divider-horizontal mx-0 h-4 self-center opacity-20"></div>
            <a href="{{ route('central.dashboard') }}" 
               class="btn btn-ghost btn-xs btn-square hover:bg-base-300"
               title="{{ __('navigation.switch_context') }}">
                <x-ui.icon name="arrows-right-left" size="xs" />
            </a>
        @endif
    </div>
</div>
