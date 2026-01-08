@props(['name' => null, 'pack' => null, 'class' => '', 'size' => null])

@inject('mapper', \App\Services\IconPackMapper::class)

<span {{ $attributes->except(['class', 'name', 'pack', 'size']) }}>
    {!! $mapper->renderIcon($name, $pack, $class, $size) !!}
</span>
