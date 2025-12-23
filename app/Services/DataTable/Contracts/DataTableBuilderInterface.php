<?php

declare(strict_types=1);

namespace App\Services\DataTable\Contracts;

use App\Services\DataTable\DataTableRequest;
use App\Services\DataTable\DataTableResponse;

/**
 * Contract for DataTable builder
 */
interface DataTableBuilderInterface
{
    /**
     * Build complete DataTable response
     */
    public function build(DataTableRequest $request): DataTableResponse;
}
