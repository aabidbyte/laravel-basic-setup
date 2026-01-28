@props(['targetId'])

<div x-data="passwordStrength('{{ $targetId }}', {
    weak: '{{ __('auth.password_strength.weak') }}',
    good: '{{ __('auth.password_strength.good') }}',
    strong: '{{ __('auth.password_strength.strong') }}'
})"
     class="mt-3 space-y-3">
    {{-- Header with Score Label --}}
    <div class="flex items-center justify-between text-xs"
         x-show="password.length > 0"
         x-cloak
         x-transition>
        <span class="text-base-content/70 font-medium">{{ __('auth.password_strength.title') }}</span>
        <span class="font-bold"
              :class="textColor"
              x-text="label"></span>
    </div>

    {{-- Segmented Progress Bar --}}
    <div class="grid h-1.5 w-full grid-cols-4 gap-1.5">
        <template x-for="i in 4">
            <div class="bg-base-200 rounded-full transition-colors duration-300"
                 :class="{
                     [color]: score >= i
                 }"></div>
        </template>
    </div>

    {{-- Requirements Checklist --}}
    <div class="text-base-content/60 grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
        <x-ui.label class="flex cursor-default items-center gap-2 transition-colors duration-200"
                    variant="plain"
                    :class="{ 'text-success font-medium translate-x-1': requirements.length }">
            <input type="checkbox"
                   class="checkbox checkbox-xs checkbox-success"
                   disabled
                   :checked="requirements.length" />
            <span>{{ __('auth.password_strength.requirements.length') }}</span>
        </x-ui.label>
        <x-ui.label class="flex cursor-default items-center gap-2 transition-colors duration-200"
                    variant="plain"
                    :class="{ 'text-success font-medium translate-x-1': requirements.lowercase && requirements.uppercase }">
            <input type="checkbox"
                   class="checkbox checkbox-xs checkbox-success"
                   disabled
                   :checked="requirements.lowercase && requirements.uppercase" />
            <span>{{ __('auth.password_strength.requirements.mixed_case') }}</span>
        </x-ui.label>
        <x-ui.label class="flex cursor-default items-center gap-2 transition-colors duration-200"
                    variant="plain"
                    :class="{ 'text-success font-medium translate-x-1': requirements.number }">
            <input type="checkbox"
                   class="checkbox checkbox-xs checkbox-success"
                   disabled
                   :checked="requirements.number" />
            <span>{{ __('auth.password_strength.requirements.number') }}</span>
        </x-ui.label>
        <x-ui.label class="flex cursor-default items-center gap-2 transition-colors duration-200"
                    variant="plain"
                    :class="{ 'text-success font-medium translate-x-1': requirements.symbol }">
            <input type="checkbox"
                   class="checkbox checkbox-xs checkbox-success"
                   disabled
                   :checked="requirements.symbol" />
            <span>{{ __('auth.password_strength.requirements.symbol') }}</span>
        </x-ui.label>
    </div>
</div>
