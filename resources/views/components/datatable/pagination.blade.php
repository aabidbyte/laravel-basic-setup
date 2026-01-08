@php
    // Get share URL from the component (includes all query params: search, sort, filters, per_page, page)
    // $this refers to the Livewire component that rendered this pagination view
    // Pass current page from paginator to ensure correct page number
    $shareUrl = $this->getShareUrl($paginator->currentPage());
@endphp

@if ($paginator->hasPages())

    <nav
        role="navigation"
        aria-label="{{ __('pagination.pagination_navigation') }}"
        class="flex items-center gap-2"
    >
        <div class="flex items-center gap-2">
            <x-ui.tooltip text="{{ __('table.per_page') }}">
                <x-ui.select
                    wire:model.live="perPage"
                    :label="null"
                    class="select-sm"
                    :options="['12' => '12', '25' => '25', '50' => '50', '100' => '100', '200' => '200']"
                    :prependEmpty="false"
                >
                </x-ui.select>
            </x-ui.tooltip>
        </div>

        <div class="flex gap-2 items-center justify-between sm:hidden">

            @if ($paginator->onFirstPage())
                <span class="btn btn-sm btn-ghost btn-disabled">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <x-ui.button
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    style="ghost"
                    size="sm"
                >
                    {!! __('pagination.previous') !!}
                </x-ui.button>
            @endif

            @if ($paginator->hasMorePages())
                <x-ui.button
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    style="ghost"
                    size="sm"
                >
                    {!! __('pagination.next') !!}
                </x-ui.button>
            @else
                <span class="btn btn-sm btn-ghost btn-disabled">
                    {!! __('pagination.next') !!}
                </span>
            @endif

        </div>

        <div class="hidden sm:flex sm:flex-1 sm:gap-2 sm:items-center sm:justify-between">

            <div>
                <p class="text-sm text-base-content/70">
                    <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    {!! __('pagination.of') !!}
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {!! __('pagination.results') !!}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex rtl:flex-row-reverse shadow-sm rounded-md">

                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span
                            aria-disabled="true"
                            aria-label="{{ __('pagination.previous') }}"
                        >
                            <span
                                class="btn btn-sm btn-ghost btn-disabled rounded-r-none"
                                aria-hidden="true"
                            >
                                <x-ui.icon
                                    name="chevron-left"
                                    size="sm"
                                />
                            </span>
                        </span>
                    @else
                        <x-ui.button
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            style="ghost"
                            size="sm"
                            class="rounded-r-none"
                            aria-label="{{ __('pagination.previous') }}"
                        >
                            <x-ui.icon
                                name="chevron-left"
                                size="sm"
                            />
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
                                <span aria-current="page">
                                    <span class="btn btn-sm btn-active rounded-none">{{ $i }}</span>
                                </span>
                            @else
                                <x-ui.button
                                    wire:click="gotoPage({{ $i }}, '{{ $paginator->getPageName() }}')"
                                    style="ghost"
                                    size="sm"
                                    class="rounded-none"
                                    aria-label="{{ __('pagination.go_to_page', ['page' => $i]) }}"
                                >
                                    {{ $i }}
                                </x-ui.button>
                            @endif
                        @endfor
                    @elseif ($distanceToEnd == 4)
                        {{-- Rule: Near end (e.g. page 23), show current through to end (23, 24, 25, 26, 27) --}}
                        @for ($i = $currentPage; $i <= $lastPage; $i++)
                            @if ($i == $currentPage)
                                <span aria-current="page">
                                    <span class="btn btn-sm btn-active rounded-none">{{ $i }}</span>
                                </span>
                            @else
                                <x-ui.button
                                    wire:click="gotoPage({{ $i }}, '{{ $paginator->getPageName() }}')"
                                    style="ghost"
                                    size="sm"
                                    class="rounded-none"
                                    aria-label="{{ __('pagination.go_to_page', ['page' => $i]) }}"
                                >
                                    {{ $i }}
                                </x-ui.button>
                            @endif
                        @endfor
                    @else
                        {{-- Rule: Standard smart pattern (e.g. 1 2 3 ... 26 27 or 22 23 24 ... 26 27) --}}
                        @php $windowEnd = $currentPage + 2; @endphp
                        @for ($i = $currentPage; $i <= $windowEnd; $i++)
                            @if ($i == $currentPage)
                                <span aria-current="page">
                                    <span class="btn btn-sm btn-active rounded-none">{{ $i }}</span>
                                </span>
                            @else
                                <x-ui.button
                                    wire:click="gotoPage({{ $i }}, '{{ $paginator->getPageName() }}')"
                                    style="ghost"
                                    size="sm"
                                    class="rounded-none"
                                    aria-label="{{ __('pagination.go_to_page', ['page' => $i]) }}"
                                >
                                    {{ $i }}
                                </x-ui.button>
                            @endif
                        @endfor

                        <span aria-disabled="true">
                            <span class="btn btn-sm btn-ghost btn-disabled rounded-none">...</span>
                        </span>

                        @for ($i = $lastPage - 1; $i <= $lastPage; $i++)
                            <x-ui.button
                                wire:click="gotoPage({{ $i }}, '{{ $paginator->getPageName() }}')"
                                style="ghost"
                                size="sm"
                                class="rounded-none"
                                aria-label="{{ __('pagination.go_to_page', ['page' => $i]) }}"
                            >
                                {{ $i }}
                            </x-ui.button>
                        @endfor
                    @endif

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <x-ui.button
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            style="ghost"
                            size="sm"
                            class="rounded-l-none"
                            aria-label="{{ __('pagination.next') }}"
                        >
                            <x-ui.icon
                                name="chevron-right"
                                size="sm"
                            />
                        </x-ui.button>
                    @else
                        <span
                            aria-disabled="true"
                            aria-label="{{ __('pagination.next') }}"
                        >
                            <span
                                class="btn btn-sm btn-ghost btn-disabled rounded-l-none"
                                aria-hidden="true"
                            >
                                <x-ui.icon
                                    name="chevron-right"
                                    size="sm"
                                />
                            </span>
                        </span>
                    @endif
                </span>
                @if ($paginator->lastPage() > 20)
                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            wire:model.blur="gotoPageInput"
                            wire:keydown.enter="performGotoPage"
                            class="input input-sm input-bordered w-16 text-center"
                            min="1"
                            max="{{ $paginator->lastPage() }}"
                            placeholder="1"
                            aria-label="{{ __('pagination.go_to_page_label') }}"
                        />
                        <x-ui.tooltip text="{{ __('pagination.go_to_page_label') }}">
                            <x-ui.button
                                wire:click="performGotoPage"
                                style="ghost"
                                size="sm"
                                aria-label="{{ __('pagination.go_to_page', ['page' => 'X']) }}"
                            >
                                <x-ui.icon
                                    name="chevron-double-right"
                                    size="sm"
                                />
                            </x-ui.button>
                        </x-ui.tooltip>
                    </div>
                @endif
                {{-- Share Button --}}
                <x-ui.share-button
                    :url="$shareUrl"
                    size="sm"
                    style="ghost"
                ></x-ui.share-button>
            </div>
        </div>
    </nav>
@endif
