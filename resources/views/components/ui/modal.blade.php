@props(['name', 'show' => false, 'title' => null, 'description' => null])

<div x-data="{ show: @js($show) }" x-show="show" @keydown.escape.window="show = false"
    @open-modal.window="if ($event.detail === '{{ $name }}') show = true"
    @close-modal.window="if ($event.detail === '{{ $name }}') show = false" class="modal" role="dialog"
    aria-modal="true" style="display: none;">
    <div class="modal-box">
        @if ($title)
            <h3 class="font-bold text-lg mb-2">{{ $title }}</h3>
        @endif
        @if ($description)
            <p class="text-base-content/70 mb-4">{{ $description }}</p>
        @endif
        {{ $slot }}
    </div>
    <form method="dialog" class="modal-backdrop">
        <button type="button" @click="show = false">close</button>
    </form>
</div>
