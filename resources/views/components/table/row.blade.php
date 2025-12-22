@props([
    'selected' => false,
])

<tr
    {{ $attributes->merge(['class' => 'cursor-pointer hover:bg-base-200' . ($selected ? ' bg-base-200' : '')]) }}
>
    {{ $slot }}
</tr>

