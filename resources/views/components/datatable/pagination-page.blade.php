@if ($i === $paginator->currentPage())
    <x-ui.button wire:key="page-{{ $i }}"
                 variant="solid"
                 size="sm"
                 class="btn-active pointer-events-none rounded-none"
                 aria-current="page">
        {{ $i }}
    </x-ui.button>
@else
    <x-ui.button wire:key="page-{{ $i }}"
                 wire:click="gotoPage({{ $i }}, '{{ $paginator->getPageName() }}')"
                 variant="ghost"
                 size="sm"
                 class="hidden rounded-none md:block"
                 aria-label="{{ __('pagination.go_to_page', ['page' => $i]) }}">
        {{ $i }}
    </x-ui.button>
@endif
