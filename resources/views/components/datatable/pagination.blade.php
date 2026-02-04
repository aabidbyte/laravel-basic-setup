@php
    // Get share URL from the component (includes all query params: search, sort, filters, per_page, page)
    // $this refers to the Livewire component that rendered this pagination view
    // Pass current page from paginator to ensure correct page number
    $shareUrl = $this->getShareUrl($paginator->currentPage());
@endphp

<nav role="navigation"
     aria-label="{{ __('pagination.pagination_navigation') }}"
     class="flex items-center justify-between gap-2">
    {{-- Per-Page Selector (always visible) --}}
    <div class="flex items-center gap-2">
        <x-ui.tooltip text="{{ __('table.per_page') }}">
            <x-ui.select wire:model.live="perPage"
                         :label="null"
                         variant="ghost"
                         size="sm"
                         :options="['12' => '12', '25' => '25', '50' => '50', '100' => '100', '200' => '200']"
                         :prependEmpty="false"
                         title="{{ __('table.per_page') }}">
            </x-ui.select>
        </x-ui.tooltip>
        <div class="hidden md:block">
            <p class="text-base-content/70 text-sm">
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
                {!! __('pagination.of') !!}
                <span class="font-medium">{{ $paginator->total() }}</span>
                {!! __('pagination.results') !!}
            </p>
        </div>
    </div>

    @if ($paginator->hasPages())
        <div class="flex items-center justify-between gap-2">

            <div class="flex items-center gap-2">
                <span class="inline-flex rounded-md shadow-sm rtl:flex-row-reverse">

                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <x-ui.button variant="ghost"
                                     size="sm"
                                     disabled
                                     class="rounded-r-none"
                                     aria-label="{{ __('pagination.previous') }}">
                            <x-ui.icon name="chevron-left"
                                       size="sm" />
                        </x-ui.button>
                    @else
                        <x-ui.button wire:click="previousPage('{{ $paginator->getPageName() }}')"
                                     variant="ghost"
                                     size="sm"
                                     class="rounded-r-none"
                                     aria-label="{{ __('pagination.previous') }}">
                            <x-ui.icon name="chevron-left"
                                       size="sm" />
                        </x-ui.button>
                    @endif

                    {{-- Custom Smart Pagination Logic --}}
                    @php
                        $currentPage = $paginator->currentPage();
                        $lastPage = $paginator->lastPage();
                        $distanceToEnd = $lastPage - $currentPage;
                    @endphp

                    @if ($distanceToEnd <= 3)
                        {{-- Rule: Always show last 4 pages (e.g., 24, 25, 26, 27) --}}
                        @php $start = max(1, $lastPage - 3); @endphp
                        @for ($i = $start; $i <= $lastPage; $i++)
                            @if ($i == $currentPage)
                                <x-ui.button variant="solid"
                                             size="sm"
                                             class="btn-active pointer-events-none rounded-none"
                                             aria-current="page">
                                    {{ $i }}
                                </x-ui.button>
                            @else
                                <x-ui.button wire:click="gotoPage({{ $i }}, '{{ $paginator->getPageName() }}')"
                                             variant="ghost"
                                             size="sm"
                                             class="hidden rounded-none md:block"
                                             aria-label="{{ __('pagination.go_to_page', ['page' => $i]) }}">
                                    {{ $i }}
                                </x-ui.button>
                            @endif
                        @endfor
                    @elseif ($distanceToEnd == 4)
                        {{-- Rule: Near end (e.g. page 23), show current through to end (23, 24, 25, 26, 27) --}}
                        @for ($i = $currentPage; $i <= $lastPage; $i++)
                            @if ($i == $currentPage)
                                <x-ui.button variant="solid"
                                             size="sm"
                                             class="btn-active pointer-events-none rounded-none"
                                             aria-current="page">
                                    {{ $i }}
                                </x-ui.button>
                            @else
                                <x-ui.button wire:click="gotoPage({{ $i }}, '{{ $paginator->getPageName() }}')"
                                             variant="ghost"
                                             size="sm"
                                             class="hidden rounded-none md:block"
                                             aria-label="{{ __('pagination.go_to_page', ['page' => $i]) }}">
                                    {{ $i }}
                                </x-ui.button>
                            @endif
                        @endfor
                    @else
                        {{-- Rule: Standard smart pattern (e.g. 1 2 3 ... 26 27 or 22 23 24 ... 26 27) --}}
                        @php $windowEnd = $currentPage + 2; @endphp
                        @for ($i = $currentPage; $i <= $windowEnd; $i++)
                            @if ($i == $currentPage)
                                <x-ui.button variant="solid"
                                             size="sm"
                                             class="btn-active pointer-events-none rounded-none"
                                             aria-current="page">
                                    {{ $i }}
                                </x-ui.button>
                            @else
                                <x-ui.button wire:click="gotoPage({{ $i }}, '{{ $paginator->getPageName() }}')"
                                             variant="ghost"
                                             size="sm"
                                             class="hidden rounded-none md:block"
                                             aria-label="{{ __('pagination.go_to_page', ['page' => $i]) }}">
                                    {{ $i }}
                                </x-ui.button>
                            @endif
                        @endfor

                        <x-ui.button variant="ghost"
                                     size="sm"
                                     disabled
                                     class="hidden rounded-none sm:block">
                            ...
                        </x-ui.button>

                        @for ($i = $lastPage - 1; $i <= $lastPage; $i++)
                            <x-ui.button wire:click="gotoPage({{ $i }}, '{{ $paginator->getPageName() }}')"
                                         variant="ghost"
                                         size="sm"
                                         class="hidden rounded-none sm:block"
                                         aria-label="{{ __('pagination.go_to_page', ['page' => $i]) }}">
                                {{ $i }}
                            </x-ui.button>
                        @endfor
                    @endif

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <x-ui.button wire:click="nextPage('{{ $paginator->getPageName() }}')"
                                     variant="ghost"
                                     size="sm"
                                     class="rounded-l-none"
                                     aria-label="{{ __('pagination.next') }}">
                            <x-ui.icon name="chevron-right"
                                       size="sm" />
                        </x-ui.button>
                    @else
                        <x-ui.button variant="ghost"
                                     size="sm"
                                     disabled
                                     class="rounded-l-none"
                                     aria-label="{{ __('pagination.next') }}">
                            <x-ui.icon name="chevron-right"
                                       size="sm" />
                        </x-ui.button>
                    @endif
                </span>
                @if ($paginator->lastPage() > 20)
                    <div class="flex items-center gap-2">
                        <x-ui.input type="number"
                                    wire:model.blur="gotoPageInput"
                                    wire:keydown.enter="performGotoPage"
                                    class="text-center"
                                    size="sm"
                                    min="1"
                                    max="{{ $paginator->lastPage() }}"
                                    placeholder="1"
                                    aria-label="{{ __('pagination.go_to_page_label') }}" />
                        <x-ui.tooltip text="{{ __('pagination.go_to_page_label') }}">
                            <x-ui.icon wire:click="performGotoPage"
                                       name="chevron-double-right"
                                       size="xs" />
                        </x-ui.tooltip>
                    </div>
                @endif
                {{-- Share Button --}}
                <x-ui.share-button :url="$shareUrl"
                                   size="sm"
                                   variant="ghost"></x-ui.share-button>
            </div>
        </div>
    @else
        {{-- Single page: Just show record count --}}
        <div class="flex items-center gap-2">
            <p class="text-base-content/70 text-sm">
                <span class="font-medium">{{ $paginator->total() }}</span>
                {!! __('pagination.results') !!}
            </p>
        </div>
    @endif
</nav>
