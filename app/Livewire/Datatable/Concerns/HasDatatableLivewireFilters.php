<?php

declare(strict_types=1);

namespace App\Livewire\DataTable\Concerns;

use App\Enums\DataTable\DataTableFilterType;
use App\Services\DataTable\Builders\Filter;

/**
 * Trait for handling DataTable filters logic.
 *
 * @method void resetPage()
 * @method void savePreferences()
 */
trait HasDatatableLivewireFilters
{
    /**
     * Hook called when filters are updated - resets pagination
     */
    public function updatedFilters(): void
    {
        if (method_exists($this, 'applyChanges')) {
            $this->applyChanges();
        }
    }

    /**
     * Filter values
     *
     * @var array<string, mixed>
     */
    public array $filters = [];

    /**
     * Get filter definitions (optional)
     *
     * @return array<int, Filter>
     */
    protected function getFilterDefinitions(): array
    {
        return [];
    }

    /**
     * Get filters for view
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFilters(): array
    {
        return collect($this->getResolvedFilters())
            ->filter(fn (Filter $filter) => $filter->isVisible())
            ->map(fn (Filter $filter) => $filter->toArray())
            ->values()
            ->toArray();
    }

    /**
     * Get resolved filters (memoized).
     *
     * @return array<int, Filter>
     */
    protected function getResolvedFilters(): array
    {
        return $this->memoize('filters:definitions', function () {
            return method_exists($this, 'getFilterDefinitions') ? $this->getFilterDefinitions() : [];
        });
    }

    /**
     * Get active filters with their labels
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActiveFilters(): array
    {
        // Use memoized filters to avoid re-resolving definitions (which can trigger queries)
        $filterDefinitions = collect($this->getResolvedFilters());

        return collect($this->filters)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(function ($value, $key) use ($filterDefinitions) {
                $filter = $filterDefinitions->first(fn (Filter $f) => $f->getKey() === $key);

                if ($filter === null) {
                    return null;
                }

                // Get the label for the value
                $valueLabel = match ($filter->getType()) {
                    DataTableFilterType::SELECT => $filter->getOptions()[$value] ?? $value,
                    DataTableFilterType::DATE_RANGE => \is_array($value) ? $this->formatDateRangeLabel($value) : $value,
                    default => \is_array($value) ? implode(', ', $value) : $value,
                };

                return [
                    'key' => $key,
                    'label' => $filter->getLabel(),
                    'value' => $value,
                    'valueLabel' => $valueLabel,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    public function clearFilters(): void
    {
        $this->filters = [];
        $this->applyChanges();
    }

    /**
     * Remove a specific filter
     *
     * @param  string  $key  Filter key
     */
    public function removeFilter(string $key): void
    {
        unset($this->filters[$key]);
        $this->applyChanges();
    }

    /**
     * Render the filters section
     */
    public function renderFilters(): string
    {
        return view('components.datatable.filters', [
            'datatable' => $this,
        ])->render();
    }

    /**
     * Format date range label
     *
     * @param  array{from?: string|null, to?: string|null}  $range
     */
    protected function formatDateRangeLabel(array $range): string
    {
        $from = $range['from'] ?? null;
        $to = $range['to'] ?? null;

        if ($from && $to) {
            return "{$from} - {$to}";
        } elseif ($from) {
            return "From {$from}";
        } elseif ($to) {
            return "To {$to}";
        }

        return '';
    }
}
