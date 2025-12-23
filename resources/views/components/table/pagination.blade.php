@props([
    'paginator',
])

<div class="flex items-center justify-between">
    <div class="text-sm text-base-content/70">
        @if ($paginator->total() > 0)
            {{ __('ui.table.pagination.showing', [
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
            ]) }}
        @else
            {{ __('ui.table.empty') }}
        @endif
    </div>

    @if ($paginator->hasPages())
        <div class="join">
            @if ($paginator->onFirstPage())
                <button class="btn btn-sm btn-disabled join-item" disabled>
                    <x-ui.icon name="chevron-left" class="h-4 w-4"></x-ui.icon>
                </button>
            @else
                <button wire:click="previousPage" class="btn btn-sm join-item">
                    <x-ui.icon name="chevron-left" class="h-4 w-4"></x-ui.icon>
                </button>
            @endif

            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
            @endphp

            @if ($startPage > 1)
                <button wire:click="gotoPage(1)" class="btn btn-sm join-item">1</button>
                @if ($startPage > 2)
                    <button class="btn btn-sm btn-disabled join-item" disabled>...</button>
                @endif
            @endif

            @for ($page = $startPage; $page <= $endPage; $page++)
                @if ($page == $currentPage)
                    <button class="btn btn-sm btn-active join-item" disabled>
                        {{ $page }}
                    </button>
                @else
                    <button wire:click="gotoPage({{ $page }})" class="btn btn-sm join-item">
                        {{ $page }}
                    </button>
                @endif
            @endfor

            @if ($endPage < $lastPage)
                @if ($endPage < $lastPage - 1)
                    <button class="btn btn-sm btn-disabled join-item" disabled>...</button>
                @endif
                <button wire:click="gotoPage({{ $lastPage }})" class="btn btn-sm join-item">{{ $lastPage }}</button>
            @endif

            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" class="btn btn-sm join-item">
                    <x-ui.icon name="chevron-right" class="h-4 w-4"></x-ui.icon>
                </button>
            @else
                <button class="btn btn-sm btn-disabled join-item" disabled>
                    <x-ui.icon name="chevron-right" class="h-4 w-4"></x-ui.icon>
                </button>
            @endif
        </div>
    @endif
</div>

