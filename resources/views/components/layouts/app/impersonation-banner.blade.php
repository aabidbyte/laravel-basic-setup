@if (session()->has('impersonator_id'))
    @php
        $user = Auth::user();
        $roles = $user->roles->pluck('name')->map(fn($role) => \Str::headline($role))->implode(', ');
        $tenantName = tenant('name') ?? __('tenancy.unknown_tenant');
    @endphp
    <div class="bg-error animate-in slide-in-from-top sticky top-0 z-[100] flex items-center justify-between border-b border-white/20 px-4 py-2 text-white shadow-xl duration-500">
        <div class="flex items-center gap-4">
            <div class="flex h-10 w-10 animate-pulse items-center justify-center rounded-lg bg-white/20">
                <x-ui.icon name="user-secret" pack="fontawesome" size="lg" />
            </div>
            <div class="flex flex-col">
                <span class="font-mono text-[10px] font-bold uppercase leading-tight tracking-[0.2em] opacity-80">
                    {{ __('tenancy.system_override_mode') }}
                </span>
                <span class="text-sm font-bold tracking-tight">
                    {{ __('tenancy.impersonating_user', ['user' => $user->name]) }}
                    <span class="mx-1 opacity-60 font-normal">{{ __('tenancy.in_tenant', ['tenant' => $tenantName]) }}</span>
                    @if($roles)
                        <span class="ml-2 rounded bg-white/10 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide opacity-90">
                            {{ __('tenancy.with_roles', ['roles' => $roles]) }}
                        </span>
                    @endif
                </span>
            </div>
        </div>

        <a href="{{ route('administration.instance.stop-impersonating') }}"
           class="btn btn-sm text-error rounded-lg border-none bg-white px-6 text-[10px] font-black uppercase tracking-wider shadow-lg hover:bg-white/90">
            <x-ui.icon name="times" pack="fontawesome" size="xs" class="mr-1" />
            {{ __('tenancy.stop_impersonating') }}
        </a>
    </div>
@endif
