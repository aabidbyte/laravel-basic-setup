@props(['name', 'pack' => null, 'class' => '', 'size' => null])

@inject('mapper', \App\Services\IconPackMapper::class)

{!! $mapper->renderIcon($name, $pack, $class, $size) !!}
