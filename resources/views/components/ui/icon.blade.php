@props(['name' => null, 'pack' => null, 'class' => null, 'size' => null, 'color' => null])

@inject('mapper', App\Services\IconPackMapper::class)

@php
    $boundColor = $attributes->get('x-bind:color') ?? $attributes->get(':color');
@endphp

<span {{ $attributes->except(['class', 'name', 'pack', 'size', 'color', 'x-bind:color', ':color']) }}
      @if ($boundColor) x-bind:class="{{ alpineColorClasses($boundColor, 'text-') }}" @endif>
    {!! $mapper->renderIcon(
        new App\Support\UI\IconOptions(name: $name, pack: $pack, class: $class, size: $size, color: $color),
    ) !!}
</span>
