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
    @php
        use App\Enums\Ui\PlaceholderType;
        // Ensure type is compared as string backing value if needed, or use Enum match
        $typeValue = $type instanceof PlaceholderType ? $type->value : $type;
    @endphp

    @switch($typeValue)
        @case(PlaceholderType::TABLE->value)
            {{-- Table skeleton --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body p-4">
                    {{-- Search and action bar --}}
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div class="flex max-w-md flex-1 items-center gap-2">
                            <div class="skeleton h-10 w-10 shrink-0 rounded-lg"></div>
                            <div class="skeleton h-10 flex-1 rounded-lg"></div>
                        </div>
                        <div class="skeleton h-10 w-36 rounded-lg"></div>
                    </div>
                    {{-- Table header --}}
                    <div class="border-base-300 flex gap-4 border-b pb-3">
                        <div class="skeleton h-4 w-8"></div>
                        @for ($i = 0; $i < $columns; $i++)
                            <div class="skeleton h-4 flex-1"></div>
                        @endfor
                    </div>
                    {{-- Table rows --}}
                    <div class="flex flex-col gap-3 pt-3">
                        @for ($r = 0; $r < $rows; $r++)
                            <div class="flex items-center gap-4">
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

        @case(PlaceholderType::FORM->value)
            {{-- Form skeleton --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <div class="skeleton mb-6 h-8 w-1/3"></div>
                    <div class="flex flex-col gap-6">
                        @for ($r = 0; $r < $rows; $r++)
                            <div class="space-y-2">
                                <div class="skeleton h-4 w-24"></div>
                                <div class="skeleton h-12 w-full"></div>
                            </div>
                        @endfor
                        <div class="skeleton mt-4 h-12 w-32"></div>
                    </div>
                </div>
            </div>
        @break

        @case(PlaceholderType::CARD->value)
            {{-- Card skeleton --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="skeleton h-16 w-16 shrink-0 rounded-full"></div>
                        <div class="flex flex-1 flex-col gap-2">
                            <div class="skeleton h-6 w-1/3"></div>
                            <div class="skeleton h-4 w-1/4"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
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

        @case(PlaceholderType::LIST->value)
            {{-- List skeleton --}}
            <div class="flex flex-col gap-4">
                @for ($r = 0; $r < $rows; $r++)
                    <div class="card bg-base-100 shadow">
                        <div class="card-body p-4">
                            <div class="flex items-start gap-3">
                                <div class="skeleton h-6 w-6 shrink-0 rounded"></div>
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

        @case(PlaceholderType::STATS->value)
            {{-- Stats Grid Only --}}
            <div class="lg:grid-cols-{{ $columns }} grid grid-cols-1 gap-6 md:grid-cols-2">
                @for ($i = 0; $i < $columns; $i++)
                    <div class="card bg-base-100 border-base-200 border p-4 shadow-sm">
                        <div class="skeleton mb-2 h-4 w-20"></div>
                        <div class="skeleton h-8 w-12"></div>
                    </div>
                @endfor
            </div>
        @break

        @case(PlaceholderType::CHARTS->value)
            {{-- Charts Grid Only --}}
            <div class="md:grid-cols-{{ $columns > 1 ? 2 : 1 }} lg:grid-cols-{{ $columns }} grid grid-cols-1 gap-6">
                @for ($i = 0; $i < $columns; $i++)
                    <div class="card bg-base-100 border-base-200 h-64 border p-4 shadow-sm">
                        <div class="skeleton h-full w-full rounded-lg"></div>
                    </div>
                @endfor
            </div>
        @break

        @case(PlaceholderType::CHARTS_STATS->value)
            {{-- Mixed: Stats Row + Charts Row --}}
            <div class="space-y-6">
                {{-- Stats Grid (Defaulting to 4 cols for stats in this mixed view, or use $columns) --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="card bg-base-100 border-base-200 border p-4 shadow-sm">
                            <div class="skeleton mb-2 h-4 w-20"></div>
                            <div class="skeleton h-8 w-12"></div>
                        </div>
                    @endfor
                </div>
                {{-- Charts Grid (Defaulting to 2 cols for charts, or based on input) --}}
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    @for ($i = 0; $i < 2; $i++)
                        <div class="card bg-base-100 border-base-200 h-64 border p-4 shadow-sm">
                            <div class="skeleton h-full w-full rounded-lg"></div>
                        </div>
                    @endfor
                </div>
            </div>
        @break

        @default
            {{-- Default: centered loading spinner --}}
            <div class="flex items-center justify-center">
                <x-ui.loading size="lg"
                              color="neutral" />
            </div>
    @endswitch
</div>
