@if(session()->has('impersonator_id'))
    <div class="bg-error text-white px-4 py-2 flex items-center justify-between sticky top-0 z-[100] shadow-xl border-b border-white/20 animate-in slide-in-from-top duration-500">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-8 h-8 rounded bg-white/20 animate-pulse">
                <x-ui.icon name="exclamation-triangle" size="sm" />
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-mono font-bold uppercase tracking-[0.2em] opacity-80 leading-tight">System Override Mode</span>
                <span class="text-sm font-bold tracking-tight">
                    {{ __('messages.context.impersonating', ['name' => Auth::user()->name]) }}
                </span>
            </div>
        </div>

        <a href="{{ route('administration.instance.stop-impersonating') }}" 
           class="btn btn-sm bg-white text-error border-none hover:bg-white/90 shadow-lg font-black uppercase tracking-wider text-[10px] px-6 rounded-none">
            {{ __('actions.stop_impersonating') ?? 'Stop Impersonation' }}
        </a>
    </div>
@endif
