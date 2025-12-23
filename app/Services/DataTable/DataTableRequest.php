<?php

declare(strict_types=1);

namespace App\Services\DataTable;

use App\Services\DataTable\Contracts\DataTableConfigInterface;
use App\Services\DataTable\Contracts\TransformerInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Data Transfer Object for DataTable requests
 *
 * Encapsulates the DataTable configuration, base query, transformer, and HTTP request.
 */
class DataTableRequest
{
    public function __construct(
        private DataTableConfigInterface $config,
        private Builder $baseQuery,
        private TransformerInterface $transformer,
        private Request $request
    ) {}

    public function getConfig(): DataTableConfigInterface
    {
        return $this->config;
    }

    public function getBaseQuery(): Builder
    {
        return $this->baseQuery;
    }

    public function getTransformer(): TransformerInterface
    {
        return $this->transformer;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getEntityKey(): string
    {
        return $this->config->getEntityKey();
    }

    public function getViewName(): ?string
    {
        return $this->config->getViewName();
    }
}
