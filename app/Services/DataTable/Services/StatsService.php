<?php

declare(strict_types=1);

namespace App\Services\DataTable\Services;

use App\Services\DataTable\DataTableRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * Service for calculating DataTable statistics
 */
class StatsService
{
    /**
     * Calculate context-aware statistics
     *
     * @return array<string, mixed>
     */
    public function calculate(Builder $filteredQuery, DataTableRequest $request): array
    {
        $hasFilters = $this->hasActiveFilters($request);

        // Use base query (unfiltered) for stats when no filters are applied
        // Use filtered query for stats when filters are applied
        $statsQuery = $hasFilters ? clone $filteredQuery : clone $request->getBaseQuery();

        // Calculate basic stats
        $stats = [
            'total' => $statsQuery->count(),
            'context' => $hasFilters ? 'filtered' : 'all',
            'filters_applied' => $hasFilters,
        ];

        // Add entity-specific stats based on entity key
        $stats = array_merge($stats, $this->getEntitySpecificStats($statsQuery, $request));

        return $stats;
    }

    /**
     * Check if request has active filters
     */
    private function hasActiveFilters(DataTableRequest $request): bool
    {
        $httpRequest = $request->getRequest();

        // Check search
        if ($httpRequest->filled('search')) {
            return true;
        }

        // Check filters
        $filterableFields = array_keys($request->getConfig()->getFilterableFields());
        $filters = array_filter($httpRequest->only($filterableFields));

        return ! empty($filters);
    }

    /**
     * Get entity-specific statistics
     *
     * @return array<string, mixed>
     */
    private function getEntitySpecificStats(Builder $query, DataTableRequest $request): array
    {
        $entityKey = $request->getEntityKey();

        return match ($entityKey) {
            'users' => $this->getUserStats($query),
            default => []
        };
    }

    /**
     * Calculate user-specific statistics
     *
     * @return array<string, int>
     */
    private function getUserStats(Builder $query): array
    {
        return [
            'active_users' => (clone $query)->where('users.is_active', true)->count(),
            'inactive_users' => (clone $query)->where('users.is_active', false)->count(),
            'new_this_month' => (clone $query)->whereMonth('users.created_at', now()->month)
                ->whereYear('users.created_at', now()->year)
                ->count(),
        ];
    }
}
