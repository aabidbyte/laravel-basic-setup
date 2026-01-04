{{--
    User View Modal Content
    
    Props received from datatable action-modal:
    - userUuid: UUID of the user to display
    
    Model is re-fetched here to have full Eloquent functionality.
    Uses $modalIsOpen from parent action-modal Alpine scope.
--}}
@props([
    'userUuid' => null,
])

@php
    use App\Models\User;
    
    // Fetch user from UUID
    $user = $userUuid ? User::where('uuid', $userUuid)->first() : null;
@endphp

<div class="flex flex-col gap-6 py-4">
    @if ($user)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Personal Information --}}
            <div class="flex flex-col gap-2">
                <h4 class="text-sm font-semibold text-base-content/50 uppercase tracking-wider">
                    {{ __('ui.users.personal_info') }}
                </h4>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-base-content/60">{{ __('ui.users.name') }}</span>
                    <span class="font-medium">{{ $user->name }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-base-content/60">{{ __('ui.users.email') }}</span>
                    <span class="font-medium">{{ $user->email }}</span>
                </div>
                @if ($user->username)
                    <div class="flex flex-col gap-1">
                        <span class="text-xs text-base-content/60">{{ __('ui.users.username') }}</span>
                        <span class="font-medium">{{ $user->username }}</span>
                    </div>
                @endif
            </div>

            {{-- Account Information --}}
            <div class="flex flex-col gap-2">
                <h4 class="text-sm font-semibold text-base-content/50 uppercase tracking-wider">
                    {{ __('ui.users.account_info') }}
                </h4>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-base-content/60">{{ __('ui.users.uuid') }}</span>
                    <span class="font-mono text-xs">{{ $user->uuid }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-base-content/60">{{ __('ui.users.status') }}</span>
                    <x-ui.badge :variant="$user->is_active ? 'success' : 'error'" size="sm">
                        {{ $user->is_active ? __('ui.users.active') : __('ui.users.inactive') }}
                    </x-ui.badge>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-base-content/60">{{ __('ui.users.created_at') }}</span>
                    <span>{{ $user->created_at?->diffForHumans() }} ({{ $user->created_at?->format('Y-m-d H:i') }})</span>
                </div>
            </div>
        </div>

        @if ($user->last_login_at)
            <div class="divider"></div>
            <div class="flex flex-col gap-1">
                <span class="text-xs text-base-content/60">{{ __('ui.users.last_login_at') }}</span>
                <span>{{ $user->last_login_at->diffForHumans() }} ({{ $user->last_login_at->format('Y-m-d H:i') }})</span>
            </div>
        @endif

        <div class="modal-action">
            {{-- modalIsOpen comes from parent action-modal Alpine scope --}}
            <x-ui.button @click="modalIsOpen = false" variant="ghost">
                {{ __('ui.actions.close') }}
            </x-ui.button>
        </div>
    @else
        <div class="alert alert-error">
            <x-ui.icon name="exclamation-triangle" size="sm"></x-ui.icon>
            <span>{{ __('ui.users.user_not_found') }}</span>
        </div>
    @endif
</div>
