<?php

declare(strict_types=1);

namespace App\Livewire\Concerns\DataTable;

/**
 * Trait for handling DataTable sorting logic.
 *
 * @method void resetPage()
 * @method void savePreferences()
 */
trait HasSorting
{
    /**
     * Sort column
     */
    public string $sortBy = '';

    /**
     * Sort direction
     */
    public string $sortDirection = 'asc';

    /**
     * Sort by column
     *
     * @param  string  $field  Field name
     */
    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }

        if (method_exists($this, 'savePreferences')) {
            $this->savePreferences();
        }
    }
}
