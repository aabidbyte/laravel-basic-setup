{{--
    Livewire Lazy Loading Placeholder Component

    Props:
    - type: 'default' | 'table' | 'form' | 'card' | 'list'
    - rows: Number of skeleton rows (for table/form/list)
    - columns: Number of table columns
    - class: Additional classes
--}}
@props([
    'type' => 'default',
    'rows' => 3,
    'columns' => 4,
    'class' => '',
])

@php
    $containerClass = "w-full {$class}";
@endphp

<div class="{{ $containerClass }}">
    @switch($type)
        @case('table')
            {{-- Table skeleton --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body p-4">
                    {{-- Table header --}}
                    <div class="flex gap-4 border-b border-base-300 pb-3">
                        <div class="skeleton h-4 w-8"></div>
                        @for ($i = 0; $i < $columns; $i++)
                            <div class="skeleton h-4 flex-1"></div>
                        @endfor
                    </div>
                    {{-- Table rows --}}
                    <div class="flex flex-col gap-3 pt-3">
                        @for ($r = 0; $r < $rows; $r++)
                            <div class="flex gap-4 items-center">
                                <div class="skeleton h-4 w-4 rounded"></div>
                                @for ($i = 0; $i < $columns; $i++)
                                    <div class="skeleton h-4 flex-1"></div>
                                @endfor
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        @break

        @case('form')
            {{-- Form skeleton --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    {{-- Title --}}
                    <div class="skeleton h-8 w-1/3 mb-6"></div>
                    {{-- Form fields --}}
                    <div class="flex flex-col gap-6">
                        @for ($r = 0; $r < $rows; $r++)
                            <div class="space-y-2">
                                <div class="skeleton h-4 w-24"></div>
                                <div class="skeleton h-12 w-full"></div>
                            </div>
                        @endfor
                        {{-- Submit button --}}
                        <div class="skeleton h-12 w-32 mt-4"></div>
                    </div>
                </div>
            </div>
        @break

        @case('card')
            {{-- Card skeleton --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    {{-- Header with avatar and title --}}
                    <div class="flex items-center gap-4 mb-6">
                        <div class="skeleton h-16 w-16 rounded-full shrink-0"></div>
                        <div class="flex flex-col gap-2 flex-1">
                            <div class="skeleton h-6 w-1/3"></div>
                            <div class="skeleton h-4 w-1/4"></div>
                        </div>
                    </div>
                    {{-- Content sections --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @for ($s = 0; $s < 2; $s++)
                            <div class="space-y-4">
                                <div class="skeleton h-5 w-1/2 border-b pb-2"></div>
                                @for ($r = 0; $r < $rows; $r++)
                                    <div class="space-y-2">
                                        <div class="skeleton h-3 w-20"></div>
                                        <div class="skeleton h-4 w-full"></div>
                                    </div>
                                @endfor
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        @break

        @case('list')
            {{-- List skeleton --}}
            <div class="flex flex-col gap-4">
                @for ($r = 0; $r < $rows; $r++)
                    <div class="card bg-base-100 shadow">
                        <div class="card-body p-4">
                            <div class="flex items-start gap-3">
                                <div class="skeleton h-6 w-6 rounded shrink-0"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="skeleton h-5 w-3/4"></div>
                                    <div class="skeleton h-3 w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        @break

        @default
            {{-- Default: centered loading spinner --}}
            <div class="flex items-center justify-center">
                <x-ui.loading
                    size="lg"
                    color="neutral"
                ></x-ui.loading>
            </div>
    @endswitch
</div>
