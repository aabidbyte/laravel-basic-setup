<?php

declare(strict_types=1);

namespace App\Services\DataTable;

use Illuminate\Http\Request;

/**
 * Data Transfer Object for DataTable responses
 *
 * Stores the data, meta, stats, config, and filter state for the DataTable.
 * For Livewire 4, this is used to structure the response data that will be
 * consumed by Livewire components.
 */
class DataTableResponse
{
    private array $data = [];

    private array $meta = [];

    private ?array $stats = null;

    private ?array $config = null;

    private ?array $filterState = null;

    private string $viewName = '';

    private ?Request $request = null;

    private array $additionalProps = [];

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function setStats(array $stats): self
    {
        $this->stats = $stats;

        return $this;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function setFilterState(array $filterState): self
    {
        $this->filterState = $filterState;

        return $this;
    }

    public function setViewName(string $viewName): self
    {
        $this->viewName = $viewName;

        return $this;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function setAdditionalProps(array $props): self
    {
        $this->additionalProps = $props;

        return $this;
    }

    /**
     * Get all response data as an array for Livewire consumption
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'data' => $this->data,
            'meta' => $this->meta,
        ];

        if ($this->stats !== null) {
            $data['stats'] = $this->stats;
        }

        if ($this->config !== null) {
            $data['datatable_config'] = $this->config;
        }

        if ($this->filterState !== null) {
            $data['applied_filters'] = $this->filterState;
        }

        // Automatically extract and include query parameters for frontend state management
        if ($this->request !== null) {
            $data['query'] = [
                'search' => $this->request->input('search'),
                'filters' => $this->request->input('filters', []),
                'sort_column' => $this->request->input('sort_column'),
                'sort_direction' => $this->request->input('sort_direction'),
                'page' => $this->request->input('page', 1),
                'per_page' => $this->request->input('per_page', 10),
            ];
        }

        // Merge additional props
        return array_merge($data, $this->additionalProps);
    }

    /**
     * Get data array
     *
     * @return array<int, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get meta array
     *
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Get stats array
     *
     * @return array<string, mixed>|null
     */
    public function getStats(): ?array
    {
        return $this->stats;
    }

    /**
     * Get config array
     *
     * @return array<string, mixed>|null
     */
    public function getConfig(): ?array
    {
        return $this->config;
    }

    /**
     * Get filter state array
     *
     * @return array<string, mixed>|null
     */
    public function getFilterState(): ?array
    {
        return $this->filterState;
    }
}
