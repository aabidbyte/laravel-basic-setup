<?php

declare(strict_types=1);

namespace App\Livewire\DataTable\Concerns;

use Livewire\WithPagination;

/**
 * Trait for handling DataTable pagination logic.
 */
trait HasDatatableLivewirePagination
{
    use \Livewire\WithoutUrlPagination;
    use WithPagination;

    /**
     * Disable query string synchronization for pagination
     */
    public function queryStringHasPagination(): array
    {
        // Use custom page parameter name if alias is set
        if ($this->queryStringAlias) {
            return [
                'page' => ['as' => $this->getQueryParamName('page')],
            ];
        }

        return [];
    }

    /**
     * Items per page
     */
    /**
     * Items per page
     */
    public int $perPage = 12;

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
        $this->savePreferences();
        $this->refreshTable();
    }

    /**
     * Perform go to page action using the input value
     */
    public function performGotoPage(): void
    {
        if ($this->gotoPageInput && $this->gotoPageInput >= 1 && $this->gotoPageInput <= $this->rows->lastPage()) {
            $this->gotoPage((int) $this->gotoPageInput);
            $this->gotoPageInput = null;
        }
    }
}
