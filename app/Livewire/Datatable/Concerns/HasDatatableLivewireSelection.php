<?php

declare(strict_types=1);

namespace App\Livewire\DataTable\Concerns;

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;

/**
 * Trait for handling DataTable row selection logic.
 *
 * @property LengthAwarePaginator $rows
 */
trait HasDatatableLivewireSelection
{
    /**
     * Selected row UUIDs
     *
     * @var array<int, string>
     */
    public array $selected = [];

    /**
     * UUIDs of rows on the current page (accessible by $wire)
     *
     * @var array<int, string>
     */
    public array $currentPageUuids = [];

    /**
     * Hook into Livewire rendering to populate current page UUIDs for the frontend.
     */
    public function rendering(): void
    {
        $this->currentPageUuids = $this->currentPageUuids();
    }

    /**
     * Get current page UUIDs
     *
     * @return array<int, string>
     */
    #[Computed]
    public function currentPageUuids(): array
    {
        return $this->rows->pluck('uuid')->filter()->toArray();
    }

    /**
     * Toggle select all rows on current page
     */
    public function toggleSelectAll(): void
    {
        $currentPageUuids = $this->currentPageUuids;

        if ($this->isAllSelected()) {
            // Deselect current page UUIDs
            $this->selected = $this->normalizeArray(array_diff($this->selected, $currentPageUuids));
        } else {
            // Select all UUIDs on current page
            $this->selected = $this->normalizeArray(array_unique(\array_merge($this->selected, $currentPageUuids)));
        }
    }

    /**
     * Check if a row is selected
     *
     * @param  string  $uuid  Row UUID
     */
    public function isSelected(string $uuid): bool
    {
        return \in_array($uuid, $this->selected, true);
    }

    /**
     * Toggle selection of a single row
     *
     * @param  string  $uuid  Row UUID
     */
    public function toggleRow(string $uuid): void
    {
        if ($this->isSelected($uuid)) {
            $this->selected = $this->normalizeArray(array_filter($this->selected, fn ($id) => $id !== $uuid));
        } else {
            $this->selected[] = $uuid;
            $this->selected = $this->normalizeArray(array_unique($this->selected));
        }
    }

    /**
     * Normalize array to have sequential numeric keys
     *
     * @param  array<int, string>  $array
     * @return array<int, string>
     */
    protected function normalizeArray(array $array): array
    {
        return \array_values($array);
    }

    /**
     * Clear all selections
     */
    public function clearSelection(): void
    {
        $this->selected = [];
    }

    /**
     * Get count of selected rows
     */
    #[Computed]
    public function selectedCount(): int
    {
        return \count($this->selected);
    }

    /**
     * Check if any rows are selected
     */
    #[Computed]
    public function hasSelection(): bool
    {
        return \count($this->selected) > 0;
    }

    /**
     * Check if all rows on current page are selected
     */
    #[Computed]
    public function isAllSelected(): bool
    {
        if (empty($this->selected)) {
            return false;
        }

        $currentPageUuids = $this->currentPageUuids;

        if (empty($currentPageUuids)) {
            return false;
        }

        return \count(array_intersect($currentPageUuids, $this->selected)) === \count($currentPageUuids);
    }
}
