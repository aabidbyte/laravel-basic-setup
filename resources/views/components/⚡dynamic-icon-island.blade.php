<?php

use App\Services\IconPackMapper;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    public function mount(string $name, ?string $pack = null, string $class = ''): void
    {
        $this->pack = $pack ?? 'heroicons';
        $this->name = $name;
        $this->class = $class;
    }

    #[Locked]
    public string $pack;

    #[Locked]
    public string $name;

    #[Locked]
    public string $class = '';

    #[Computed]
    public function componentName(): string
    {
        $mapper = app(IconPackMapper::class);

        return $mapper->getComponentName($this->pack, $this->name);
    }
};
?>

@php
    $componentClass = $class ?: 'w-6 h-6';
@endphp

<div class="inline-flex items-center justify-center {{ $componentClass }}">
    @try
        <x-dynamic-component :component="$this->componentName" :class="$componentClass" />
        @catch (\Exception $e)
        {{-- Fallback to question mark icon if component doesn't exist --}}
        <x-heroicon-o-question-mark-circle :class="$componentClass" />
    @endtry
</div>
