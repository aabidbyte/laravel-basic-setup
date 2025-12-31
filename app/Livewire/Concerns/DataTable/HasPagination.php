<?php

declare(strict_types=1);

namespace App\Livewire\Concerns\DataTable;

use Livewire\WithPagination;

/**
 * Trait for handling DataTable pagination logic.
 */
trait HasPagination
{
    use WithPagination;

    /**
     * Disable query string synchronization for pagination
     */
    public function queryStringHasPagination(): array
    {
        return [];
    }

    /**
     * Items per page
     */
    public int $perPage = 15;

    /**
     * Go to page input value
     */
    public ?int $gotoPageInput = null;

    /**
     * Hook into page updates to scroll to top
     */
    public function updatedPage(): void
    {
        $this->refreshTable();
    }

    /**
     * Hook into per-page updates to scroll to top
     */
    public function updatedPerPage(): void
    {
        $this->refreshTable();
    }
}
