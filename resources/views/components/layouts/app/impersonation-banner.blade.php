@if (session()->has('impersonator_id'))
    <div
         class="bg-error animate-in slide-in-from-top sticky top-0 z-[100] flex items-center justify-between border-b border-white/20 px-4 py-2 text-white shadow-xl duration-500">
        <div class="flex items-center gap-4">
            <div class="flex h-8 w-8 animate-pulse items-center justify-center rounded bg-white/20">
                <x-ui.icon name="exclamation-triangle"
                           size="sm" />
            </div>
            <div class="flex flex-col">
                <span class="font-mono text-[10px] font-bold uppercase leading-tight tracking-[0.2em] opacity-80">System
                    Override Mode</span>
                <span class="text-sm font-bold tracking-tight">
                    {{ __('messages.context.impersonating', ['name' => Auth::user()->name]) }}
                </span>
            </div>
        </div>

        <a href="{{ route('administration.instance.stop-impersonating') }}"
           class="btn btn-sm text-error rounded-none border-none bg-white px-6 text-[10px] font-black uppercase tracking-wider shadow-lg hover:bg-white/90">
            {{ __('actions.stop_impersonating') ?? 'Stop Impersonation' }}
        </a>
    </div>
@endif
