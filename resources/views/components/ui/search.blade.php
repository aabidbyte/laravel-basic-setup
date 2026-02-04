@props([
    'label' => null,
    'error' => null,
    'required' => false,
])

<x-ui.input {{ $attributes }}
            :label="$label"
            :error="$error"
            :required="$required">
    <x-slot:prepend>
        <x-ui.icon name="magnifying-glass"
                   size="sm"
                   class="text-base-content opacity-50" />
    </x-slot:prepend>
</x-ui.input>
