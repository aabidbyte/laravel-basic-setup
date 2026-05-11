@props(['user'])

@php
    $tenants = $user->tenants;
    $count = $tenants->count();
@endphp

<div class="flex flex-wrap gap-1">
    @if ($count > 3)
        <div class="badge badge-accent badge-sm">{{ $count }} {{ __('navigation.tenants') }}</div>
    @else
        @foreach ($tenants as $tenant)
            <div class="badge badge-accent badge-sm">{{ $tenant->name }}</div>
        @endforeach
    @endif
</div>
