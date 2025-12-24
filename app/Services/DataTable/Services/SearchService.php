<?php

declare(strict_types=1);

namespace App\Services\DataTable\Services;

use App\Services\DataTable\DataTableRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * Service for applying global search to DataTable queries
 */
class SearchService
{
    /**
     * Apply global search to the query
     */
    public function apply(Builder $query, DataTableRequest $request): Builder
    {
        $httpRequest = $request->getRequest();
        $searchQuery = $httpRequest->input('search');

        if (empty($searchQuery)) {
            return $query;
        }

        $searchableFields = [];

        // First check if definition is available and has searchable fields
        $definition = $request->getDefinition();
        if ($definition !== null) {
            $searchableFields = $definition->getSearchableFields();
        }

        // Fallback to config if definition is null or returns empty array
        if (empty($searchableFields)) {
            $searchableFields = $request->getConfig()->getSearchableFields();
        }

        if (empty($searchableFields)) {
            return $query;
        }

        return $query->search($searchQuery, $searchableFields);
    }
}
