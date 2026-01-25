@props(['name' => null, 'pack' => null, 'class' => null, 'size' => null])

@inject('mapper', App\Services\IconPackMapper::class)

<span {{ $attributes->except(['class','name', 'pack', 'size']) }}>
    {!! $mapper->renderIcon(name:$name, pack:$pack, class:$class,size:$size) !!}
</span>
