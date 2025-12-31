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
     * Items per page
     */
    public int $perPage = 15;

    /**
     * Go to page input value
     */
    public ?int $gotoPageInput = null;
}
