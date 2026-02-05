<div aria-label="{{ __('pagination.pagination_navigation') }}"
     class="flex items-center justify-between gap-2"
     wire:key="datatable-pagination-{{ $this->datatableId }}-{{ $position ?? 'default' }}">
    {{-- LEFT SIDE --}}
    <div class="flex items-center gap-2">
        {{-- Per page --}}
        <x-ui.tooltip text="{{ __('table.per_page') }}"
                      wire:key="pagination-per-page-tooltip">
            <x-ui.select wire:model.live="perPage"
                         :label="null"
                         variant="ghost"
                         size="sm"
                         :options="['12' => '12', '25' => '25', '50' => '50', '100' => '100', '200' => '200']"
                         :prependEmpty="false"
                         title="{{ __('table.per_page') }}" />
        </x-ui.tooltip>

        {{-- Count --}}
        <div class="hidden md:block">
            <p class="text-base-content/70 text-sm">
                @if ($paginator->hasPages())
                    <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    {{ __('pagination.of') }}
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {{ __('pagination.results') }}
                @else
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {{ __('pagination.results') }}
                @endif
            </p>
        </div>
    </div>

    {{-- RIGHT SIDE (always rendered) --}}
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            {{-- Pagination buttons container (always exists) --}}
            <div class="inline-flex rounded-md shadow-sm rtl:flex-row-reverse">
                @if ($paginator->hasPages())
                    {{-- Previous --}}
                    @if ($paginator->onFirstPage())
                        <x-ui.button wire:key="prev-disabled"
                                     variant="ghost"
                                     size="sm"
                                     disabled
                                     class="rounded-r-none"
                                     aria-label="{{ __('pagination.previous') }}">
                            <x-ui.icon name="chevron-left"
                                       size="sm" />
                        </x-ui.button>
                    @else
                        <x-ui.button wire:key="prev-active"
                                     wire:click="previousPage('{{ $paginator->getPageName() }}')"
                                     variant="ghost"
                                     size="sm"
                                     class="rounded-r-none"
                                     aria-label="{{ __('pagination.previous') }}">
                            <x-ui.icon name="chevron-left"
                                       size="sm" />
                        </x-ui.button>
                    @endif

                    {{-- Smart pagination --}}
                    @php
                        $currentPage = $paginator->currentPage();
                        $lastPage = $paginator->lastPage();
                        $distanceToEnd = $lastPage - $currentPage;
                    @endphp

                    @if ($distanceToEnd <= 3)
                        @php $start = max(1, $lastPage - 3); @endphp
                        @for ($i = $start; $i <= $lastPage; $i++)
                            @include('components.datatable.pagination-page', ['i' => $i])
                        @endfor
                    @elseif ($distanceToEnd == 4)
                        @for ($i = $currentPage; $i <= $lastPage; $i++)
                            @include('components.datatable.pagination-page', ['i' => $i])
                        @endfor
                    @else
                        @php $windowEnd = $currentPage + 2; @endphp
                        @for ($i = $currentPage; $i <= $windowEnd; $i++)
                            @include('components.datatable.pagination-page', ['i' => $i])
                        @endfor

                        <x-ui.button wire:key="separator-end"
                                     variant="ghost"
                                     size="sm"
                                     disabled
                                     class="hidden rounded-none sm:block">
                            ...
                        </x-ui.button>

                        @for ($i = $lastPage - 1; $i <= $lastPage; $i++)
                            @include('components.datatable.pagination-page', ['i' => $i])
                        @endfor
                    @endif

                    {{-- Next --}}
                    @if ($paginator->hasMorePages())
                        <x-ui.button wire:key="next-active"
                                     wire:click="nextPage('{{ $paginator->getPageName() }}')"
                                     variant="ghost"
                                     size="sm"
                                     class="rounded-l-none"
                                     aria-label="{{ __('pagination.next') }}">
                            <x-ui.icon name="chevron-right"
                                       size="sm" />
                        </x-ui.button>
                    @else
                        <x-ui.button wire:key="next-disabled"
                                     variant="ghost"
                                     size="sm"
                                     disabled
                                     class="rounded-l-none"
                                     aria-label="{{ __('pagination.next') }}">
                            <x-ui.icon name="chevron-right"
                                       size="sm" />
                        </x-ui.button>
                    @endif
                @endif
            </div>

            {{-- Goto --}}
            <div class="flex items-center gap-2"
                 wire:key="pagination-goto-container">
                @if ($paginator->hasPages() && $paginator->lastPage() > 20)
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
                @endif
            </div>

            {{-- Share (always exists) --}}
            <x-ui.share-button :url="$this->getShareUrl($paginator->currentPage())"
                               size="sm"
                               wire:key="pagination-share-btn"
                               variant="ghost" />
        </div>
    </div>
</div>
